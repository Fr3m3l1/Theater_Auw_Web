<?php
/**
 * CSS und JS — nur dort laden, wo die Seite auch wirklich gerendert wird.
 */

defined( 'ABSPATH' ) || exit;

class Tha_Assets {

	const FONT_URL = 'https://fonts.googleapis.com/css2?family=Big+Shoulders:opsz,wght@10..72,600;10..72,700&family=Libre+Franklin:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap';

	private static $enqueued      = false;
	private static $printed_fonts = false;

	/**
	 * Rückfallebene für die Schriften.
	 *
	 * Wenn die Seite gerendert wird, ist `wp_head` längst durch. Wurde das
	 * Schriften-Stylesheet bis dahin nicht ausgegeben — weil die Erkennung
	 * nicht gegriffen hat oder ein anderes Plugin es entfernt hat —, geben wir
	 * den <link> hier direkt im Markup aus. Ein <link> im Body ist zulässig
	 * und funktioniert überall; und er kann von nichts mehr weggefiltert
	 * werden.
	 *
	 * @return string
	 */
	public static function font_link_fallback() {
		if ( self::$printed_fonts ) {
			return '';
		}
		$settings = Tha_Settings::get();
		if ( 'theme' === $settings['font_source'] ) {
			return '';
		}
		if ( wp_style_is( 'theater-auw-fonts', 'done' ) || wp_style_is( 'theater-auw-fonts', 'queue' ) ) {
			return '';
		}
		self::$printed_fonts = true;

		return '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
			. '<link rel="stylesheet" href="' . esc_url( self::FONT_URL ) . '">';
	}

	public static function init() {
		// Priorität 20, damit unser <link> in der Praxis nach dem Theme-
		// Stylesheet steht.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue' ), 20 );
	}

	/**
	 * Erkennung in vier Stufen — die letzte fängt Page-Builder ab, bei denen
	 * der Shortcode im Postmeta statt im Content steht.
	 */
	public static function maybe_enqueue() {
		if ( self::is_theater_page() ) {
			self::enqueue();
		}
	}

	/**
	 * Steht auf der gerade abgefragten Seite unser Inhalt?
	 *
	 * Vier Stufen — die letzte fängt Page-Builder ab, bei denen der Shortcode
	 * im Postmeta statt im Seiteninhalt steckt.
	 */
	public static function is_theater_page( $post_id = 0 ) {
		if ( is_admin() ) {
			return false;
		}

		$post_id = $post_id ? (int) $post_id : (int) get_queried_object_id();
		if ( ! $post_id ) {
			return false;
		}

		$content = get_post_field( 'post_content', $post_id );

		if ( is_string( $content ) ) {
			foreach ( Tha_Shortcode::TAGS as $tag ) {
				if ( has_shortcode( $content, $tag ) ) {
					return true;
				}
			}
		}

		if ( function_exists( 'has_block' ) && has_block( 'theater-auw/seite', $post_id ) ) {
			return true;
		}

		$settings = Tha_Settings::get();
		if ( ! empty( $settings['page_id'] ) && (int) $settings['page_id'] === $post_id ) {
			return true;
		}

		// Die Anmeldeseite. Sie traegt weder Shortcode noch Block — ihr
		// Aufbau kommt aus dem Divi-Builder und sie wird von aussen
		// umgestaltet (siehe Tha_Anmeldung). Dieselbe Erkennung wie fuer
		// `page_id`, damit sie dieselben Rahmen-Klassen und Stile bekommt.
		if ( ! empty( $settings['anmeldung_page_id'] ) && (int) $settings['anmeldung_page_id'] === $post_id ) {
			return true;
		}

		// Die Jahrgangsseiten des Rueckblicks. Sie tragen keinen Shortcode —
		// ihr Aufbau kommt aus einer Theme-Builder-Vorlage, und sechzehnmal
		// von Hand etwas einzusetzen waere keine Loesung. Erkannt wird hier
		// am Beitragstyp, nicht am Inhalt.
		if ( ! empty( $settings['rueckblick_singles'] )
			&& ! empty( $settings['rueckblick_post_type'] )
			&& function_exists( 'is_singular' )
			&& is_singular( $settings['rueckblick_post_type'] ) ) {
			return true;
		}

		// Die Berichte. Gleiche Begruendung wie eine Zeile hoeher, anderer
		// Beitragstyp: ein Bericht wird im Editor geschrieben, nicht mit
		// einem Shortcode zusammengesetzt — dort etwas einzusetzen waere die
		// falsche Stelle, und beim naechsten Bericht faengt es von vorne an.
		// Erkannt wird deshalb am Beitragstyp.
		//
		// Dass das gefahrlos alle Beitraege trifft, liegt an dieser Website:
		// stvauw.ch/theater ist eine eigene Site im Netzwerk. Der Vereinsblog
		// mit Turnfesten und Matchberichten liegt auf stvauw.ch und hat mit
		// dieser Installation nichts zu tun.
		if ( ! empty( $settings['post_singles'] )
			&& function_exists( 'is_singular' )
			&& is_singular( 'post' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Idempotent — der Renderer ruft das als Sicherheitsnetz noch einmal auf.
	 */
	public static function enqueue() {
		if ( self::$enqueued ) {
			return;
		}
		self::$enqueued = true;

		$settings = Tha_Settings::get();

		// Bewusst «alles ausser theme» statt «genau google»: ein leerer oder
		// unerwarteter Wert soll die Schriften laden, nicht stillschweigend
		// weglassen. Genau das ist auf dem Testsystem passiert — die Seite
		// fiel wortlos auf die System-Schrift zurück.
		if ( 'theme' !== $settings['font_source'] ) {
			wp_enqueue_style(
				'theater-auw-fonts',
				self::FONT_URL,
				array(),
				null // phpcs:ignore
			);
		}

		wp_enqueue_style(
			'theater-auw',
			THA_URL . 'assets/css/theater-auw.css',
			array(),
			THA_VERSION
		);

		$args = array( 'in_footer' => true );
		if ( version_compare( get_bloginfo( 'version' ), '6.3', '>=' ) ) {
			$args['strategy'] = 'defer';
		}

		wp_enqueue_script(
			'theater-auw',
			THA_URL . 'assets/js/theater-auw.js',
			array(),
			THA_VERSION,
			$args
		);

		wp_add_inline_script(
			'theater-auw',
			'window.THA=window.THA||{};window.THA.locale="de-CH";',
			'before'
		);
	}

	/**
	 * `<link rel="preconnect">` für Google Fonts, sonst kostet der erste Font
	 * eine zusätzliche Verbindungsrunde.
	 */
	public static function resource_hints( $urls, $relation ) {
		if ( 'preconnect' === $relation && wp_style_is( 'theater-auw-fonts', 'enqueued' ) ) {
			$urls[] = array(
				'href'        => 'https://fonts.gstatic.com',
				'crossorigin' => 'anonymous',
			);
		}
		return $urls;
	}
}

add_filter( 'wp_resource_hints', array( 'Tha_Assets', 'resource_hints' ), 10, 2 );
