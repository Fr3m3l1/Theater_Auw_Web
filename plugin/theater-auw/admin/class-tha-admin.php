<?php
/**
 * Backend: ein Menüpunkt «Theater Auw» mit Tabs.
 *
 * Bewusst als handgeschriebenes Formular über der Settings API statt über
 * add_settings_field(): die wiederholbaren Spieldaten-Zeilen und der Media-
 * Picker lassen sich so viel direkter bauen, und die Seite bleibt lesbar.
 */

defined( 'ABSPATH' ) || exit;

class Tha_Admin {

	const SLUG = 'theater-auw';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( 'Tha_Settings', 'register' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( THA_FILE ), array( __CLASS__, 'action_links' ) );
	}

	public static function menu() {
		add_menu_page(
			__( 'Theater Auw', 'theater-auw' ),
			__( 'Theater Auw', 'theater-auw' ),
			'manage_options',
			self::SLUG,
			array( __CLASS__, 'page' ),
			'dashicons-tickets-alt',
			30
		);
	}

	public static function action_links( $links ) {
		$url = admin_url( 'admin.php?page=' . self::SLUG );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Einstellungen', 'theater-auw' ) . '</a>' );
		return $links;
	}

	public static function assets( $hook ) {
		if ( false === strpos( $hook, self::SLUG ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'theater-auw-admin', THA_URL . 'admin/admin.css', array(), THA_VERSION );
		// jquery-ui-sortable: damit sich die Bilder einer Gruppe an ihren Platz
		// ziehen lassen. WordPress bringt es mit, es kommt kein Fremdcode dazu.
		wp_enqueue_script( 'theater-auw-admin', THA_URL . 'admin/admin.js', array( 'jquery', 'jquery-ui-sortable' ), THA_VERSION, true );
		wp_localize_script(
			'theater-auw-admin',
			'THA_ADMIN',
			array(
				'chooseImage' => __( 'Bild wählen', 'theater-auw' ),
				'useImage'    => __( 'Dieses Bild verwenden', 'theater-auw' ),
				'confirmDrop'  => __( 'Diesen Eintrag wirklich entfernen?', 'theater-auw' ),
				'chooseImages' => __( 'Bilder wählen', 'theater-auw' ),
				'useImages'    => __( 'Diese Bilder verwenden', 'theater-auw' ),
				'wide'         => __( 'gross', 'theater-auw' ),

				// Die einzelne Bildkarte.
				'replaceImage'   => __( 'Anderes Bild wählen', 'theater-auw' ),
				'removeImage'    => __( 'Entfernen', 'theater-auw' ),
				'textField'      => __( 'Text', 'theater-auw' ),
				'dragHint'       => __( 'Zum Verschieben ziehen', 'theater-auw' ),
				'fieldName'      => __( 'Name', 'theater-auw' ),
				'fieldRole'      => __( 'Rolle', 'theater-auw' ),
				// Bei den Sponsoren steht in derselben Zeile der Ort.
				'fieldPlace'     => __( 'Ort', 'theater-auw' ),
				'fieldText'      => __( 'Text oder Zitat', 'theater-auw' ),
				'alreadyInGroup' => __( 'Dieses Bild steht in dieser Gruppe schon.', 'theater-auw' ),
				'confirmClear'   => __( 'Wirklich alle Bilder dieser Gruppe entfernen?', 'theater-auw' ),
			)
		);
	}

	/**
	 * Welche Phase gilt gerade — und warum? Die Frage stellt sich jede Admin-
	 * Person als erstes, also steht die Antwort ganz oben.
	 */
	public static function status_explanation( array $settings ) {
		$phase = Tha_Phase::from_schedule( $settings, tha_now() );

		if ( 'manual' === $settings['phase_mode'] ) {
			return array(
				'phase'  => $settings['phase_manual'],
				'reason' => sprintf(
					/* translators: %s: Phase, die automatisch gelten würde */
					__( 'Manuell festgelegt. Nach Kalender wäre gerade «%s» aktiv.', 'theater-auw' ),
					self::phase_label( $phase )
				),
				'manual' => true,
			);
		}

		$next   = self::next_switch( $settings );
		$reason = __( 'Automatisch nach Kalender.', 'theater-auw' );
		if ( $next ) {
			$reason .= ' ' . sprintf(
				/* translators: 1: nächste Phase, 2: Datum */
				__( 'Nächster Wechsel zu «%1$s» am %2$s.', 'theater-auw' ),
				self::phase_label( $next['phase'] ),
				$next['when']
			);
		} else {
			$reason .= ' ' . __( 'Kein weiterer Wechsel geplant.', 'theater-auw' );
		}

		return array(
			'phase'  => $phase,
			'reason' => $reason,
			'manual' => false,
		);
	}

	private static function next_switch( array $settings ) {
		$now  = tha_now();
		$map  = array(
			'vorverkauf' => $settings['phase_schedule']['vorverkauf_start'],
			'spielzeit'  => $settings['phase_schedule']['spielzeit_start'],
			'danke'      => $settings['phase_schedule']['danke_start'],
		);
		foreach ( $map as $phase => $raw ) {
			$dt = tha_parse_datetime( $raw );
			if ( $dt && $dt > $now ) {
				return array(
					'phase' => $phase,
					'when'  => $dt->format( 'd.m.Y, H:i' ) . ' Uhr',
				);
			}
		}
		return null;
	}

	public static function phase_label( $phase ) {
		$labels = array(
			'vorbereitung' => __( 'Vorbereitung', 'theater-auw' ),
			'vorverkauf'   => __( 'Vorverkauf', 'theater-auw' ),
			'spielzeit'    => __( 'Spielzeit', 'theater-auw' ),
			'danke'        => __( 'Danke', 'theater-auw' ),
		);
		return isset( $labels[ $phase ] ) ? $labels[ $phase ] : $phase;
	}

	public static function phase_hint( $phase ) {
		$hints = array(
			'vorbereitung' => __( 'Vor dem Theater. Teaser, Countdown auf die Premiere, keine Ticket-Buttons.', 'theater-auw' ),
			'vorverkauf'   => __( 'Verkauf läuft (oder Countdown darauf). Roter CTA, Spieldaten mit Verfügbarkeit.', 'theater-auw' ),
			'spielzeit'    => __( 'Während der Aufführungen. Gold-Akzent, «Heute Vorstellung», Restplätze.', 'theater-auw' ),
			'danke'        => __( 'Nach der Derniere. Dank, Rückblick, alle Daten als «Vorbei».', 'theater-auw' ),
		);
		return isset( $hints[ $phase ] ) ? $hints[ $phase ] : '';
	}

	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Keine Berechtigung.', 'theater-auw' ) );
		}

		$settings = Tha_Settings::get();
		$tab      = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'seiten';
		$tabs     = self::tabs();

		if ( isset( self::MOVED_TABS[ $tab ] ) ) {
			$tab = self::MOVED_TABS[ $tab ];
		}
		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'seiten';
		}

		include THA_DIR . 'admin/views/settings-page.php';
	}

	/**
	 * Die Reiter — in der Reihenfolge, in der man sie im Lauf einer Saison
	 * braucht.
	 *
	 * Zuerst die Hauptseite von oben nach unten (Phasen, Stück, Spieldaten,
	 * Preise), dann die Unterseiten in der Reihenfolge des Menüs, zuletzt die
	 * Technik. Vorher standen «Navigation & Rückblick» und «Bilder & Rückblick»
	 * an zwei Enden der Leiste und teilten sich EIN Thema — wer den Rückblick
	 * einstellen wollte, musste raten, in welchem der beiden.
	 */
	public static function tabs() {
		return array(
			// Wo stehe ich? Die Übersicht, welche Seite schon umgestellt ist.
			'seiten'     => __( 'Übersicht', 'theater-auw' ),

			// Die Hauptseite, in der Reihenfolge ihrer Abschnitte.
			'phasen'     => __( 'Phasen & Tickets', 'theater-auw' ),
			'stueck'     => __( 'Stück & Texte', 'theater-auw' ),
			'spieldaten' => __( 'Spieldaten', 'theater-auw' ),
			'details'    => __( 'Preise & Infos', 'theater-auw' ),

			// Die eigenen Seiten. Im Backend sucht man nach dem Namen der
			// SEITE, die man pflegen will — nicht nach einer Sammelkategorie.
			'karte'      => __( 'Speisekarte', 'theater-auw' ),
			'updates'    => __( 'Updates', 'theater-auw' ),
			'gruppen'    => __( 'Unterseiten', 'theater-auw' ),
			'anmeldung'  => __( 'Sponsoring', 'theater-auw' ),
			'rueckblick' => __( 'Rückblick', 'theater-auw' ),

			// Alles, was mit dem Theme zu tun hat und nicht mit dem Theater.
			'anzeige'    => __( 'Darstellung', 'theater-auw' ),
		);
	}

	/**
	 * Alte Reiter auf ihren neuen Ort.
	 *
	 * Ein Lesezeichen auf `&tab=bilder` soll dort landen, wo die Felder heute
	 * stehen — nicht wortlos auf der Übersicht.
	 */
	const MOVED_TABS = array(
		'bilder'     => 'rueckblick', // Rückblick-Liste; Logo und Stückbild sind umgezogen
		'navigation' => 'anzeige',    // Navigation gehört zur Theme-Anpassung
	);

	// -----------------------------------------------------------------
	// Kleine Formular-Helfer, damit die Views nicht in Markup ersticken
	// -----------------------------------------------------------------

	public static function name( $key ) {
		return Tha_Settings::OPTION . '[' . $key . ']';
	}

	public static function text_field( $key, $value, $label, $desc = '', $type = 'text' ) {
		?>
		<tr>
			<th scope="row"><label for="tha-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input type="<?php echo esc_attr( $type ); ?>" class="regular-text"
					id="tha-<?php echo esc_attr( $key ); ?>"
					name="<?php echo esc_attr( self::name( $key ) ); ?>"
					value="<?php echo esc_attr( $value ); ?>">
				<?php if ( $desc ) : ?>
					<p class="description"><?php echo wp_kses_post( $desc ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	public static function textarea_field( $key, $value, $label, $desc = '' ) {
		?>
		<tr>
			<th scope="row"><label for="tha-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<textarea rows="7" class="large-text" id="tha-<?php echo esc_attr( $key ); ?>"
					name="<?php echo esc_attr( self::name( $key ) ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
				<?php if ( $desc ) : ?>
					<p class="description"><?php echo wp_kses_post( $desc ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * `datetime-local` — der Browser bringt den Datumsauswähler mit, wir
	 * müssen keinen bauen.
	 */
	public static function datetime_field( $name, $value, $label, $desc = '' ) {
		$value = str_replace( ' ', 'T', (string) $value );
		?>
		<tr>
			<th scope="row"><label for="tha-<?php echo esc_attr( sanitize_key( $name ) ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input type="datetime-local" id="tha-<?php echo esc_attr( sanitize_key( $name ) ); ?>"
					name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
				<?php if ( $desc ) : ?>
					<p class="description"><?php echo wp_kses_post( $desc ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	public static function image_field( $key, $id, $label, $desc = '' ) {
		$id  = absint( $id );
		$src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
		?>
		<tr>
			<th scope="row"><?php echo esc_html( $label ); ?></th>
			<td>
				<div class="tha-media-field" data-tha-media>
					<div class="tha-media-field__preview">
						<?php if ( $src ) : ?>
							<img src="<?php echo esc_url( $src ); ?>" alt="">
						<?php endif; ?>
					</div>
					<input type="hidden" name="<?php echo esc_attr( self::name( $key ) ); ?>" value="<?php echo esc_attr( $id ); ?>">
					<p>
						<button type="button" class="button" data-tha-media-pick><?php esc_html_e( 'Bild wählen', 'theater-auw' ); ?></button>
						<button type="button" class="button-link" data-tha-media-clear><?php esc_html_e( 'Entfernen', 'theater-auw' ); ?></button>
					</p>
					<?php if ( $desc ) : ?>
						<p class="description"><?php echo wp_kses_post( $desc ); ?></p>
					<?php endif; ?>
				</div>
			</td>
		</tr>
		<?php
	}
}
