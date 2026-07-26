<?php
/**
 * Rückblick — die Jahrgänge seit 1937.
 *
 * Auf stvauw.ch liegt dieser Bestand längst gepflegt vor: ein Divi-Portfolio,
 * ein `project`-Beitrag pro Jahrgang, das Plakat als Beitragsbild, der Titel in
 * der Form «2025 Karriere», und für jeden Jahrgang eine eigene Detailseite.
 *
 * Genau deshalb wird hier NICHTS abgetippt. Das Plugin liest den vorhandenen
 * Bestand — sonst gäbe es zwei Wahrheiten, die auseinanderlaufen.
 */

defined( 'ABSPATH' ) || exit;

class Tha_Rueckblick {

	/**
	 * Einträge für die Rückblick-Sektion.
	 *
	 * @return array Liste aus year, title, image_id, url.
	 */
	public static function entries( array $settings = null, array $args = array() ) {
		$settings = $settings ? $settings : Tha_Settings::get();

		$args = wp_parse_args(
			$args,
			array(
				'von'    => 0,   // Jahr, ab dem gezeigt wird (einschliesslich)
				'bis'    => 0,   // Jahr, bis zu dem gezeigt wird (einschliesslich)
				'anzahl' => 0,   // 0 = Einstellung verwenden
			)
		);

		$filtert_jahre = ( $args['von'] || $args['bis'] );

		if ( 'manual' === $settings['rueckblick_source'] ) {
			$rows = self::from_settings( $settings );
		} else {
			// Wird nach Jahren gefiltert, müssen ALLE Beiträge geholt werden.
			// Sonst fehlen bei «1975 – 1937» genau die alten Jahrgänge, weil
			// die Liste schon nach acht Einträgen abgeschnitten wäre.
			$limit = $filtert_jahre ? -1 : (int) $args['anzahl'];
			$rows  = self::from_posts( $settings, $limit );

			// Gibt es den Beitragstyp (noch) nicht oder ist er leer, fällt die
			// Sektion auf die von Hand gepflegte Liste zurück, statt zu
			// verschwinden.
			if ( ! $rows ) {
				$rows = self::from_settings( $settings );
			}
		}

		return self::filter_years( $rows, (int) $args['von'], (int) $args['bis'] );
	}

	/**
	 * Auf einen Jahrgangsbereich einschränken.
	 *
	 * Die alte Seite teilt den Rückblick in 2025–2008, 2007–1987 und
	 * 1975–1937. Genau diese Aufteilung soll erhalten bleiben — es sind drei
	 * eigene Seiten, kein endloses Scrollen.
	 */
	private static function filter_years( array $rows, $von, $bis ) {
		if ( ! $von && ! $bis ) {
			return $rows;
		}

		$out = array();
		foreach ( $rows as $row ) {
			$year = (int) $row['year'];
			if ( ! $year ) {
				continue; // Ohne Jahreszahl lässt sich nicht einordnen.
			}
			if ( $von && $year < $von ) {
				continue;
			}
			if ( $bis && $year > $bis ) {
				continue;
			}
			$out[] = $row;
		}
		return $out;
	}

	/**
	 * Aus einem Beitragstyp lesen — standardmässig dem Divi-Portfolio.
	 */
	private static function from_posts( array $settings, $limit = 0 ) {
		$type = $settings['rueckblick_post_type'];

		if ( ! $type || ! post_type_exists( $type ) ) {
			return array();
		}

		// -1 heisst «alle» (WordPress-Konvention), 0 heisst «wie eingestellt».
		if ( -1 === (int) $limit ) {
			$number = -1;
		} elseif ( $limit > 0 ) {
			$number = (int) $limit;
		} else {
			$number = (int) $settings['rueckblick_count'];
		}

		$posts = get_posts(
			array(
				'post_type'        => $type,
				'post_status'      => 'publish',
				'numberposts'      => $number,
				'orderby'          => 'title',
				'order'            => 'DESC',
				'suppress_filters' => false,
			)
		);

		$out = array();
		foreach ( $posts as $post ) {
			$parsed = self::split_title( $post->post_title );

			$out[] = array(
				'year'     => $parsed['year'],
				'title'    => $parsed['title'],
				'image_id' => (int) get_post_thumbnail_id( $post ),
				'url'      => get_permalink( $post ),
			);
		}

		return $out;
	}

	/**
	 * Von Hand gepflegte Liste aus den Einstellungen.
	 */
	private static function from_settings( array $settings ) {
		$out = array();
		foreach ( (array) $settings['rueckblick'] as $row ) {
			if ( empty( $row['year'] ) ) {
				continue;
			}
			$out[] = array(
				'year'     => $row['year'],
				'title'    => isset( $row['title'] ) ? $row['title'] : '',
				'image_id' => isset( $row['image_id'] ) ? (int) $row['image_id'] : 0,
				'url'      => isset( $row['url'] ) ? $row['url'] : '',
			);
		}
		return $out;
	}

	/**
	 * «2025 Karriere» → Jahr 2025, Titel «Karriere».
	 *
	 * Steht kein Jahr voran, bleibt der ganze Titel stehen — lieber ein
	 * unpassend formatierter Eintrag als ein abgeschnittener.
	 */
	public static function split_title( $raw ) {
		$raw = trim( (string) $raw );

		if ( preg_match( '/^(1[89]\d{2}|20\d{2})\s*[–—-]?\s*(.*)$/u', $raw, $m ) ) {
			return array(
				'year'  => $m[1],
				'title' => trim( $m[2] ),
			);
		}

		return array(
			'year'  => '',
			'title' => $raw,
		);
	}
}
