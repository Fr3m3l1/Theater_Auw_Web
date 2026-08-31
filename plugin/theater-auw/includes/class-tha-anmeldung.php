<?php
/**
 * Sponsoring-Anmeldung — die Anmeldeseite im Theater-Design, der Hinweis
 * auf der Hauptseite und die Frist, die beides automatisch schliesst.
 *
 * Die Anmeldeseite («Anmeldung Sponsoring») wurde im Divi-Builder gebaut und
 * trägt ein echtes Formular, das etwas entgegennimmt. Anders als die übrigen
 * Seiten lässt sie sich deshalb nicht in einen Shortcode einschliessen: der
 * Shortcode-Rahmen würde den Formular-Stilen des Themes den Boden entziehen
 * (der eigene Reset stellt alles unter `.tha` auf den Browser zurück).
 *
 * Stattdessen wird sie von aussen umgestaltet:
 *
 *   - erkannt über ihre Seitennummer (Einstellung «Sponsoring»),
 *   - bekommt dieselben Körperklassen wie die übrigen Theater-Seiten
 *     (`tha-page`, `tha-typo`, … — über `is_theater_page`),
 *   - ein dunkler Seitenkopf wird vorgeschaltet, der Inhalt bleibt liegen.
 *
 * Das Formular selbst bleibt unangetastet — es steht ausserhalb der
 * `.tha`-Wurzel und behält genau die Stile, die es zum Funktionieren
 * braucht. Nichts hier schreibt in den Seiteninhalt ein.
 */

defined( 'ABSPATH' ) || exit;

class Tha_Anmeldung {

	public static function init() {
		add_filter( 'body_class', array( __CLASS__, 'body_class' ), 20 );
		add_filter( 'the_content', array( __CLASS__, 'render_pagehead' ), 30 );
		add_action( 'init', array( __CLASS__, 'block_past_deadline' ), 1 );
	}

	// -----------------------------------------------------------------
	// Auskunft — die reine Logik, so geschrieben dass sie testbar ist.
	// -----------------------------------------------------------------

	/**
	 * Die Seite mit dem Anmeldeformular.
	 */
	public static function page_id( array $settings = null ) {
		$settings = $settings ? $settings : Tha_Settings::get();
		return absint( $settings['anmeldung_page_id'] );
	}

	/**
	 * Die Bestätigungsseite, auf die das Formular nach dem Absenden leitet.
	 * Eigene Nummer, damit sie sich nicht versehentlich mit der
	 * Anmeldeseite deckt — dieselbe Erkennung, aber ohne Frist-Logik.
	 */
	public static function ok_page_id( array $settings = null ) {
		$settings = $settings ? $settings : Tha_Settings::get();
		return absint( $settings['anmeldung_ok_page_id'] );
	}

	/**
	 * Ist die abgefragte Seite eine der beiden Sponsoring-Seiten?
	 */
	public static function is_anmeldung_page( $post_id ) {
		$settings = Tha_Settings::get();
		return (int) $post_id === self::page_id( $settings )
			|| (int) $post_id === self::ok_page_id( $settings );
	}

	/**
	 * Adresse der Anmeldeseite — leer, wenn keine Seite gesetzt ist oder die
	 * gesetzte Nummer niemandem gehört.
	 */
	public static function signup_url( array $settings = null ) {
		$id = self::page_id( $settings );
		if ( ! $id ) {
			return '';
		}
		$url = get_permalink( $id );
		return $url ? (string) $url : '';
	}

	/**
	 * Die Frist als Zeitpunkt — exklusiv: Mitternacht NACH dem Stichtag.
	 * Wer bis zum 8. Oktober anmeldet, ist dabei; am 9. Oktober ist zu.
	 * Kein Datum eingetragen → keine Frist, die Anmeldung bleibt offen.
	 *
	 * @return DateTimeImmutable|null
	 */
	public static function deadline( array $settings = null ) {
		$settings = $settings ? $settings : Tha_Settings::get();
		$raw      = trim( (string) $settings['anmeldung_deadline'] );
		if ( '' === $raw ) {
			return null;
		}
		$dt = tha_parse_date( $raw );
		if ( ! $dt ) {
			return null;
		}
		return $dt->modify( '+1 day' );
	}

	/**
	 * Ist die Frist abgelaufen?
	 *
	 * @param DateTimeImmutable|null $now Für Tests; sonst die echte Uhr.
	 */
	public static function deadline_passed( array $settings = null, DateTimeImmutable $now = null ) {
		$dt = self::deadline( $settings );
		if ( ! $dt ) {
			return false;
		}
		$now = $now ? $now : tha_now();
		return $now >= $dt;
	}

	/**
	 * Nimmt die Anmeldung noch etwas entgegen? Ohne Frist immer ja.
	 */
	public static function is_open( array $settings = null, DateTimeImmutable $now = null ) {
		return ! self::deadline_passed( $settings, $now );
	}

	/**
	 * Der Stichtag lesbar: `8. Oktober 2026`. Leer ohne Frist.
	 *
	 * Monatsname von Hand — `date()` gäbe in einer englischen Serverumgebung
	 * «October» aus, und die Vorlagen sprechen Deutsch.
	 */
	public static function deadline_label( array $settings = null ) {
		$settings = $settings ? $settings : Tha_Settings::get();
		$raw      = trim( (string) $settings['anmeldung_deadline'] );
		if ( '' === $raw ) {
			return '';
		}
		$dt = tha_parse_date( $raw );
		if ( ! $dt ) {
			return '';
		}
		$months = array( '', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember' );
		return sprintf( '%d. %s %s', (int) $dt->format( 'j' ), $months[ (int) $dt->format( 'n' ) ], $dt->format( 'Y' ) );
	}

	// -----------------------------------------------------------------
	// Anmeldeseite
	// -----------------------------------------------------------------

	/**
	 * Körperklassen für die Anmeldeseite — zusätzlich zu den üblichen
	 * Theater-Klassen, die `Tha_Pagechrome` über `is_theater_page` vergibt.
	 *
	 * `tha-anmeldung-zu` ist die Hand, die nach Ablauf der Frist zugreift:
	 * an ihr hängt das Ausblenden des Formulars im Stylesheet.
	 */
	public static function body_class( $classes ) {
		if ( is_admin() || ! is_singular() ) {
			return $classes;
		}
		$id = (int) get_queried_object_id();
		if ( ! self::is_anmeldung_page( $id ) ) {
			return $classes;
		}

		$classes[] = 'tha-anmeldung';
		if ( $id === self::page_id() ) {
			// Die Frist-Schalter nur auf der Anmeldeseite selbst — die
			// Bestätigungsseite soll nie ausgeblendet werden.
			$classes[] = self::is_open() ? 'tha-anmeldung-offen' : 'tha-anmeldung-zu';
		}

		return $classes;
	}

	/**
	 * Der dunkle Seitenkopf vor den Inhalt der Anmeldeseite.
	 *
	 * Als `the_content`-Filter, damit auch ein Page-Builder-Inhalt (Divi)
	 * dahinter liegen bleibt — dort sieht das Plugin sonst nichts von seinem
	 * Shortcode. Priorität 30: nach dem Seiteninhalt, wie er ankommt, davor,
	 * dass andere Plugins ihn wegfischen.
	 */
	public static function render_pagehead( $content ) {
		if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		$id = (int) get_queried_object_id();
		if ( ! self::is_anmeldung_page( $id ) ) {
			return $content;
		}

		$settings = Tha_Settings::get();

		// Auf welcher der beiden Seiten stehen wir? Die Anmeldeseite trägt
		// Formular + Frist, die Bestätigungsseite nur den Dank — der Kopf
		// ist derselbe, die Texte sind eigene.
		$ok  = ( $id === self::ok_page_id( $settings ) );
		$open   = self::is_open( $settings );
		$lead   = trim( $ok ? (string) $settings['anmeldung_ok_lead'] : (string) $settings['anmeldung_lead'] );
		$kicker = trim( $ok ? (string) $settings['anmeldung_ok_kicker'] : (string) $settings['anmeldung_kicker'] );
		$titel  = trim( $ok ? (string) $settings['anmeldung_ok_title'] : (string) $settings['anmeldung_title'] );
		$subtitle = $ok ? trim( (string) $settings['anmeldung_title'] ) : '';

		ob_start();
		include THA_DIR . 'public/views/partial-anmeldung-pagehead.php';
		$kopf = ob_get_clean();

		$phase  = Tha_Phase::current();
		$wurzel = '<div class="tha tha--' . esc_attr( $phase ) . ' tha--width-' . esc_attr( $settings['width_mode'] ) . '">'
			. $kopf
			. '</div>';

		// Der Inhalt bleibt, wie er ist — bewusst ausserhalb der `.tha`-
		// Wurzel: nur so behalten die Divi-Module ihre Theme-Stile.
		return $wurzel . '<div class="tha-anmeldung-content">' . $content . '</div>';
	}

	/**
	 * Nach Ablauf der Frist will die Seite nichts mehr entgegennehmen.
	 *
	 * Das Divi-Anmeldeformular verarbeitet seinen POST beim Rendern derselben
	 * Seite — wer nach der Frist abschickt, bekommt die Antwort «zu spät»
	 * also erst NACH dem Abschicken. Deshalb wird der POST hier früher
	 * abgefangen: vor der Verarbeitung, mit Rückleitung auf die Seite samt
	 * Hinweis. Das Formular selbst ist dann ohnehin schon ausgeblendet.
	 */
	public static function block_past_deadline() {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}
		if ( empty( $_POST ) || self::is_open() ) {
			return;
		}
		$id = self::page_id();
		if ( ! $id ) {
			return;
		}

		// Nur die Divi-Formularfelder identifizieren den Anmelde-POST,
		// damit nicht jede beliebige POST-Anfrage auf dieser Seite
		// abgeschossen wird. So bleibt die Sperre auch dann harmlos, wenn
		// die Seite später noch anderes enthält.
		$hat_formular = false;
		foreach ( array_keys( $_POST ) as $key ) {
			if ( 0 === strpos( (string) $key, 'et_pb_contact' ) ) {
				$hat_formular = true;
				break;
			}
		}
		if ( ! $hat_formular ) {
			return;
		}

		// Nach der Frist geht kein POST mehr durch — aber statt die Seite
		// mit dem ausgeblendeten Formular zu zeigen, führen wir die Leute
		// direkt auf die Bestätigungsseite. Wer es versucht, sieht den
		// Dank statt eines leeren Formulars.
		$ok_id = self::ok_page_id();
		$url   = $ok_id ? get_permalink( $ok_id ) : get_permalink( $id );
		if ( $url ) {
			wp_safe_redirect( add_query_arg( 'anmeldung', 'zu', $url ) );
			exit;
		}
	}
}