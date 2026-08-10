<?php
/**
 * Suchmaschinen und Vorschauen — was die Theater-Seiten von sich erzählen.
 *
 * Drei Dinge, die sonst niemand liefert:
 *
 *   1. Die Vorstellungen als strukturierte Daten (schema.org TheaterEvent).
 *      Google kann die Spieldaten damit direkt im Suchergebnis zeigen —
 *      und NUR das Plugin kennt sie samt Uhrzeit, Status und Ticket-Link.
 *   2. Eine Beschreibung für das Suchergebnis-Snippet.
 *   3. Open-Graph-Angaben, damit ein geteilter Link in WhatsApp und
 *      Facebook Titel, Text und Bild zeigt statt nackter Adresse.
 *
 * Ist ein SEO-Plugin installiert (Yoast, Rank Math, …), zieht sich das
 * Plugin bei 2. und 3. zurück — zwei Beschreibungen im selben Kopf sind
 * schlimmer als eine fehlende. Die strukturierten Vorstellungsdaten
 * bleiben: die kennt auch das beste SEO-Plugin nicht.
 */

defined( 'ABSPATH' ) || exit;

class Tha_Seo {

	public static function init() {
		// Früh genug, dass die Angaben im <head> vor dem Theme-Zubehör
		// stehen; spät genug, dass WordPress den Seitentitel schon kennt.
		add_action( 'wp_head', array( __CLASS__, 'output' ), 5 );
	}

	public static function output() {
		if ( ! is_singular() || ! Tha_Assets::is_theater_page() ) {
			return;
		}

		$settings = Tha_Settings::get();

		// Die Vorstellungen nur auf der Hauptseite — stünden sie auch auf
		// jeder Unterseite, meldete jede Seite dieselben sieben Termine,
		// und Google mag keine Kopien.
		if ( self::is_main_page() ) {
			$events = self::events( $settings );
			if ( $events ) {
				echo '<script type="application/ld+json">'
					. wp_json_encode( $events, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
					. '</script>' . "\n";
			}
		}

		if ( self::seo_plugin_active() ) {
			return;
		}

		$beschreibung = self::description( $settings );
		$titel        = wp_get_document_title();
		$url          = get_permalink();
		$bild         = self::image_url( $settings );

		echo '<meta name="description" content="' . esc_attr( $beschreibung ) . '">' . "\n";
		echo '<meta property="og:locale" content="de_CH">' . "\n";
		echo '<meta property="og:type" content="website">' . "\n";
		echo '<meta property="og:site_name" content="Theater Auw">' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $titel ) . '">' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $beschreibung ) . '">' . "\n";
		if ( $url ) {
			echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
		}
		if ( $bild ) {
			echo '<meta property="og:image" content="' . esc_url( $bild ) . '">' . "\n";
			echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		} else {
			echo '<meta name="twitter:card" content="summary">' . "\n";
		}
	}

	/**
	 * Trägt die abgefragte Seite den Haupt-Shortcode oder den Block?
	 */
	private static function is_main_page() {
		$post_id = (int) get_queried_object_id();
		if ( ! $post_id ) {
			return false;
		}
		$content = get_post_field( 'post_content', $post_id );
		if ( is_string( $content ) && has_shortcode( $content, 'theater_auw' ) ) {
			return true;
		}
		return function_exists( 'has_block' ) && has_block( 'theater-auw/seite', $post_id );
	}

	/**
	 * Die kommenden Vorstellungen als schema.org-Datensätze.
	 *
	 * Vergangene Termine fallen weg — Google ignoriert sie ohnehin, und ein
	 * Datensatz, der auf «vorbei» steht, sagt Suchenden nichts Nützliches.
	 *
	 * @return array Liste von TheaterEvent-Objekten, ggf. leer.
	 */
	public static function events( array $settings ) {
		$heute  = tha_now()->format( 'Y-m-d' );
		$events = array();

		foreach ( (array) $settings['dates'] as $row ) {
			if ( empty( $row['date'] ) || $row['date'] < $heute ) {
				continue;
			}
			$zeit  = isset( $row['time'] ) ? str_replace( '.', ':', $row['time'] ) : '';
			$start = tha_parse_datetime( $row['date'] . ' ' . $zeit );
			if ( ! $start ) {
				continue;
			}

			$name = tha_guillemets( $settings['play_title'] );
			if ( ! empty( $row['label'] ) ) {
				$name .= ' — ' . $row['label'];
			}

			$ort = array(
				'@type'   => 'Place',
				'name'    => $settings['venue_name'],
				'address' => array(
					'@type'           => 'PostalAddress',
					'addressLocality' => 'Auw',
					'postalCode'      => '5644',
					'addressCountry'  => 'CH',
				),
			);
			if ( ! empty( $settings['venue_maps_url'] ) ) {
				$ort['hasMap'] = $settings['venue_maps_url'];
			}

			$event = array(
				'@type'               => 'TheaterEvent',
				'name'                => 'Theater Auw: ' . $name,
				'startDate'           => $start->format( 'c' ),
				'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
				'eventStatus'         => 'https://schema.org/EventScheduled',
				'location'            => $ort,
				'performer'           => array(
					'@type' => 'PerformingGroup',
					'name'  => 'Theater Auw',
				),
				'organizer'           => array(
					'@type' => 'Organization',
					'name'  => 'Theater Auw (STV Auw)',
					'url'   => home_url( '/' ),
				),
				'description'         => self::description( $settings ),
			);

			$bild = self::image_url( $settings );
			if ( $bild ) {
				$event['image'] = $bild;
			}

			// Ein Angebot nur, wenn es wirklich einen Ticket-Link gibt.
			// tha_ticket_url() wird hier bewusst nicht benutzt: die Funktion
			// liest die Einstellungen selbst, diese Methode bekommt sie
			// übergeben — auch in der Vorschau ohne WordPress.
			$url = ! empty( $row['ticket_url'] ) ? $row['ticket_url'] : $settings['ticket_url'];
			if ( $url ) {
				$status = array(
					'offen'       => 'https://schema.org/InStock',
					'wenige'      => 'https://schema.org/LimitedAvailability',
					'ausverkauft' => 'https://schema.org/SoldOut',
				);
				$offer  = array(
					'@type'         => 'Offer',
					'url'           => $url,
					'priceCurrency' => 'CHF',
				);
				if ( isset( $row['status'], $status[ $row['status'] ] ) ) {
					$offer['availability'] = $status[ $row['status'] ];
				}
				$verkaufsstart = tha_parse_datetime( $settings['ticket_sale_start'] );
				if ( $verkaufsstart ) {
					$offer['validFrom'] = $verkaufsstart->format( 'c' );
				}
				$event['offers'] = $offer;
			}

			$events[] = $event;
		}

		return $events;
	}

	/**
	 * Die Beschreibung fürs Suchergebnis — ein Satz, der Stück, Zeitraum
	 * und Ort nennt. Gut 150 Zeichen, mehr zeigt Google ohnehin nicht.
	 */
	public static function description( array $settings ) {
		$titel = tha_guillemets( $settings['play_title'] );
		$dates = (array) $settings['dates'];

		$stueck = trim( $titel . ' — ' . $settings['play_genre'] . ' von ' . $settings['play_author'] . '.' );

		if ( $dates ) {
			$erste  = $dates[0]['date'];
			$letzte = $dates[ count( $dates ) - 1 ]['date'];
			return $stueck . ' ' . sprintf(
				/* translators: 1: Anzahl, 2: erstes Datum, 3: letztes Datum mit Jahr, 4: Ort */
				__( '%1$d Vorstellungen vom %2$s bis %3$s, %4$s.', 'theater-auw' ),
				count( $dates ),
				tha_date_short( $erste ),
				tha_date_short_year( $letzte ),
				$settings['venue_name']
			);
		}

		return $stueck;
	}

	/**
	 * Das Vorschaubild: das Stück-Bild, sonst das Website-Icon, sonst nichts.
	 */
	private static function image_url( array $settings ) {
		if ( ! empty( $settings['stueck_image_id'] ) && function_exists( 'wp_get_attachment_image_url' ) ) {
			$url = wp_get_attachment_image_url( absint( $settings['stueck_image_id'] ), 'large' );
			if ( $url ) {
				return $url;
			}
		}
		if ( function_exists( 'get_site_icon_url' ) ) {
			$url = get_site_icon_url( 512 );
			if ( $url ) {
				return $url;
			}
		}
		return '';
	}

	/**
	 * Kümmert sich schon ein SEO-Plugin um Beschreibung und Vorschau?
	 */
	private static function seo_plugin_active() {
		return defined( 'WPSEO_VERSION' )       // Yoast
			|| class_exists( 'RankMath' )
			|| defined( 'AIOSEO_VERSION' )      // All in One SEO
			|| defined( 'SEOPRESS_VERSION' );
	}
}
