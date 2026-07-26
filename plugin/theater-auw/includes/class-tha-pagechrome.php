<?php
/**
 * Seitenrahmen aufräumen.
 *
 * Die Theater-Seite ist eine Plakat-Seite: dunkler Hero über die volle Breite,
 * grosse Typografie. Wenn das Theme sie in eine schmale Inhaltsspalte neben
 * eine Blog-Seitenleiste steckt und einen Seitentitel darüberschreibt, wirkt
 * sie zusammengedrückt — genau das war am 25.07.2026 auf der Testseite zu
 * sehen: 739 von 1270 Pixeln, daneben «Neueste Beiträge» und «Archiv».
 *
 * Der saubere Weg wäre, im Theme eine Vorlage «Volle Breite» zu wählen. Weil
 * die aber nicht jedes Theme hat und bei Divi in einem eigenen Metafeld
 * versteckt ist, kann das hier per Schalter erledigt werden.
 *
 * Bewusst als OPT-IN und über Körperklassen: es wird nichts erraten und nichts
 * dauerhaft am Theme verändert. Schalter aus → alles wie vorher.
 */

defined( 'ABSPATH' ) || exit;

class Tha_Pagechrome {

	public static function init() {
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
	}

	/**
	 * Körperklassen nur auf Seiten setzen, auf denen unser Inhalt auch
	 * wirklich steht. Sonst würde die Seitenleiste der ganzen Website
	 * verschwinden.
	 */
	public static function body_class( $classes ) {
		if ( is_admin() || ! Tha_Assets::is_theater_page() ) {
			return $classes;
		}

		$settings = Tha_Settings::get();

		$classes[] = 'tha-page';

		// Der Seitentitel verschwindet, weil auf den Theater-Seiten der Hero
		// oder der Seitenkopf ihn schon traegt — er stuende sonst zweimal da.
		//
		// Auf einem Bericht traegt ihn niemand sonst. «Theater Hock 2026»
		// wegzublenden liesse einen Text ohne Ueberschrift zurueck, der mit
		// «Am Mittwochabend, 3. Juni …» anfaengt. Deshalb hier nicht.
		if ( self::is_bericht() ) {
			$classes[] = 'tha-bericht';
		} elseif ( ! empty( $settings['hide_page_title'] ) ) {
			$classes[] = 'tha-hide-title';
		}
		if ( ! empty( $settings['hide_sidebar'] ) ) {
			$classes[] = 'tha-hide-sidebar';
		}
		if ( ! empty( $settings['content_full_width'] ) ) {
			$classes[] = 'tha-full-content';
		}

		// Nur wenn wirklich eine eigene Navigation gerendert wird. Der
		// Sanitizer haelt das schon zusammen; hier steht die zweite Sperre,
		// falls die Option auf anderem Weg gesetzt wurde.
		if ( ! empty( $settings['nav_replace'] ) && ! empty( $settings['nav_menu_id'] ) ) {
			$classes[] = 'tha-hide-themenav';
		}

		if ( ! empty( $settings['content_typo'] ) ) {
			$classes[] = 'tha-typo';
		}

		return $classes;
	}

	/**
	 * Ein Bericht — also ein Beitrag, den nur der Schalter «Berichte» zu
	 * einer Theater-Seite macht. Seiten mit Shortcode fallen nicht darunter,
	 * auch wenn jemand einen auf einem Beitrag einsetzt: dann traegt der
	 * Shortcode den Titel wieder selbst.
	 */
	private static function is_bericht() {
		if ( ! function_exists( 'is_singular' ) || ! is_singular( 'post' ) ) {
			return false;
		}

		$content = get_post_field( 'post_content', (int) get_queried_object_id() );
		if ( is_string( $content ) ) {
			foreach ( Tha_Shortcode::TAGS as $tag ) {
				if ( has_shortcode( $content, $tag ) ) {
					return false;
				}
			}
		}

		return true;
	}
}
