<?php
/**
 * Test der SEO-Bausteine.
 *
 * Die strukturierten Vorstellungsdaten sind der heikelste Teil: ein
 * falsches Datumsformat, und Google wirft den ganzen Datensatz weg.
 * Deshalb hier: Zeitzone (Winterzeit!), vergangene Termine, Ticket-Status.
 *
 * Aufruf:  php preview/test-seo.php
 */

require __DIR__ . '/wp-stubs.php';

$plugin = dirname( __DIR__ ) . '/plugin/theater-auw';
define( 'THA_VERSION', '0.0.0' );
define( 'THA_FILE', $plugin . '/theater-auw.php' );
define( 'THA_DIR', $plugin . '/' );
define( 'THA_URL', '' );

require THA_DIR . 'includes/helpers.php';
require THA_DIR . 'includes/class-tha-settings.php';
require THA_DIR . 'includes/class-tha-seo.php';

$fails = 0;
$runs  = 0;

function check( $label, $expected, $actual ) {
	global $fails, $runs;
	$runs++;
	$ok = ( (string) $expected === (string) $actual );
	if ( ! $ok ) {
		$fails++;
	}
	printf( "%s  %-52s erwartet=%-44s erhalten=%s\n", $ok ? 'ok  ' : 'FAIL', $label, $expected, $actual );
}

// Anfang September: alle sechs Vorstellungen stehen noch bevor.
$GLOBALS['tha_fake_now'] = '2026-09-01 12:00';

$settings = Tha_Settings::defaults();

echo "Vorstellungen als strukturierte Daten:\n\n";

$events = Tha_Seo::events( $settings );

check( 'alle sechs Vorstellungen dabei', '6', (string) count( $events ) );
check( 'Typ ist TheaterEvent',           'TheaterEvent', $events[0]['@type'] );
check( 'Name nennt Stück und Anlass',    'Theater Auw: «s\'Jubiläum» — Premiere', $events[0]['name'] );
// November ist Winterzeit: +01:00, nicht +02:00. Und die Uhrzeit «20.00»
// muss als 20:00 gelesen werden, nicht als 20 Uhr 0.0 Minuten.
check( 'Beginn in Schweizer Winterzeit', '2026-11-14T20:00:00+01:00', $events[0]['startDate'] );
check( 'Ort ist die Halle',              'Mehrzweckhalle Auw', $events[0]['location']['name'] );
check( 'PLZ und Ort stehen dabei',       '5644', $events[0]['location']['address']['postalCode'] );
check( 'ohne Ticket-Link kein Angebot',  'nein', isset( $events[0]['offers'] ) ? 'ja' : 'nein' );

// Mit Ticket-Link und Status: das Angebot samt Verfügbarkeit.
$mit = $settings;
$mit['ticket_url'] = 'https://reservation.ticketleo.com/event/99999/x';
$mit['dates'][0]['status'] = 'ausverkauft';
$mit['dates'][1]['status'] = 'wenige';
$mit['dates'][1]['ticket_url'] = 'https://reservation.ticketleo.com/event/99999/x?d=2';

$ev = Tha_Seo::events( $mit );
check( 'Angebot zeigt auf Ticketleo',    'https://reservation.ticketleo.com/event/99999/x', $ev[0]['offers']['url'] );
check( 'eigener Link der Zeile gewinnt', 'https://reservation.ticketleo.com/event/99999/x?d=2', $ev[1]['offers']['url'] );
check( 'ausverkauft wird gemeldet',      'https://schema.org/SoldOut', $ev[0]['offers']['availability'] );
check( 'wenige Plätze ebenso',           'https://schema.org/LimitedAvailability', $ev[1]['offers']['availability'] );
// September ist Sommerzeit: der Verkaufsstart trägt +02:00.
check( 'Verkaufsstart mit Sommerzeit',   '2026-09-15T09:00:00+02:00', $ev[0]['offers']['validFrom'] );

echo "\nVergangene Vorstellungen fallen weg:\n\n";

$GLOBALS['tha_fake_now'] = '2026-11-20 12:00';
$rest = Tha_Seo::events( $settings );
check( 'nach dem 20.11. bleiben vier',   '4', (string) count( $rest ) );
check( 'die erste ist der 21.11.',       '2026-11-21', substr( $rest[0]['startDate'], 0, 10 ) );

// Der Tag einer Vorstellung zählt bis Mitternacht: wer am Aufführungsabend
// sucht, soll den Termin noch finden.
$GLOBALS['tha_fake_now'] = '2026-11-28 19:00';
$heute = Tha_Seo::events( $settings );
check( 'die Derniere gilt am Abend noch', '1', (string) count( $heute ) );

echo "\nBeschreibung fürs Suchergebnis:\n\n";

$text = Tha_Seo::description( $settings );
check(
	'nennt Stück, Zeitraum und Ort',
	'«s\'Jubiläum» — Lustspiel in 3 Akten von Dieter Gygli. 6 Vorstellungen vom 14.11. bis 28.11.2026, Mehrzweckhalle Auw.',
	$text
);
// mbstring fehlt auf manchen Systemen — Zeichen statt Bytes darum per Regex.
$zeichen = preg_match_all( '/./us', $text );
check( 'kurz genug für das Snippet', 'ja', $zeichen <= 160 ? 'ja' : 'nein' );

$ohne = $settings;
$ohne['dates'] = array();
check(
	'ohne Spieldaten bleibt das Stück',
	'«s\'Jubiläum» — Lustspiel in 3 Akten von Dieter Gygli.',
	Tha_Seo::description( $ohne )
);

printf( "\n%d Prüfungen, %d Fehler\n", $runs, $fails );
exit( $fails > 0 ? 1 : 0 );
