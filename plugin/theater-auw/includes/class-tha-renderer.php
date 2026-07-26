<?php
/**
 * Einziger Renderpfad — Shortcode und Block laufen beide hier durch, damit die
 * beiden Ausgaben nicht auseinanderdriften.
 */

defined( 'ABSPATH' ) || exit;

class Tha_Renderer {

	/**
	 * Wie oft wurde auf dieser Seite schon gerendert? Steht der Shortcode
	 * mehrfach auf einer Seite, dürfen sich die Anker-IDs nicht doppeln —
	 * sonst springt «Alle Spieldaten» immer zur ersten Instanz und das HTML
	 * ist ungültig.
	 */
	private static $instance = 0;

	/**
	 * Wurde die Navigation auf dieser Seite schon gerendert?
	 *
	 * Steht auf einer Seite mehr als ein Shortcode — etwa Rueckblick UND
	 * Chronik —, darf die Leiste trotzdem nur einmal kommen.
	 */
	private static $nav_done = false;

	/**
	 * Die Navigation ausgeben, falls sie noch fehlt.
	 *
	 * Die alleinstehenden Shortcodes (Gruppe, Rueckblick, Chronik) rendern
	 * nur ihren Abschnitt. Solange das Theme-Menue daneben stand, fiel das
	 * nicht auf. Seit es ausgeblendet werden kann, standen diese Seiten ohne
	 * jede Navigation da — auf «2025 – 2008» war es zuerst zu sehen.
	 */
	public static function nav_once( array $ctx ) {
		if ( self::$nav_done ) {
			return;
		}
		self::$nav_done = true;
		self::part( 'partial-nav', $ctx );
	}

	/**
	 * @param array $args phase, sections, width
	 * @return string
	 */
	public static function render( $args = array() ) {
		$settings = Tha_Settings::get();

		self::$instance++;

		$args = wp_parse_args(
			$args,
			array(
				'phase'    => '',
				'sections' => '',
				'width'    => '',
			)
		);

		$phase = in_array( $args['phase'], Tha_Settings::PHASES, true )
			? $args['phase']
			: Tha_Phase::current();

		$sections = self::parse_sections( $args['sections'] );

		// Sicherheitsnetz: falls die Erkennung in Tha_Assets nicht gegriffen hat
		// (Page-Builder, Widget, verschachtelter Shortcode), hier nachziehen.
		Tha_Assets::enqueue();

		$ctx = array(
			'settings'  => $settings,
			'phase'     => $phase,
			'sections'  => $sections,
			'shows'     => Tha_Phase::shows( $phase, $settings ),
			'countdown' => Tha_Phase::countdown( $phase, $settings ),
			'sale_live' => Tha_Phase::sale_is_live( $settings ),
			'cta_label' => Tha_Phase::cta_label( $settings ),
			'today'     => Tha_Phase::show_today( $settings ),
			'next'      => Tha_Phase::next_show( $settings ),
			'width'     => in_array( $args['width'], array( 'auto', 'viewport', 'contained' ), true ) ? $args['width'] : $settings['width_mode'],
			'is_preview'=> '' !== Tha_Phase::preview_request(),
			// Erste Instanz behält die sauberen IDs, jede weitere bekommt ein Suffix.
			'uid'       => self::$instance > 1 ? '-' . self::$instance : '',
		);

		ob_start();
		include THA_DIR . 'public/views/page.php';
		return ob_get_clean();
	}

	/**
	 * Welche Abschnitte darf eine Unterseite einsetzen?
	 *
	 * Der Name landet in einem Dateipfad. Eine Weissliste statt einer
	 * Bereinigung: so kann kein Shortcode-Attribut je etwas anderes einbinden
	 * als genau diese Vorlagen.
	 */
	const SUB_SECTIONS = array( 'karte', 'updates' );

	/**
	 * Eine Unterseite — Schauspieler, Sponsoren, OK-Team, Speisekarte, Updates.
	 *
	 * Gleiche Navigation und Bildsprache wie die Hauptseite, aber ein
	 * kompakter Kopf. Der Inhalt der Seite bleibt erhalten und wird nur
	 * eingerahmt; es geht nichts verloren.
	 *
	 * Ein Rahmen für alle Unterseiten, nicht drei fast gleiche: was sie
	 * unterscheidet, ist der Baustein in der Mitte — eine Bildergruppe, die
	 * Speisekarte oder die Update-Liste.
	 *
	 * @param array  $args    titel, kicker, lead, gruppe, section, anzahl, phase, width
	 * @param string $content Inhalt zwischen den Shortcode-Tags.
	 * @return string
	 */
	public static function render_subpage( $args = array(), $content = '' ) {
		$settings = Tha_Settings::get();

		self::$instance++;

		$args = wp_parse_args(
			$args,
			array(
				'titel'  => '',
				'kicker' => '',
				'lead'   => '',
				'gruppe'  => '',
				'section' => '',
				'anzahl'  => 0,
				'phase'   => '',
				'width'   => '',
				'kontakt' => true,
			)
		);

		Tha_Assets::enqueue();

		$phase = in_array( $args['phase'], Tha_Settings::PHASES, true )
			? $args['phase']
			: Tha_Phase::current();

		$ctx = array(
			'settings'    => $settings,
			'phase'       => $phase,
			'sections'    => array(),
			'shows'       => Tha_Phase::shows( $phase, $settings ),
			'countdown'   => null,
			'sale_live'   => Tha_Phase::sale_is_live( $settings ),
			'cta_label'   => Tha_Phase::cta_label( $settings ),
			'today'       => Tha_Phase::show_today( $settings ),
			'next'        => Tha_Phase::next_show( $settings ),
			'width'       => in_array( $args['width'], array( 'auto', 'viewport', 'contained' ), true ) ? $args['width'] : $settings['width_mode'],
			'is_preview'  => '' !== Tha_Phase::preview_request(),
			'uid'         => self::$instance > 1 ? '-' . self::$instance : '',
			'sub_title'   => (string) $args['titel'],
			'sub_kicker'  => (string) $args['kicker'],
			'sub_lead'    => (string) $args['lead'],
			'sub_group'   => sanitize_key( $args['gruppe'] ),
			'sub_section' => in_array( $args['section'], self::SUB_SECTIONS, true ) ? $args['section'] : '',
			'sub_limit'   => absint( $args['anzahl'] ),
			'sub_content' => trim( (string) $content ),
			'sub_kontakt' => ! empty( $args['kontakt'] ),
		);

		ob_start();
		include THA_DIR . 'public/views/subpage.php';
		return ob_get_clean();
	}

	/**
	 * Rendert die Seite eine eigene Navigation mit Wortmarke?
	 *
	 * Die Heroes brauchen das: steht oben schon die Marke in der Navigation,
	 * ist dieselbe Marke 111 Pixel darunter im Hero keine Wiedererkennung
	 * mehr, sondern ein Fehler. Dieselbe Bedingung wie in `partial-nav`, an
	 * einer Stelle — sonst laufen die beiden irgendwann auseinander.
	 */
	public static function has_nav( array $settings ) {
		$menu_id = absint( $settings['nav_menu_id'] );
		if ( ! $menu_id ) {
			return false;
		}
		return (bool) wp_get_nav_menu_object( $menu_id );
	}

	/**
	 * Ein Template einbinden. `$ctx` und `$settings` stehen dort zur Verfügung.
	 */
	public static function part( $name, array $ctx, array $extra = array() ) {
		$file = THA_DIR . 'public/views/' . $name . '.php';
		if ( ! file_exists( $file ) ) {
			return;
		}
		$settings = $ctx['settings'];
		$phase    = $ctx['phase'];
		if ( $extra ) {
			extract( $extra, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}
		include $file;
	}

	/**
	 * `sections="hero,spieldaten"` → Whitelist. Leer heisst: alle.
	 */
	private static function parse_sections( $raw ) {
		$all = array( 'hero', 'spieldaten', 'stueck', 'weiteres', 'details', 'rueckblick', 'kontakt' );
		$raw = trim( (string) $raw );
		if ( '' === $raw ) {
			return $all;
		}
		$wanted = array_map( 'trim', explode( ',', strtolower( $raw ) ) );
		$out    = array_values( array_intersect( $all, $wanted ) );
		return $out ? $out : $all;
	}

	/**
	 * Bild ausgeben, sonst gebrandeter Platzhalter. So entstehen nie leere
	 * Rahmen, wenn noch keine Fotos ausgewählt wurden.
	 */
	public static function image( $attachment_id, $size, $label, $dark = false, $ratio = '4 / 3' ) {
		$attachment_id = absint( $attachment_id );

		// Leeres Seitenverhältnis = das Bild behält seine eigenen Proportionen
		// und wird nicht beschnitten. Für Gruppenfotos ist das der Normalfall.
		$auto = ( '' === trim( (string) $ratio ) );

		if ( $auto ) {
			// Das echte Verhältnis aus den Bild-Metadaten holen und als feste
			// Box setzen. Ohne das reserviert ein `loading="lazy"`-Bild keinen
			// Platz: bis es geladen ist, ist es zwar da, aber null Pixel hoch —
			// genau das ist dem Tombolateam passiert. Weil das Verhältnis exakt
			// dem Bild entspricht, wird trotzdem nichts beschnitten.
			$ratio = '16 / 9';
			if ( $attachment_id && function_exists( 'wp_get_attachment_metadata' ) ) {
				$meta = wp_get_attachment_metadata( $attachment_id );
				if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
					$ratio = (int) $meta['width'] . ' / ' . (int) $meta['height'];
				}
			}
		}

		$style = ' style="--tha-ratio:' . esc_attr( $ratio ) . '"';

		if ( $attachment_id && function_exists( 'wp_get_attachment_image' ) ) {
			$html = wp_get_attachment_image(
				$attachment_id,
				$size,
				false,
				array(
					'class'   => 'tha-img',
					'loading' => 'lazy',
				)
			);
			if ( $html ) {
				return '<div class="tha-media' . ( $auto ? ' tha-media--auto' : '' ) . '"' . $style . '>' . $html . '</div>';
			}
		}

		return '<div class="tha-placeholder' . ( $dark ? ' tha-placeholder--dark' : '' ) . '"' . $style . '>'
			. '<span>' . esc_html( $label ) . '</span></div>';
	}
}
