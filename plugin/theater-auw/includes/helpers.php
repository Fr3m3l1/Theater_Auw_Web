<?php
/**
 * Kleine Helfer. Bewusst als Funktionen, damit die Templates lesbar bleiben.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Titel in Guillemets setzen — «s'Jubiläum». Doppelte Anführungszeichen
 * werden vermieden, wenn der Titel sie schon mitbringt.
 */
function tha_guillemets( $title ) {
	$title = trim( (string) $title );
	if ( '' === $title ) {
		return '';
	}
	if ( 0 === strpos( $title, '«' ) ) {
		return $title;
	}
	return '«' . $title . '»';
}

/**
 * Wochentag-Kürzel auf Deutsch, wie auf der alten Seite: MO DI MI DO FR SA SO.
 */
function tha_weekday_short( $ymd ) {
	$days = array( 'SO', 'MO', 'DI', 'MI', 'DO', 'FR', 'SA' );
	$dt   = tha_parse_date( $ymd );
	if ( ! $dt ) {
		return '';
	}
	return $days[ (int) $dt->format( 'w' ) ];
}

/**
 * Datum als `14.11.` — Schweizer Kurzform ohne Jahr, wie im Programmheft.
 */
function tha_date_short( $ymd ) {
	$dt = tha_parse_date( $ymd );
	return $dt ? $dt->format( 'd.m.' ) : '';
}

/**
 * Datum mit Jahr: `28.11.2026`. Für Spannen, wo sonst «28.11. 2026» mit
 * Leerzeichen entstünde.
 */
function tha_date_short_year( $ymd ) {
	$dt = tha_parse_date( $ymd );
	return $dt ? $dt->format( 'd.m.Y' ) : '';
}

/**
 * Datum ausgeschrieben: `Samstag, 14. November 2026`.
 */
function tha_date_long( $ymd ) {
	$dt = tha_parse_date( $ymd );
	if ( ! $dt ) {
		return '';
	}
	$days   = array( 'Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag' );
	$months = array( '', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember' );
	return sprintf(
		'%s, %d. %s %s',
		$days[ (int) $dt->format( 'w' ) ],
		(int) $dt->format( 'j' ),
		$months[ (int) $dt->format( 'n' ) ],
		$dt->format( 'Y' )
	);
}

/**
 * Datum ohne Wochentag: `14. November 2026`.
 *
 * Für die Updates. Dort trägt der Wochentag nichts bei — bei einer Vorstellung
 * schon («Samstag» sagt mehr als das Datum), bei einer Mitteilung nicht.
 */
function tha_date_written( $ymd ) {
	$dt = tha_parse_date( $ymd );
	if ( ! $dt ) {
		return '';
	}
	$months = array( '', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember' );
	return sprintf(
		'%d. %s %s',
		(int) $dt->format( 'j' ),
		$months[ (int) $dt->format( 'n' ) ],
		$dt->format( 'Y' )
	);
}

/**
 * `20.00` → `20.00 Uhr`. Schweizer Punktnotation, nicht 20:00.
 */
function tha_time_label( $time ) {
	$time = trim( (string) $time );
	return '' === $time ? '' : $time . ' Uhr';
}

/**
 * Datum (Y-m-d) in der Zeitzone der Website parsen.
 */
function tha_parse_date( $ymd ) {
	$ymd = trim( (string) $ymd );
	if ( '' === $ymd ) {
		return null;
	}
	$dt = DateTimeImmutable::createFromFormat( 'Y-m-d|', $ymd, tha_timezone() );
	return $dt instanceof DateTimeImmutable ? $dt : null;
}

/**
 * Datum + Uhrzeit (`Y-m-d H:i`) in der Zeitzone der Website parsen.
 */
function tha_parse_datetime( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return null;
	}
	$dt = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $value, tha_timezone() );
	if ( ! $dt instanceof DateTimeImmutable ) {
		$dt = DateTimeImmutable::createFromFormat( 'Y-m-d|', $value, tha_timezone() );
	}
	return $dt instanceof DateTimeImmutable ? $dt : null;
}

/**
 * Zeitzone der Website — mit Fallback auf Europe/Zurich, falls WordPress auf
 * UTC steht (kommt bei Vereinsseiten öfter vor, als einem lieb ist).
 */
function tha_timezone() {
	static $tz = null;
	if ( null !== $tz ) {
		return $tz;
	}
	if ( function_exists( 'wp_timezone' ) ) {
		$tz = wp_timezone();
		if ( 'UTC' === $tz->getName() ) {
			$tz = new DateTimeZone( 'Europe/Zurich' );
		}
		return $tz;
	}
	$tz = new DateTimeZone( 'Europe/Zurich' );
	return $tz;
}

/**
 * Jetzt, in der Zeitzone der Website.
 *
 * `$GLOBALS['tha_fake_now']` wird ausschliesslich von der lokalen Vorschau
 * (preview/render.php) gesetzt, damit sich jede Phase zu einem passenden
 * Zeitpunkt ansehen lässt. In WordPress existiert die Variable nie.
 */
function tha_now() {
	if ( isset( $GLOBALS['tha_fake_now'] ) ) {
		$fake = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $GLOBALS['tha_fake_now'], tha_timezone() );
		if ( $fake instanceof DateTimeImmutable ) {
			return $fake;
		}
	}
	return new DateTimeImmutable( 'now', tha_timezone() );
}

/**
 * Nächste lokale Mitternacht (plus fünf Minuten) als UTC-Timestamp — für den
 * täglichen Cache-Purge.
 */
function tha_next_local_midnight_utc() {
	return tha_now()->modify( 'tomorrow 00:05' )->getTimestamp();
}

/**
 * Ticket-URL für eine Vorstellung: die Zeile gewinnt, sonst die globale URL.
 */
function tha_ticket_url( $show = array() ) {
	if ( ! empty( $show['ticket_url'] ) ) {
		return $show['ticket_url'];
	}
	$settings = Tha_Settings::get();
	return $settings['ticket_url'];
}
