<?php
/**
 * Bildergruppen für die Unterseiten — Schauspieler, Hinter den Kulissen,
 * OK-Team, Sponsoren.
 *
 * Der Kniff: die Namen müssen nicht erfasst werden. Auf stvauw.ch steht in
 * jedem Bild bereits der Name («Hanna Gehrig», «Aargauische Kantonalbank») —
 * als Alternativtext, Bildunterschrift oder Titel. Die Gruppe ist deshalb nur
 * eine Auswahl aus der Mediathek; die Beschriftung kommt aus dem Bild selbst.
 *
 * Wer eine Beschriftung ändern will, ändert sie in der Mediathek — dort, wo
 * sie hingehört, und wirkt dann überall.
 */

defined( 'ABSPATH' ) || exit;

class Tha_Groups {

	const TYPES = array( 'personen', 'logos', 'bilder' );

	/**
	 * Eine Gruppe anhand ihres Schlüssels.
	 *
	 * @return array|null
	 */
	public static function get( $key, array $settings = null ) {
		$settings = $settings ? $settings : Tha_Settings::get();

		foreach ( (array) $settings['groups'] as $group ) {
			if ( isset( $group['key'] ) && $group['key'] === $key ) {
				// Gruppen, die vor 1.0.9 gespeichert wurden, kennen die
				// Gruppenbild-Schlüssel noch nicht.
				return wp_parse_args(
					$group,
					array(
						'title'         => '',
						'kicker'        => '',
						'intro'         => '',
						'type'          => 'personen',
						'columns'       => 4,
						'dark'          => false,
						'lead_image_id' => 0,
						'lead_caption'  => '',
						'lead_layout'   => 'neben',
						'sort'          => 'manuell',
						'labels'        => array(),
						'wide'          => array(),
						'ids'           => array(),
					)
				);
			}
		}
		return null;
	}

	/**
	 * Die Einträge einer Gruppe, fertig aufbereitet.
	 *
	 * @return array Liste aus id, name, role, url.
	 */
	public static function items( array $group ) {
		$out = array();

		$labels = isset( $group['labels'] ) && is_array( $group['labels'] ) ? $group['labels'] : array();
		$wide   = isset( $group['wide'] ) && is_array( $group['wide'] ) ? array_map( 'absint', $group['wide'] ) : array();

		foreach ( (array) $group['ids'] as $id ) {
			$id = absint( $id );
			if ( ! $id ) {
				continue;
			}

			// Eigene Beschriftung schlägt die aus der Mediathek — und zwar Feld
			// für Feld. Wer nur den Firmennamen richtigstellen will, tippt nur
			// den Namen; Rolle und Text kommen weiterhin aus dem Bild. Das Bild
			// selbst bleibt unverändert, es kann anderswo noch verwendet werden.
			$auto  = self::label_for( $id );
			$eigen = isset( $labels[ $id ] ) ? self::normalize_label( $labels[ $id ] ) : self::empty_label();

			$label = array();
			foreach ( array( 'name', 'role', 'text' ) as $feld ) {
				$label[ $feld ] = '' !== $eigen[ $feld ] ? $eigen[ $feld ] : $auto[ $feld ];
			}

			$out[] = array(
				'id'   => $id,
				'name' => $label['name'],
				'role' => $label['role'],
				'text' => $label['text'],
				// Gruppenaufnahmen stehen breiter und behalten ihr Format.
				'wide' => in_array( $id, $wide, true ),
			);
		}

		if ( isset( $group['sort'] ) && 'alpha' === $group['sort'] ) {
			usort( $out, array( __CLASS__, 'compare_by_name' ) );
		}

		return $out;
	}

	/**
	 * Zwei Einträge alphabetisch vergleichen.
	 *
	 * Unbeschriftete Bilder wandern ans Ende. Sonst stünde eine Kachel ohne
	 * Namen ganz vorn — nur weil ihr Sortierschlüssel leer ist, und das sieht
	 * aus wie ein Fehler.
	 */
	private static function compare_by_name( $a, $b ) {
		$leer_a = ( '' === $a['name'] );
		$leer_b = ( '' === $b['name'] );
		if ( $leer_a !== $leer_b ) {
			return $leer_a ? 1 : -1;
		}
		return strcmp( self::sort_key( $a['name'] ), self::sort_key( $b['name'] ) );
	}

	/**
	 * Sortierschlüssel für die alphabetische Reihenfolge.
	 *
	 * «Ägerter» gehört vor «Amrein» — im Deutschen zählt der Umlaut als sein
	 * Grundbuchstabe. PHP vergleicht aber Bytes, und «Ä» liegt dort hinter
	 * «Z»; alle Umlaut-Firmen sammelten sich sonst am Ende der Sponsorenwand.
	 * Deshalb wird vor dem Vergleich gefaltet.
	 *
	 * Führende Anführungszeichen und Klammern fallen weg: «‹Rössli› Auw» soll
	 * beim R stehen, nicht bei den Satzzeichen.
	 */
	private static function sort_key( $name ) {
		$map = array(
			"\u{00E4}" => 'a',
			"\u{00F6}" => 'o',
			"\u{00FC}" => 'u',
			"\u{00C4}" => 'a',
			"\u{00D6}" => 'o',
			"\u{00DC}" => 'u',
			"\u{00E0}" => 'a',
			"\u{00E1}" => 'a',
			"\u{00E2}" => 'a',
			"\u{00E8}" => 'e',
			"\u{00E9}" => 'e',
			"\u{00EA}" => 'e',
			"\u{00EB}" => 'e',
			"\u{00EE}" => 'i',
			"\u{00EF}" => 'i',
			"\u{00F4}" => 'o',
			"\u{00FB}" => 'u',
			"\u{00E7}" => 'c',
			"\u{00DF}" => 'ss',
		);

		$key = strtr( (string) $name, $map );
		$key = function_exists( 'mb_strtolower' ) ? mb_strtolower( $key, 'UTF-8' ) : strtolower( $key );

		return ltrim( $key, " \t\"'(\u{00AB}\u{2039}\u{201E}\u{201C}" );
	}

	/**
	 * Beschriftung aus dem Bild ziehen.
	 *
	 * Reihenfolge: Bildunterschrift → Alternativtext → Titel. Die
	 * Bildunterschrift gewinnt, weil sie am ehesten bewusst gesetzt wurde;
	 * der Titel verliert, weil er oft nur der Dateiname ist.
	 *
	 * Steht in der Beschriftung «Name | Rolle» oder «Name — Rolle», wird
	 * getrennt. Sonst bleibt alles der Name.
	 */
	public static function label_for( $id ) {
		$caption = function_exists( 'wp_get_attachment_caption' ) ? wp_get_attachment_caption( $id ) : '';
		$alt     = get_post_meta( $id, '_wp_attachment_image_alt', true );
		$title   = get_the_title( $id );

		$raw = '';
		foreach ( array( $caption, $alt, $title ) as $candidate ) {
			$candidate = trim( (string) $candidate );
			if ( '' !== $candidate ) {
				$raw = $candidate;
				break;
			}
		}

		return self::split_label( $raw );
	}

	/**
	 * Eine leere Beschriftung.
	 */
	public static function empty_label() {
		return array(
			'name' => '',
			'role' => '',
			'text' => '',
		);
	}

	/**
	 * Eine gespeicherte Beschriftung auf die drei Felder bringen.
	 *
	 * Seit 1.1 stehen Name, Rolle und Text im Backend in getrennten Feldern und
	 * werden auch getrennt gespeichert — niemand muss mehr einen senkrechten
	 * Strich tippen. Ältere Installationen haben dort noch einen einzigen
	 * String «Name | Rolle | Text»; der wird hier aufgeteilt wie eh und je.
	 */
	public static function normalize_label( $value ) {
		if ( ! is_array( $value ) ) {
			return self::split_label( $value );
		}

		$out = self::empty_label();
		foreach ( array_keys( $out ) as $feld ) {
			if ( isset( $value[ $feld ] ) ) {
				$out[ $feld ] = trim( (string) $value[ $feld ] );
			}
		}
		return $out;
	}

	/**
	 * «Franz Troxler | Grilleur» → Name + Rolle.
	 *
	 * Gilt weiterhin für die Bildunterschriften aus der Mediathek: dort ist der
	 * Strich die einzige Möglichkeit, Name und Rolle in ein Feld zu bekommen.
	 */
	public static function split_label( $raw ) {
		$raw = trim( (string) $raw );

		// Dateinamen-Reste wie «DSC_0421» sind keine Beschriftung.
		if ( preg_match( '/^(DSC|IMG|P\d|_MG)[\s_-]*\d+$/i', $raw ) ) {
			$raw = '';
		}

		if ( '' === $raw ) {
			return self::empty_label();
		}

		// «Sohlenberger alias Markus Ineichen» — hier steht die ROLLE vorn und
		// der Mensch hinten. So heissen die Bilder der Schauspieler-Seite.
		if ( preg_match( '/^(.+?)\s+alias\s+(.+)$/iu', $raw, $m ) ) {
			return array(
				'name' => trim( $m[2] ),
				'role' => trim( $m[1] ),
				'text' => '',
			);
		}

		// Sonst am senkrechten Strich oder Gedankenstrich trennen:
		//
		//   «Franz Troxler | Grilleur»
		//   «Michèle Rippman | Souffleuse | „Als Souffleuse kann ich …"»
		//
		// Der dritte Teil ist der Beschreibungstext — auf der Kulissen-Seite
		// steht dort ein ganzes Zitat. Alles Weitere bleibt beim Text, damit
		// ein Strich mitten im Satz nichts abschneidet.
		$parts = preg_split( '/\s*[|–—]\s*/u', $raw, 3 );

		return array(
			'name' => isset( $parts[0] ) ? trim( $parts[0] ) : '',
			'role' => isset( $parts[1] ) ? trim( $parts[1] ) : '',
			'text' => isset( $parts[2] ) ? trim( $parts[2] ) : '',
		);
	}

	/**
	 * Bildgrösse je Gruppentyp.
	 */
	public static function image_size( $type ) {
		return 'logos' === $type ? 'medium' : 'medium_large';
	}

	/**
	 * Seitenverhältnis je Gruppentyp.
	 *
	 * Bei `bilder` bewusst KEINES: das sind Gruppenfotos, und die sind mal
	 * breiter, mal schmaler. Ein erzwungenes 4:3 würde die Leute am Rand
	 * abschneiden — bei einem Vereinsfoto genau die falschen. Leerer Wert
	 * heisst: das Bild behält seine eigenen Proportionen.
	 */
	public static function ratio( $type ) {
		if ( 'logos' === $type ) {
			return '3 / 2';
		}
		if ( 'bilder' === $type ) {
			return '';
		}
		return '3 / 4'; // Porträts sind hochkant.
	}
}
