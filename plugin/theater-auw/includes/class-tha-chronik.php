<?php
/**
 * Die Chronik der abgeschlossenen Jahrgänge.
 *
 * Zwei Bestände, eine Ansicht:
 *
 *   1937–2007  liegt fest im Plugin (includes/data-chronik.php). Diese Jahre
 *              sind Geschichte; sie ändern sich nicht mehr.
 *   ab 2008    kommt aus dem Divi-Portfolio, wo jedes Jahr ein Eintrag mit
 *              Plakat und eigener Seite dazukommt.
 *
 * Auf den beiden Archivseiten von stvauw.ch standen die alten Jahrgänge als
 * von Hand gebaute Divi-Module. Jede Änderung am Aussehen hätte 60 Blöcke
 * betroffen. Hier sind es 60 Datenzeilen und eine Vorlage.
 */

defined( 'ABSPATH' ) || exit;

class Tha_Chronik {

	const IMAGE_CACHE = 'tha_chronik_bilder';

	/** @var array|null Einmal geladen, dann gemerkt. */
	private static $cache = null;

	/**
	 * Alle Einträge, neueste zuerst.
	 *
	 * @return array
	 */
	public static function all() {
		if ( null === self::$cache ) {
			$rows = include THA_DIR . 'includes/data-chronik.php';
			if ( ! is_array( $rows ) ) {
				$rows = array();
			}
			usort(
				$rows,
				static function ( $a, $b ) {
					return (int) $b['year'] - (int) $a['year'];
				}
			);
			self::$cache = $rows;
		}
		return self::$cache;
	}

	/**
	 * Einträge in einem Zeitraum.
	 *
	 * @param array $args von, bis
	 * @return array
	 */
	public static function entries( $args = array() ) {
		$von = isset( $args['von'] ) ? (int) $args['von'] : 0;
		$bis = isset( $args['bis'] ) ? (int) $args['bis'] : 0;

		if ( $von && $bis && $von > $bis ) {
			$tausch = $von;
			$von    = $bis;
			$bis    = $tausch;
		}

		$out = array();
		foreach ( self::all() as $row ) {
			$jahr = (int) $row['year'];
			if ( $von && $jahr < $von ) {
				continue;
			}
			if ( $bis && $jahr > $bis ) {
				continue;
			}
			$out[] = $row;
		}
		return $out;
	}

	/**
	 * Ältestes und jüngstes Jahr des eingebauten Bestands.
	 *
	 * @return array {min, max}
	 */
	public static function span() {
		$rows = self::all();
		if ( ! $rows ) {
			return array(
				'min' => 0,
				'max' => 0,
			);
		}
		$jahre = array();
		foreach ( $rows as $row ) {
			$jahre[] = (int) $row['year'];
		}
		return array(
			'min' => min( $jahre ),
			'max' => max( $jahre ),
		);
	}

	/**
	 * Deckt die Chronik diesen Zeitraum ab?
	 *
	 * Damit entscheidet der Tab «Seiten», ob er für eine Archivseite die
	 * Chronik oder den Portfolio-Rückblick vorschlägt. «2007 – 1987» liegt
	 * ganz in der Chronik, «2025 – 2008» ganz im Portfolio.
	 */
	public static function covers( $von, $bis ) {
		$span = self::span();
		if ( ! $span['max'] ) {
			return false;
		}
		return (int) $bis <= $span['max'] && (int) $von >= $span['min'];
	}

	/**
	 * Die Fotos, nach Jahr sortiert.
	 *
	 * Auf stvauw.ch sind die alten Aufnahmen bereits nach Jahr benannt —
	 * «1937», «1958 2», «1963 4» bei den frühen Jahrgängen, «2007_01» bis
	 * «2007_20» bei den späteren, dazu ein Titelbild «2007-verwandtschaftlich-
	 * verwandt.jpg». Das ist eine Zuordnung, die schon existiert; sie muss
	 * niemand von Hand nachbauen.
	 *
	 * EINE Abfrage für alle Jahrgänge, nicht sechzig. Das Ergebnis liegt einen
	 * Tag im Transient — der Bestand ist abgeschlossen und ändert sich nicht.
	 *
	 * @return array Jahr => Liste von Anhang-IDs.
	 */
	public static function images_by_year() {
		$cached = get_transient( self::IMAGE_CACHE );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;

		// Der Jahresanfang muss im TITEL oder im Dateinamen stehen. Beim
		// Dateinamen zaehlt nur der letzte Pfadteil: sonst wuerde der
		// Upload-Ordner «…/2019/06/…» jedes Bild dieses Monats einsammeln.
		$rows = $wpdb->get_results(
			"SELECT p.ID, p.post_title, m.meta_value AS datei
			   FROM {$wpdb->posts} p
			   LEFT JOIN {$wpdb->postmeta} m
			     ON m.post_id = p.ID AND m.meta_key = '_wp_attached_file'
			  WHERE p.post_type = 'attachment'
			    AND p.post_mime_type LIKE 'image/%'
			    AND (
			          p.post_title REGEXP '^(19|20)[0-9]{2}'
			       OR m.meta_value REGEXP '(^|/)(19|20)[0-9]{2}[^/]*$'
			    )"
		); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		// Nur Jahre, die in der Chronik wirklich vorkommen. Sonst landete
		// etwa «2025_Theater_Auw_Programm_web.png» als Jahrgang 2025 hier.
		$erlaubt = array();
		foreach ( self::all() as $eintrag ) {
			$erlaubt[ (int) $eintrag['year'] ] = true;
		}

		$out = array();
		foreach ( (array) $rows as $row ) {
			$basis = $row->post_title;
			if ( ! preg_match( '/^(19|20)\d{2}/', $basis ) ) {
				$basis = basename( (string) $row->datei );
			}
			if ( ! preg_match( '/^((?:19|20)\d{2})/', $basis, $m ) ) {
				continue;
			}
			$jahr = (int) $m[1];
			if ( ! isset( $erlaubt[ $jahr ] ) ) {
				continue;
			}
			$out[ $jahr ][ $basis ] = (int) $row->ID;
		}

		// Innerhalb eines Jahres nach Namen: «2007-titelbild» kommt vor
		// «2007_01», weil der Bindestrich vor dem Unterstrich liegt — das
		// Titelbild steht damit von selbst zuerst.
		foreach ( $out as $jahr => $bilder ) {
			ksort( $bilder, SORT_NATURAL );
			$out[ $jahr ] = array_values( $bilder );
		}

		set_transient( self::IMAGE_CACHE, $out, DAY_IN_SECONDS );
		return $out;
	}

	/**
	 * Die Fotos eines einzelnen Jahrgangs.
	 *
	 * @return int[] Anhang-IDs.
	 */
	public static function images( $year ) {
		$alle = self::images_by_year();
		$jahr = (int) $year;
		return isset( $alle[ $jahr ] ) ? $alle[ $jahr ] : array();
	}

	/** Nach einem Medien-Upload ist die Zuordnung veraltet. */
	public static function forget_images() {
		delete_transient( self::IMAGE_CACHE );
	}

	/**
	 * Die Nebenangaben einer Zeile als lesbare Kette.
	 *
	 * «von Hans Lee · Regie August Burkart · 5 D / 7 M». Was fehlt, fehlt —
	 * für 1937 bis 1975 ist nur Titel und Gattung überliefert, und ein
	 * erfundener Regisseur wäre schlimmer als eine leere Zeile.
	 */
	public static function meta( array $row ) {
		$teile = array();

		if ( ! empty( $row['author'] ) ) {
			$teile[] = sprintf(
				/* translators: %s: Autorin oder Autor */
				__( 'von %s', 'theater-auw' ),
				$row['author']
			);
		}
		if ( ! empty( $row['director'] ) ) {
			$teile[] = sprintf(
				/* translators: %s: Regie */
				__( 'Regie %s', 'theater-auw' ),
				$row['director']
			);
		}
		if ( ! empty( $row['cast'] ) ) {
			$teile[] = $row['cast'];
		}

		return implode( ' · ', $teile );
	}
}
