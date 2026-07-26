<?php
/**
 * Die Shortcodes — der Weg, den die Admin-Person tatsächlich benutzt.
 *
 *   [theater_auw]                      die Hauptseite mit den vier Phasen
 *   [theater_auw_seite titel="…"]      Rahmen für eine Unterseite
 *   [theater_auw_gruppe key="…"]       eine Bildergruppe einzeln
 *   [theater_auw_karte]                die Speisekarte
 *   [theater_auw_updates]              die Update-Seite
 */

defined( 'ABSPATH' ) || exit;

class Tha_Shortcode {

	/**
	 * Alle Shortcodes des Plugins — an EINER Stelle.
	 *
	 * Vorher stand die Liste dreimal im Code, und zweimal fehlten die
	 * juengsten Eintraege. Folge: auf den Rueckblick- und Chronik-Seiten
	 * erkannte das Plugin seine eigenen Inhalte nicht, setzte keine
	 * Koerperklassen — und das alte Theme-Menue blieb stehen.
	 */
	const TAGS = array(
		'theater_auw',
		'theater_auw_seite',
		'theater_auw_gruppe',
		'theater_auw_rueckblick',
		'theater_auw_chronik',
		'theater_auw_nav',
		'theater_auw_kontakt',
		'theater_auw_karte',
		'theater_auw_updates',
	);

	public static function init() {
		add_shortcode( 'theater_auw', array( __CLASS__, 'render' ) );
		add_shortcode( 'theater_auw_seite', array( __CLASS__, 'render_subpage' ) );
		add_shortcode( 'theater_auw_gruppe', array( __CLASS__, 'render_group' ) );
		add_shortcode( 'theater_auw_rueckblick', array( __CLASS__, 'render_rueckblick' ) );
		add_shortcode( 'theater_auw_chronik', array( __CLASS__, 'render_chronik' ) );
		add_shortcode( 'theater_auw_nav', array( __CLASS__, 'render_nav' ) );
		add_shortcode( 'theater_auw_kontakt', array( __CLASS__, 'render_kontakt' ) );
		add_shortcode( 'theater_auw_karte', array( __CLASS__, 'render_karte' ) );
		add_shortcode( 'theater_auw_updates', array( __CLASS__, 'render_updates' ) );
	}

	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'phase'    => '',
				'sections' => '',
				'width'    => '',
			),
			$atts,
			'theater_auw'
		);

		return Tha_Renderer::render( $atts );
	}

	/**
	 * Unterseite. Umschliessend benutzbar:
	 *
	 *   [theater_auw_seite titel="Die Schauspieler" gruppe="schauspieler"]
	 *     Freier Text, der unter der Galerie steht.
	 *   [/theater_auw_seite]
	 *
	 * Der Inhalt dazwischen wird mit `do_shortcode` und `wpautop` behandelt,
	 * damit sich darin ganz normal weiterarbeiten lässt.
	 */
	public static function render_subpage( $atts, $content = '' ) {
		$atts = shortcode_atts(
			array(
				'titel'  => '',
				'kicker' => '',
				'lead'   => '',
				'gruppe'  => '',
				'phase'   => '',
				'width'   => '',
				'kontakt' => '1',
			),
			$atts,
			'theater_auw_seite'
		);

		$atts['kontakt'] = self::flag( $atts['kontakt'] );

		return Tha_Renderer::render_subpage( $atts, self::inner( $content ) );
	}

	/**
	 * Die Speisekarte:
	 *
	 *   [theater_auw_karte]
	 *   [theater_auw_karte titel="Speisekarte 2026"]
	 *     Freier Text, der unter der Karte steht.
	 *   [/theater_auw_karte]
	 *
	 * Derselbe Rahmen wie bei einer Unterseite, nur mit den Kartenzeilen aus
	 * den Einstellungen statt einer Bildergruppe. Ohne Angaben nimmt der
	 * Shortcode Überschrift und Kicker von dort — auf der Seite steht dann
	 * wirklich nur `[theater_auw_karte]`.
	 */
	public static function render_karte( $atts, $content = '' ) {
		$settings = Tha_Settings::get();

		$atts = shortcode_atts(
			array(
				'titel'   => $settings['menu_title'],
				'kicker'  => $settings['menu_kicker'],
				'lead'    => '',
				'phase'   => '',
				'width'   => '',
				'kontakt' => '1',
			),
			$atts,
			'theater_auw_karte'
		);

		$atts['kontakt'] = self::flag( $atts['kontakt'] );
		$atts['section'] = 'karte';

		return Tha_Renderer::render_subpage( $atts, self::inner( $content ) );
	}

	/**
	 * Die Update-Seite:
	 *
	 *   [theater_auw_updates]
	 *   [theater_auw_updates anzahl="5"]
	 *
	 * `anzahl` zeigt nur die neuesten Einträge — für einen Auszug auf einer
	 * anderen Seite. Ohne Angabe stehen alle da.
	 */
	public static function render_updates( $atts, $content = '' ) {
		$settings = Tha_Settings::get();

		$atts = shortcode_atts(
			array(
				'titel'   => $settings['updates_title'],
				'kicker'  => $settings['updates_kicker'],
				'lead'    => '',
				'anzahl'  => '',
				'phase'   => '',
				'width'   => '',
				'kontakt' => '1',
			),
			$atts,
			'theater_auw_updates'
		);

		$atts['kontakt'] = self::flag( $atts['kontakt'] );
		$atts['section'] = 'updates';

		return Tha_Renderer::render_subpage( $atts, self::inner( $content ) );
	}

	/**
	 * Der Inhalt zwischen den Shortcode-Tags — mit `do_shortcode` und
	 * `wpautop`, damit sich darin ganz normal weiterarbeiten lässt.
	 */
	private static function inner( $content ) {
		$content = (string) $content;
		if ( '' === trim( $content ) ) {
			return '';
		}
		return wpautop( do_shortcode( $content ) );
	}

	/**
	 * Ja/Nein-Attribut. `kontakt="0"` und `kontakt="nein"` heissen dasselbe —
	 * wer auf einer deutschen Oberfläche arbeitet, schreibt eher «nein».
	 */
	private static function flag( $value ) {
		$value = strtolower( trim( (string) $value ) );
		return ! in_array( $value, array( '0', 'nein', 'false', 'aus' ), true );
	}

	/**
	 * Eine Bildergruppe allein — für Seiten, die schon einen eigenen Aufbau
	 * haben und nur die Galerie im Theater-Look brauchen.
	 */
	public static function render_group( $atts ) {
		$atts = shortcode_atts(
			array(
				'key'      => '',
				'spalten'  => '',
				'titel'    => '',
			),
			$atts,
			'theater_auw_gruppe'
		);

		$settings = Tha_Settings::get();
		$group    = Tha_Groups::get( sanitize_key( $atts['key'] ), $settings );

		if ( ! $group ) {
			return '';
		}

		// Der Shortcode darf die Einstellungen punktuell übersteuern.
		if ( '' !== $atts['spalten'] ) {
			$group['columns'] = max( 1, min( 8, absint( $atts['spalten'] ) ) );
		}
		if ( '' !== $atts['titel'] ) {
			$group['title'] = sanitize_text_field( $atts['titel'] );
		}

		Tha_Assets::enqueue();

		$phase = Tha_Phase::current();
		$ctx   = array(
			'settings' => $settings,
			'phase'    => $phase,
			'uid'      => '',
		);

		ob_start();
		?>
		<div class="tha tha--<?php echo esc_attr( $phase ); ?> tha--width-<?php echo esc_attr( $settings['width_mode'] ); ?>">
			<?php
			echo Tha_Assets::font_link_fallback(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			Tha_Renderer::nav_once( $ctx );
			Tha_Renderer::part( 'section-gruppe', $ctx, array( 'group' => $group ) );
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Rückblick als eigene Seite — für die Jahrgangsseiten, die es schon gibt:
	 *
	 *   [theater_auw_rueckblick von="2008" bis="2026" titel="Rückblick 2026 – 2008"]
	 *   [theater_auw_rueckblick von="1987" bis="2007"]
	 *   [theater_auw_rueckblick von="1937" bis="1975"]
	 *
	 * Die Jahrgänge kommen aus demselben Bestand wie auf der Hauptseite; hier
	 * wird nur der Zeitraum eingegrenzt.
	 */
	public static function render_rueckblick( $atts ) {
		$atts = shortcode_atts(
			array(
				'von'     => '',
				'bis'     => '',
				'anzahl'  => '',
				'titel'   => '',
				'kicker'  => '',
				'spalten' => '',
			),
			$atts,
			'theater_auw_rueckblick'
		);

		$settings = Tha_Settings::get();

		Tha_Assets::enqueue();

		$phase = Tha_Phase::current();
		$ctx   = array(
			'settings' => $settings,
			'phase'    => $phase,
			'uid'      => '',
		);

		ob_start();
		?>
		<div class="tha tha--<?php echo esc_attr( $phase ); ?> tha--width-<?php echo esc_attr( $settings['width_mode'] ); ?>">
			<?php
			echo Tha_Assets::font_link_fallback(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			Tha_Renderer::nav_once( $ctx );
			Tha_Renderer::part(
				'section-rueckblick',
				$ctx,
				array(
					'rb_args'   => array(
						'von'    => absint( $atts['von'] ),
						'bis'    => absint( $atts['bis'] ),
						'anzahl' => absint( $atts['anzahl'] ),
					),
					'rb_title'  => sanitize_text_field( $atts['titel'] ),
					'rb_kicker' => sanitize_text_field( $atts['kicker'] ),
					'rb_cols'   => absint( $atts['spalten'] ),
				)
			);
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Die eingebaute Chronik — für die Jahre vor dem Portfolio:
	 *
	 *   [theater_auw_chronik von="1987" bis="2007" titel="1987 – 2007"]
	 *   [theater_auw_chronik von="1937" bis="1975"]
	 *
	 * Ohne Zeitraum kommt der ganze Bestand von 1937 bis 2007.
	 */
	public static function render_chronik( $atts ) {
		$atts = shortcode_atts(
			array(
				'von'    => '',
				'bis'    => '',
				'titel'  => '',
				'kicker' => '',
				'text'   => '',
				'bilder' => '1',
			),
			$atts,
			'theater_auw_chronik'
		);

		$settings = Tha_Settings::get();

		Tha_Assets::enqueue();

		$phase = Tha_Phase::current();
		$ctx   = array(
			'settings' => $settings,
			'phase'    => $phase,
			'uid'      => '',
		);

		ob_start();
		?>
		<div class="tha tha--<?php echo esc_attr( $phase ); ?> tha--width-<?php echo esc_attr( $settings['width_mode'] ); ?>">
			<?php
			echo Tha_Assets::font_link_fallback(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			Tha_Renderer::nav_once( $ctx );
			Tha_Renderer::part(
				'section-chronik',
				$ctx,
				array(
					'ch_args'   => array(
						'von' => absint( $atts['von'] ),
						'bis' => absint( $atts['bis'] ),
					),
					'ch_title'  => sanitize_text_field( $atts['titel'] ),
					'ch_kicker' => sanitize_text_field( $atts['kicker'] ),
					'ch_intro'  => wp_kses_post( $atts['text'] ),
					'ch_images' => ( '0' !== (string) $atts['bilder'] && 'nein' !== (string) $atts['bilder'] ),
				)
			);
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Nur die Navigation:  [theater_auw_nav]
	 *
	 * Fuer Seiten, die bereits einen eigenen Aufbau haben — etwa im
	 * Divi-Theme-Builder. Dort laesst sich der Inhalt nicht in einen
	 * Shortcode einschliessen; er liegt in Modulen, und der Shortcode steht
	 * irgendwo dazwischen. Auf «Speisekarte» hatte das zur Folge, dass die
	 * Navigation 856 Pixel weit unten begann, unter der ganzen Speisekarte.
	 *
	 * Als eigenes Modul ganz oben eingesetzt, sitzt sie dort, wo sie hingehoert.
	 */
	public static function render_nav( $atts ) {
		return self::wrap(
			static function ( $ctx ) {
				Tha_Renderer::nav_once( $ctx );
			},
			'tha--bar'
		);
	}

	/**
	 * Nur der Abschluss mit der Kontaktzeile:  [theater_auw_kontakt]
	 *
	 * Das Gegenstueck zu `[theater_auw_nav]` — ans Ende einer Seite mit
	 * eigenem Aufbau.
	 */
	public static function render_kontakt( $atts ) {
		return self::wrap(
			static function ( $ctx ) {
				Tha_Renderer::part( 'section-kontakt', $ctx );
			}
		);
	}

	/**
	 * Die immer gleiche Wurzel um einen einzelnen Baustein.
	 */
	private static function wrap( $callback, $extra_class = '' ) {
		$settings = Tha_Settings::get();

		Tha_Assets::enqueue();

		$phase = Tha_Phase::current();
		$ctx   = array(
			'settings'  => $settings,
			'phase'     => $phase,
			'sections'  => array(),
			'sale_live' => Tha_Phase::sale_is_live( $settings ),
			'cta_label' => Tha_Phase::cta_label( $settings ),
			'uid'       => '',
		);

		ob_start();
		?>
		<div class="tha tha--<?php echo esc_attr( $phase ); ?> tha--width-<?php echo esc_attr( $settings['width_mode'] ); ?><?php echo $extra_class ? ' ' . esc_attr( $extra_class ) : ''; ?>">
			<?php
			echo Tha_Assets::font_link_fallback(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$callback( $ctx );
			?>
		</div>
		<?php
		return ob_get_clean();
	}
}
