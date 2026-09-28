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
		// Dazu die Bestaetigungsseite, auf die das Formular leitet.
		if ( Tha_Anmeldung::is_anmeldung_page( $post_id ) ) {
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
		// Frueher traf das ALLE Beitraege der Website. Das setzte voraus,
		// dass auf ihr nur Theater steht. Auf stvauw.ch liegt aber der
		// Vereinsblog auf derselben Website unter /blog/ — dessen Beitraege
		// bekamen dadurch Theater-Schrift und Papierhintergrund. Getroffen
		// wird deshalb nur, was unter der Adresse der Theater-Seite liegt
		// (siehe theater_prefix()).
		if ( ! empty( $settings['post_singles'] )
			&& function_exists( 'is_singular' )
			&& is_singular( 'post' )
			&& self::post_in_theater( $post_id, $settings ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Liegt ein Beitrag unter der Adresse der Theater-Seite?
	 *
	 * Die Theater-Adresse wird nicht an den Beitraegen erraten, sondern aus
	 * den Referenzen in den Einstellungen abgeleitet: die Theater-Seite
	 * selbst, die beiden Anmeldeseiten und die Rueckblick-Archivseite. Aus
	 * deren Adressen ergibt sich der gemeinsame Pfad — auf stvauw.ch
	 * `/theater/`. Ein Beitrag wie /theater/2026/06/2738/ liegt darunter
	 * und ist ein Bericht; /blog/2026/09/06/gruempi-merenschwand/ liegt
	 * daneben und bleibt ein gewoehnlicher Blogbeitrag.
	 *
	 * Kennt die Installation keine der Referenzen, gilt der ganze Bestand
	 * als Theater — eine Website, auf der nur Theater steht, braucht keine
	 * Abgrenzung. So bleibt das alte Verhalten fuer reine Theater-Seiten
	 * erhalten, und erst der gemischte Fall wird eingegrenzt.
	 */
	public static function post_in_theater( $post_id, $settings = null ) {
		$prefix = self::theater_prefix( $settings );
		if ( '/' === $prefix ) {
			return true;
		}
		$pfad = tha_url_path( (string) get_permalink( (int) $post_id ) );
		return $pfad === $prefix || 0 === strpos( $pfad, $prefix );
	}

	/**
	 * Der gemeinsame Pfad der Theater-Seiten, aus den Referenzen in den
	 * Einstellungen gezogen. Er endet immer auf `/` — `/theater/` statt
	 * `/theater` —, damit ein danebenliegendes `/theaterfest/` nicht mit
	 * erwischt wird. Ohne verwertbare Referenz bleibt `/` und meint: alles.
	 */
	public static function theater_prefix( $settings = null ) {
		$settings = $settings ? $settings : Tha_Settings::get();
		foreach ( self::theater_references( $settings ) as $url ) {
			$dir = tha_dir_prefix( tha_url_path( $url ) );
			if ( '/' !== $dir ) {
				return $dir;
			}
		}
		return '/';
	}

	/**
	 * Die konfigurierten Adressen, an denen die Theater-Seite erkennbar ist.
	 * Leere oder unbrauchbare Eintraege fallen weg — eine Seiten-Nummer, die
	 * auf nichts zeigt, darf die Abgrenzung nicht aufhalten.
	 */
	private static function theater_references( $settings ) {
		$refs = array();

		foreach ( array( 'page_id', 'anmeldung_page_id', 'anmeldung_ok_page_id' ) as $key ) {
			if ( empty( $settings[ $key ] ) ) {
				continue;
			}
			$url = trim( (string) get_permalink( (int) $settings[ $key ] ) );
			if ( false !== strpos( $url, '/' ) ) {
				$refs[] = $url;
			}
		}

		if ( ! empty( $settings['rueckblick_archive_url'] ) ) {
			$refs[] = (string) $settings['rueckblick_archive_url'];
		}

		return $refs;
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
