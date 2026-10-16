<?php
/**
 * Test der Phasenauflösung.
 *
 * Kein PHPUnit — für eine Vereinsseite wäre das Overkill. Das hier läuft mit
 * blossem `php preview/test-phase.php` und prüft genau die Logik, bei der ein
 * Fehler teuer wäre: dass am Premierenabend die richtige Phase steht.
 */

require __DIR__ . '/wp-stubs.php';

$plugin = dirname( __DIR__ ) . '/plugin/theater-auw';
define( 'THA_VERSION', '1.6.1' );
define( 'THA_FILE', $plugin . '/theater-auw.php' );
define( 'THA_DIR', $plugin . '/' );
define( 'THA_URL', '' );

require THA_DIR . 'includes/helpers.php';
require THA_DIR . 'includes/class-tha-settings.php';
require THA_DIR . 'includes/class-tha-phase.php';

$settings = json_decode( file_get_contents( __DIR__ . '/fixtures.json' ), true );

$fails = 0;
$runs  = 0;

function check( $label, $expected, $actual ) {
	global $fails, $runs;
	$runs++;
	$ok = ( $expected === $actual );
	if ( ! $ok ) {
		$fails++;
	}
	printf(
		"%s  %-52s erwartet=%-13s erhalten=%s\n",
		$ok ? 'ok  ' : 'FAIL',
		$label,
		$expected,
		$actual
	);
}

function at( $when ) {
	return new DateTimeImmutable( $when, new DateTimeZone( 'Europe/Zurich' ) );
}

echo "Fahrplan: Vorverkauf ab 13.10.2026 00:00 · Spielzeit 14.11.2026 00:00 · Danke 29.11.2026 00:00\n\n";

// --- Kalenderlogik ---------------------------------------------------

check( 'lange davor',                         'vorbereitung', Tha_Phase::from_schedule( $settings, at( '2026-07-25 12:00' ) ) );
check( 'eine Minute vor Vorverkauf',          'vorbereitung', Tha_Phase::from_schedule( $settings, at( '2026-10-12 23:59' ) ) );
check( 'exakt zum Vorverkauf',                'vorverkauf',   Tha_Phase::from_schedule( $settings, at( '2026-10-13 00:00' ) ) );
check( 'mitten im Vorverkauf',                'vorverkauf',   Tha_Phase::from_schedule( $settings, at( '2026-10-20 12:00' ) ) );
check( 'Vorabend der Premiere',               'vorverkauf',   Tha_Phase::from_schedule( $settings, at( '2026-11-13 23:59' ) ) );
check( 'Premierentag 00:00',                  'spielzeit',    Tha_Phase::from_schedule( $settings, at( '2026-11-14 00:00' ) ) );
check( 'Premierenabend',                      'spielzeit',    Tha_Phase::from_schedule( $settings, at( '2026-11-14 20:00' ) ) );
check( 'Derniere-Abend',                      'spielzeit',    Tha_Phase::from_schedule( $settings, at( '2026-11-28 20:00' ) ) );
check( 'Nacht nach der Derniere',             'spielzeit',    Tha_Phase::from_schedule( $settings, at( '2026-11-28 23:59' ) ) );
check( 'Tag nach der Derniere',               'danke',        Tha_Phase::from_schedule( $settings, at( '2026-11-29 00:00' ) ) );
check( 'Wochen danach',                       'danke',        Tha_Phase::from_schedule( $settings, at( '2027-02-01 12:00' ) ) );

// --- Zeitumstellung ---------------------------------------------------
// Die Saison liegt hinter dem Wechsel auf Winterzeit (letzter Sonntag im
// Oktober) und der Vorverkauf läuft über die Umstellung hinweg. Wenn die
// Zeitzone falsch behandelt würde, fiele es genau hier auf.

echo "\nZeitumstellung (25.10.2026, 03:00 → 02:00):\n";
check( 'vor der Umstellung',                  'vorverkauf',   Tha_Phase::from_schedule( $settings, at( '2026-10-24 12:00' ) ) );
check( 'nach der Umstellung',                 'vorverkauf',   Tha_Phase::from_schedule( $settings, at( '2026-10-26 12:00' ) ) );
check( 'Offset vor Umstellung ist +02:00',    '+02:00',       at( '2026-10-24 12:00' )->format( 'P' ) );
check( 'Offset nach Umstellung ist +01:00',   '+01:00',       at( '2026-10-26 12:00' )->format( 'P' ) );

// --- Manueller Override -----------------------------------------------

echo "\nManuelle Übersteuerung:\n";
$manual                   = $settings;
$manual['phase_mode']     = 'manual';
$manual['phase_manual']   = 'danke';
update_option( Tha_Settings::OPTION, $manual );
check( 'Override schlägt den Kalender',       'danke',        Tha_Phase::current() );

$auto = $settings;
update_option( Tha_Settings::OPTION, $auto );

// --- Abgeleitete Zustände ---------------------------------------------

echo "\nAbgeleitete Zustände:\n";

$GLOBALS['tha_fake_now'] = '2026-09-01 12:00';
check( 'Verkauf vor Verkaufsstart nicht live', 'nein',        Tha_Phase::sale_is_live( $settings ) ? 'ja' : 'nein' );

// Zwischen Phasenbeginn und Verkaufsstart: die Phase läuft, der Verkauf noch
// nicht — die Startseite zeigt hier «Vorverkauf startet bald» samt Countdown
// auf den Verkaufsstart.
$GLOBALS['tha_fake_now'] = '2026-10-20 12:00';
check( 'Phase läuft vor dem Verkaufsstart',   'vorverkauf',   Tha_Phase::from_schedule( $settings, at( '2026-10-20 12:00' ) ) );
check( 'und der Verkauf ist noch nicht offen', 'nein',       Tha_Phase::sale_is_live( $settings ) ? 'ja' : 'nein' );
$cd = Tha_Phase::countdown( 'vorverkauf', $settings );
check( 'Countdown zielt auf den Verkaufsstart', 'noch 10 Tage', $cd['fallback'] );

$GLOBALS['tha_fake_now'] = '2026-11-01 12:00';
check( 'Verkauf nach Verkaufsstart live',      'ja',          Tha_Phase::sale_is_live( $settings ) ? 'ja' : 'nein' );

$GLOBALS['tha_fake_now'] = '2026-11-26 15:00';
$today = Tha_Phase::show_today( $settings );
check( 'am 26.11. gibt es eine Vorstellung',   '2026-11-26',  $today ? $today['date'] : 'keine' );

$GLOBALS['tha_fake_now'] = '2026-11-27 15:00';
check( 'am 27.11. gibt es keine',              'keine',       Tha_Phase::show_today( $settings ) ? 'doch' : 'keine' );

$GLOBALS['tha_fake_now'] = '2026-11-20 12:00';
$shows = Tha_Phase::shows( 'spielzeit', $settings );
$past  = 0;
foreach ( $shows as $s ) {
	if ( 'vorbei' === $s['effective_status'] ) {
		$past++;
	}
}
check( 'am 20.11. sind 2 Vorstellungen vorbei', '2',          (string) $past );

$shows_danke = Tha_Phase::shows( 'danke', $settings );
$all_past    = true;
foreach ( $shows_danke as $s ) {
	if ( 'vorbei' !== $s['effective_status'] ) {
		$all_past = false;
	}
	if ( $s['bookable'] ) {
		$all_past = false;
	}
}
check( 'in Danke ist alles vorbei und nichts buchbar', 'ja',  $all_past ? 'ja' : 'nein' );

$GLOBALS['tha_fake_now'] = '2026-08-01 12:00';
$shows_vorb  = Tha_Phase::shows( 'vorbereitung', $settings );
$any_book    = false;
foreach ( $shows_vorb as $s ) {
	if ( $s['bookable'] ) {
		$any_book = true;
	}
}
check( 'in Vorbereitung ist nichts buchbar',   'ja',          $any_book ? 'nein' : 'ja' );

// --- Countdown ---------------------------------------------------------

echo "\nCountdown:\n";

// Am Verkaufsstart selbst steht der Termin mit Uhrzeit — «noch 0 Tage»
// wäre technisch richtig und menschlich falsch.
$GLOBALS['tha_fake_now'] = '2026-10-30 12:00';
$cd = Tha_Phase::countdown( 'vorverkauf', $settings );
check( 'am Verkaufsstart mit Uhrzeit',        'Der Vorverkauf startet heute um 18.00 Uhr', $cd['fallback'] );

$GLOBALS['tha_fake_now'] = '2026-11-13 12:00';
$cd = Tha_Phase::countdown( 'vorverkauf', $settings );
check( 'Tag vor der Premiere: 1 Tag',          '1',           (string) $cd['days'] );

$GLOBALS['tha_fake_now'] = '2026-11-26 15:00';
$cd = Tha_Phase::countdown( 'spielzeit', $settings );
check( 'am Spieltag kein "noch 0 Tage"',       'Heute um 20.00 Uhr', $cd['fallback'] );

$GLOBALS['tha_fake_now'] = '2026-12-05 12:00';
check( 'Danke hat keinen Countdown',           'keiner',      Tha_Phase::countdown( 'danke', $settings ) ? 'doch' : 'keiner' );

// ---------------------------------------------------------------------

printf( "\n%d Prüfungen, %d Fehler\n", $runs, $fails );
exit( $fails > 0 ? 1 : 0 );
