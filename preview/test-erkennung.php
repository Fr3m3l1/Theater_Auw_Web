<?php
/**
 * Test der Seiten-Erkennung: Was ist eine Theater-Seite, was nicht?
 *
 * Ausloeser: der Blogbeitrag /blog/2026/09/06/gruempi-merenschwand/ bekam
 * Theater-Schrift und Papierhintergrund. Schuld war die Bericht-Erkennung,
 * die ALLE Beitraege der Website als Theater-Seiten behandelte. Getroffen
 * wird jetzt nur noch, was unter der Adresse der Theater-Seite liegt —
 * abgeleitet aus den Referenzen in den Einstellungen (Theater-Seite,
 * Anmeldeseiten, Rueckblick-Archiv).
 */

require __DIR__ . '/wp-stubs.php';

$plugin = dirname( __DIR__ ) . '/plugin/theater-auw';
define( 'THA_VERSION', '1.6.1' );
define( 'THA_FILE', $plugin . '/theater-auw.php' );
define( 'THA_DIR', $plugin . '/' );
define( 'THA_URL', '' );

require THA_DIR . 'includes/helpers.php';
require THA_DIR . 'includes/class-tha-settings.php';
require THA_DIR . 'includes/class-tha-anmeldung.php';
require THA_DIR . 'includes/class-tha-shortcode.php';
require THA_DIR . 'includes/class-tha-assets.php';
require THA_DIR . 'includes/class-tha-pagechrome.php';

$fails = 0;
$runs  = 0;

function check( $label, $expected, $actual ) {
	global $fails, $runs;
	$runs++;
	$ok = ( (string) $expected === (string) $actual );
	if ( ! $ok ) {
		$fails++;
	}
	printf( "%s  %-52s erwartet=%-34s erhalten=%s\n", $ok ? 'ok  ' : 'FAIL', $label, $expected, $actual );
}

// Der haeufigste Fall: die Theater-Seite ist in den Einstellungen gesetzt,
// ihr Pfad /theater/ grenzt die Berichte vom Blog ab.
$saved = Tha_Settings::defaults();
$saved['page_id']      = 42;
$saved['post_singles'] = true;
update_option( Tha_Settings::OPTION, $saved );
Tha_Settings::flush_cache();

$GLOBALS['tha_preview_singular']   = 'post';
$GLOBALS['tha_preview_permalinks'] = array(
	42   => 'https://stvauw.ch/theater/',
	2738 => 'https://stvauw.ch/theater/2026/06/2738/',
	4711 => 'https://stvauw.ch/blog/2026/09/06/gruempi-merenschwand/',
);

echo "Berichte — Abgrenzung gegen den Blog:\n\n";

$GLOBALS['tha_preview_post_id'] = 2738;
check( 'Bericht unter /theater/ ist eine Theater-Seite', 'ja', Tha_Assets::is_theater_page( 2738 ) ? 'ja' : 'nein' );

$GLOBALS['tha_preview_post_id'] = 4711;
check( 'Blogbeitrag unter /blog/ ist keine Theater-Seite', 'nein', Tha_Assets::is_theater_page( 4711 ) ? 'ja' : 'nein' );

echo "\nKörperklassen folgen derselben Grenze:\n\n";

$GLOBALS['tha_preview_post_id'] = 4711;
$blog = Tha_Pagechrome::body_class( array() );
check( 'Blogbeitrag bekommt keine Theater-Klassen', 'nein', ( in_array( 'tha-page', $blog, true ) || in_array( 'tha-bericht', $blog, true ) ) ? 'ja' : 'nein' );

$GLOBALS['tha_preview_post_id'] = 2738;
$bericht = Tha_Pagechrome::body_class( array() );
check( 'Bericht bekommt tha-bericht', 'ja', in_array( 'tha-bericht', $bericht, true ) ? 'ja' : 'nein' );
check( 'Bericht bekommt tha-typo',    'ja', in_array( 'tha-typo', $bericht, true ) ? 'ja' : 'nein' );

echo "\nOhne Referenzen gilt der ganze Bestand als Theater:\n\n";

// Eine Website, auf der nur Theater steht, kennt keine anderen Beitraege —
// dort darf die Abgrenzung nichts kaputt machen.
$allein = Tha_Settings::defaults();
$allein['page_id'] = 0;
$allein['post_singles'] = true;
update_option( Tha_Settings::OPTION, $allein );
Tha_Settings::flush_cache();

check( 'Blogbeitrag zaehlt dann als Bericht', 'ja', Tha_Assets::is_theater_page( 4711 ) ? 'ja' : 'nein' );

echo "\nJede Theater-Referenz reicht fuer die Abgrenzung:\n\n";

$anmelden = Tha_Settings::defaults();
$anmelden['page_id'] = 0;
$anmelden['anmeldung_page_id'] = 81;
$GLOBALS['tha_preview_permalinks'][81] = 'https://stvauw.ch/theater/anmeldung-sponsoring/';
update_option( Tha_Settings::OPTION, $anmelden );
Tha_Settings::flush_cache();

check( 'Anmeldeseite: Bericht bleibt erkannt',  'ja',   Tha_Assets::is_theater_page( 2738 ) ? 'ja' : 'nein' );
check( 'Anmeldeseite: Blogbeitrag bleibt draussen', 'nein', Tha_Assets::is_theater_page( 4711 ) ? 'ja' : 'nein' );

$archiv = Tha_Settings::defaults();
$archiv['page_id']              = 0;
$archiv['anmeldung_page_id']    = 0;
$archiv['anmeldung_ok_page_id'] = 0;
$archiv['rueckblick_archive_url'] = 'https://stvauw.ch/theater/2025-2008/';
update_option( Tha_Settings::OPTION, $archiv );
Tha_Settings::flush_cache();

check( 'Archivseite: Bericht bleibt erkannt',   'ja',   Tha_Assets::is_theater_page( 2738 ) ? 'ja' : 'nein' );
check( 'Archivseite: Blogbeitrag bleibt draussen', 'nein', Tha_Assets::is_theater_page( 4711 ) ? 'ja' : 'nein' );

echo "\nAusgeschaltet bleibt alles draussen:\n\n";

$aus = Tha_Settings::defaults();
$aus['page_id']      = 42;
$aus['post_singles'] = false;
update_option( Tha_Settings::OPTION, $aus );
Tha_Settings::flush_cache();

check( 'Bericht ohne Schalter ist keine Theater-Seite', 'nein', Tha_Assets::is_theater_page( 2738 ) ? 'ja' : 'nein' );
check( 'Blogbeitrag weiterhin nicht',                    'nein', Tha_Assets::is_theater_page( 4711 ) ? 'ja' : 'nein' );

echo "\nEin ähnlich klingendes Verzeichnis bleibt aussen:\n\n";

update_option( Tha_Settings::OPTION, array_merge( Tha_Settings::defaults(), array( 'page_id' => 42 ) ) );
Tha_Settings::flush_cache();
$GLOBALS['tha_preview_permalinks'][4711] = 'https://stvauw.ch/theaterfest/2026/09/06/gruempi-merenschwand/';

check( 'Präfix endet auf Schrägstrich', '/theater/', Tha_Assets::theater_prefix() );
check( '/theaterfest/ wird nicht erwischt', 'nein', Tha_Assets::is_theater_page( 4711 ) ? 'ja' : 'nein' );

echo "\nPfad-Helfer:\n\n";

check( 'absolute Adresse', '/theater/2025-2008/', tha_url_path( 'https://stvauw.ch/theater/2025-2008/' ) );
check( 'relative Adresse', '/theater/2026/06/2738/', tha_url_path( '/theater/2026/06/2738/' ) );
check( 'protocol-relative Adresse', '/theater/', tha_url_path( '//stvauw.ch/theater/' ) );
check( 'Adresse ohne Pfad', '/', tha_url_path( 'https://stvauw.ch' ) );
check( 'Verzeichnis der Archivseite', '/theater/', tha_dir_prefix( '/theater/2025-2008/' ) );
check( 'Verzeichnis der Theater-Seite', '/theater/', tha_dir_prefix( tha_url_path( 'https://stvauw.ch/theater/' ) ) );
check( 'Startseite bleibt Wurzel', '/', tha_dir_prefix( '/' ) );
check( 'Pfad ohne Schrägstrich am Ende', '/theater/', tha_dir_prefix( '/theater' ) );

printf( "\n%d Prüfungen, %d Fehler\n", $runs, $fails );
exit( $fails ? 1 : 0 );