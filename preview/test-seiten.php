<?php
/**
 * Test der Shortcode-Vorschläge im Tab «Seiten».
 *
 * Anlass: für «Anmeldung Sponsoring» wurde die Sponsorengalerie vorgeschlagen,
 * weil im Titel «Sponsor» steht. Auf der Seite läuft aber das Buchungsformular
 * für die Premieren- und Spenderpakete. Ein Vorschlag, der eine Anmeldung
 * zuschüttet, ist schlimmer als gar keiner — deshalb prüft dieser Test die
 * Vorschläge an den ECHTEN Seitentiteln von stvauw.ch/theater.
 *
 * Aufruf:  php preview/test-seiten.php
 */

require __DIR__ . '/wp-stubs.php';

$plugin = dirname( __DIR__ ) . '/plugin/theater-auw';
define( 'THA_VERSION', '1.6.1' );
define( 'THA_FILE', $plugin . '/theater-auw.php' );
define( 'THA_DIR', $plugin . '/' );
define( 'THA_URL', '' );

require THA_DIR . 'includes/helpers.php';
require THA_DIR . 'includes/class-tha-settings.php';
require THA_DIR . 'includes/class-tha-groups.php';
require THA_DIR . 'includes/class-tha-chronik.php';
require THA_DIR . 'includes/class-tha-renderer.php';
require THA_DIR . 'includes/class-tha-shortcode.php';
// Die Uebersicht rendert inzwischen auch Formularfelder — dafuer braucht sie
// die Feld-Helfer aus Tha_Admin. Nur die Klasse laden, nichts registrieren.
require THA_DIR . 'admin/class-tha-admin.php';

$fails = 0;
$runs  = 0;

function check( $label, $expected, $actual ) {
	global $fails, $runs;
	$runs++;
	$ok = ( (string) $expected === (string) $actual );
	if ( ! $ok ) {
		$fails++;
	}
	printf( "%s  %-44s erwartet=%-22s erhalten=%s\n", $ok ? 'ok  ' : 'FAIL', $label, $expected, $actual );
}

// Der Bestand, wie ihn die REST-Schnittstelle von stvauw.ch am 25.07.2026
// ausgegeben hat.
$GLOBALS['tha_test_pages'] = array(
	'Home',
	'Die Schauspieler',
	'Hinter den Kulissen',
	'OK',
	'Spieldaten',
	'Sponsoren 2025',
	'Sponsoren 2018',
	'Sponsoren Archiv',
	'Anmeldung Sponsoring',
	'Anmeldung Premiere',
	'Anmeldung Erfolgreich',
	'Theater 2025',
	'Speisekarte',
	'Updates',
	'Impressum',
	'Datenschutz',
	'2025 – 2008',
	'2007 – 1987',
	'1975 – 1937',
);

$settings = Tha_Settings::defaults();
$settings['season_year'] = 2026;

ob_start();
include THA_DIR . 'admin/views/tab-seiten.php';
$html = ob_get_clean();

/** Welcher Vorschlag steht in der Zeile dieser Seite? */
function zeile( $html, $titel ) {
	$teile = explode( '<tr>', $html );
	foreach ( $teile as $teil ) {
		if ( false !== strpos( $teil, '<strong>' . htmlspecialchars( $titel, ENT_QUOTES, 'UTF-8' ) . '</strong>' ) ) {
			return $teil;
		}
	}
	return null;
}

function vorschlag( $html, $titel ) {
	$teil = zeile( $html, $titel );
	if ( null === $teil ) {
		return '(nicht gelistet)';
	}
	if ( false !== strpos( $teil, 'Hier gehört kein Shortcode hin' ) ) {
		return '(Formular)';
	}
	if ( preg_match( '/<textarea[^>]*>(.*?)<\/textarea>/s', $teil, $m ) ) {
		return html_entity_decode( $m[1], ENT_QUOTES, 'UTF-8' );
	}
	return '(kein Vorschlag)';
}

echo "Der Fall, der es ausgelöst hat:\n\n";

check(
	'Anmeldung Sponsoring bekommt keine Galerie',
	'(Formular)',
	vorschlag( $html, 'Anmeldung Sponsoring' )
);
check(
	'Anmeldung Premiere ebenso',
	'(Formular)',
	vorschlag( $html, 'Anmeldung Premiere' )
);
check(
	'Anmeldung Erfolgreich taucht gar nicht auf',
	'(nicht gelistet)',
	vorschlag( $html, 'Anmeldung Erfolgreich' )
);

echo "\nDie Seiten, die weiterhin einen Vorschlag brauchen:\n\n";

check(
	'Die Schauspieler',
	'[theater_auw_seite titel="Die Schauspieler" gruppe="schauspieler"][/theater_auw_seite]',
	vorschlag( $html, 'Die Schauspieler' )
);
check(
	'Hinter den Kulissen',
	'[theater_auw_seite titel="Hinter den Kulissen" gruppe="kulissen"][/theater_auw_seite]',
	vorschlag( $html, 'Hinter den Kulissen' )
);
check(
	'OK',
	'[theater_auw_seite titel="Organisation Komitee" gruppe="ok"][/theater_auw_seite]',
	vorschlag( $html, 'OK' )
);
check(
	'Spieldaten',
	'[theater_auw sections="hero,spieldaten"]',
	vorschlag( $html, 'Spieldaten' )
);
check(
	'Sponsoren 2025 ist die laufende Saison',
	'[theater_auw_seite titel="Sponsoren" gruppe="sponsoren"][/theater_auw_seite]',
	vorschlag( $html, 'Sponsoren 2025' )
);

echo "\nAlte Jahrgänge bleiben unangetastet:\n\n";

check( 'Sponsoren 2018 ist ein Archiv', '(nicht gelistet)', vorschlag( $html, 'Sponsoren 2018' ) );
check( 'Sponsoren Archiv ebenso',       '(nicht gelistet)', vorschlag( $html, 'Sponsoren Archiv' ) );
check( 'Theater 2025 ebenso',           '[theater_auw_seite titel="Sponsoren" gruppe="sponsoren"][/theater_auw_seite]' === vorschlag( $html, 'Theater 2025' ) ? 'GALERIE' : 'keine Galerie', 'keine Galerie' );

echo "\nJahresbereiche bekommen den Rückblick:\n\n";

check(
	'2025 – 2008 nimmt das Portfolio',
	'[theater_auw_rueckblick von="2008" bis="2025" titel="2025 – 2008"]',
	vorschlag( $html, '2025 – 2008' )
);
// Genau der Fall, der leer blieb: diese Jahre stehen nicht im Portfolio.
check(
	'2007 – 1987 nimmt die Chronik',
	'[theater_auw_chronik von="1987" bis="2007" titel="2007 – 1987"]',
	vorschlag( $html, '2007 – 1987' )
);
check(
	'1975 – 1937 ebenso',
	'[theater_auw_chronik von="1937" bis="1975" titel="1975 – 1937"]',
	vorschlag( $html, '1975 – 1937' )
);

echo "\nDie eingebaute Chronik:\n\n";

$span = Tha_Chronik::span();
check( 'reicht bis 1937',            '1937', (string) $span['min'] );
check( 'und bis 2007',               '2007', (string) $span['max'] );
check( 'enthaelt 60 Jahrgaenge',     '60',   (string) count( Tha_Chronik::all() ) );
check( 'neuester Eintrag zuerst',    '2007', (string) Tha_Chronik::all()[0]['year'] );

$ch1987 = Tha_Chronik::entries( array( 'von' => 1987, 'bis' => 2007 ) );
check( '1987–2007 sind 21 Stuecke',  '21',   (string) count( $ch1987 ) );
check( 'davon 2001 der Titel',       'Wer esch de Vatter?', $ch1987[6]['title'] );
check( 'mit Regie',                  'Hanna Gehrig',        $ch1987[6]['director'] );
check(
	'Nebenangaben als Kette',
	"von Anita Hart und Maurice Braddel \u{00B7} Regie Hanna Gehrig \u{00B7} 7 D / 7 M",
	Tha_Chronik::meta( $ch1987[6] )
);

$ch1937 = Tha_Chronik::entries( array( 'von' => 1937, 'bis' => 1975 ) );
check( '1937–1975 sind 39 Zeilen',   '39',   (string) count( $ch1937 ) );
check( 'aeltester Eintrag 1937',     'Der Sohn des Wilddiebs', $ch1937[38]['title'] );

// Fuer diese Jahre ist nur Titel und Gattung ueberliefert — nichts erfinden.
check( 'ohne Autor bleibt leer',     '',     $ch1937[0]['author'] );
check( 'ohne Regie bleibt leer',     '',     Tha_Chronik::meta( $ch1937[0] ) );

// 1939 und 1942 stehen im Original als Gedankenstrich.
$luecken = array();
foreach ( $ch1937 as $row ) {
	if ( '' === $row['title'] ) {
		$luecken[] = $row['year'];
	}
}
check( 'die zwei Luecken bleiben',   '1942,1939', implode( ',', $luecken ) );

// Verdrehte Reihenfolge darf nicht ins Leere laufen.
check( 'von/bis vertauscht',         '21', (string) count( Tha_Chronik::entries( array( 'von' => 2007, 'bis' => 1987 ) ) ) );
check( 'ohne Zeitraum alles',        '60', (string) count( Tha_Chronik::entries() ) );

echo "\nSeiten ohne Theater-Bezug:\n\n";

check( 'Impressum bleibt draussen',   '(nicht gelistet)', vorschlag( $html, 'Impressum' ) );
check( 'Datenschutz bleibt draussen', '(nicht gelistet)', vorschlag( $html, 'Datenschutz' ) );

echo "\nSpeisekarte und Updates — die letzten zwei im alten Design:\n\n";

// Vorher standen beide gar nicht in der Liste: es gab keinen Shortcode, der
// zu ihnen gepasst haette. Seit es fuer beide einen eigenen Seitentyp gibt,
// gehoeren sie dazu.
check( 'Speisekarte bekommt ihren Seitentyp', '[theater_auw_karte]',    vorschlag( $html, 'Speisekarte' ) );
check( 'Updates ebenso',                      '[theater_auw_updates]',  vorschlag( $html, 'Updates' ) );

echo "\nUmgestellte Seiten zeigen ihren echten Shortcode:\n\n";

// Genau die drei Seiten, die vorher alle dasselbe nichtssagende
// «[theater_auw_seite]» anzeigten.
$GLOBALS['tha_test_pages'] = array(
	'Die Schauspieler'    => '[theater_auw_seite titel="Die Schauspieler" gruppe="schauspieler"][/theater_auw_seite]',
	'Hinter den Kulissen' => '[theater_auw_seite titel="Hinter den Kulissen" gruppe="kulissen"][/theater_auw_seite]',
	'OK'                  => '[theater_auw_seite titel="Organisation Komitee" gruppe="ok"][/theater_auw_seite]',
	'Home'                => 'Text davor [theater_auw] Text danach',
	'Spieldaten'          => '[theater_auw sections="hero,spieldaten"]',
	'2025 – 2008'         => '[theater_auw_rueckblick von="2008" bis="2025"]',
);

ob_start();
include THA_DIR . 'admin/views/tab-seiten.php';
$html2 = ob_get_clean();

/** Was steht in der Shortcode-Spalte einer umgestellten Seite? */
function gezeigt( $html, $titel ) {
	$teil = zeile( $html, $titel );
	if ( null === $teil ) {
		return '(nicht gelistet)';
	}
	preg_match_all( '/<code[^>]*>(.*?)<\/code>/s', $teil, $m );
	return $m[1] ? html_entity_decode( implode( ' + ', $m[1] ), ENT_QUOTES, 'UTF-8' ) : '(kein Code)';
}

check(
	'Schauspieler zeigt seine Gruppe',
	'[theater_auw_seite titel="Die Schauspieler" gruppe="schauspieler"]',
	gezeigt( $html2, 'Die Schauspieler' )
);
check(
	'Kulissen zeigt seine Gruppe',
	'[theater_auw_seite titel="Hinter den Kulissen" gruppe="kulissen"]',
	gezeigt( $html2, 'Hinter den Kulissen' )
);
check(
	'OK zeigt seine Gruppe',
	'[theater_auw_seite titel="Organisation Komitee" gruppe="ok"]',
	gezeigt( $html2, 'OK' )
);
check( 'die drei sind unterscheidbar', 'ja', gezeigt( $html2, 'OK' ) !== gezeigt( $html2, 'Hinter den Kulissen' ) ? 'ja' : 'nein' );

// Der Fallstrick: «theater_auw» ist ein Präfix von «theater_auw_seite».
check( 'kurzer Shortcode wird nicht verwechselt', '[theater_auw]', gezeigt( $html2, 'Home' ) );
check(
	'Parameter bleiben stehen',
	'[theater_auw sections="hero,spieldaten"]',
	gezeigt( $html2, 'Spieldaten' )
);
check(
	'Rückblick mit Jahresbereich',
	'[theater_auw_rueckblick von="2008" bis="2025"]',
	gezeigt( $html2, '2025 – 2008' )
);

echo "\nBildformat des Rückblicks:\n\n";

check( 'Standard ist Querformat', 'quer', Tha_Settings::defaults()['rueckblick_ratio'] );
check( 'Hochformat wird übernommen', 'hoch', Tha_Settings::sanitize( array( 'rueckblick_ratio' => 'hoch' ) )['rueckblick_ratio'] );
check( 'unbekanntes Format fällt zurück', 'quer', Tha_Settings::sanitize( array( 'rueckblick_ratio' => 'schraeg' ) )['rueckblick_ratio'] );

echo "\nFotos der Chronik — Zuordnung über den Namen:\n\n";

// Genau die Mediathek-Eintraege von stvauw.ch.
$GLOBALS['tha_test_attachments'] = array(
	array( 'id' => 379, 'title' => '1937',      'file' => 'sites/4/2016/03/1937.jpg' ),
	array( 'id' => 383, 'title' => '1958',      'file' => 'sites/4/2016/03/1958.jpg' ),
	array( 'id' => 384, 'title' => '1958 2',    'file' => 'sites/4/2016/03/1958_2.jpg' ),
	array( 'id' => 388, 'title' => '1963',      'file' => 'sites/4/2016/03/1963.jpg' ),
	array( 'id' => 389, 'title' => '1963 2',    'file' => 'sites/4/2016/03/1963_2.jpg' ),
	array( 'id' => 390, 'title' => '1963 3',    'file' => 'sites/4/2016/03/1963_3.jpg' ),
	array( 'id' => 391, 'title' => '1963 4',    'file' => 'sites/4/2016/03/1963_4.jpg' ),
	// Spaetere Jahrgaenge: Titelbild plus durchnummerierte Aufnahmen.
	array( 'id' => 900, 'title' => '', 'file' => 'sites/4/2017/01/2007-verwandtschaftlich-verwandt.jpg' ),
	array( 'id' => 901, 'title' => '', 'file' => 'sites/4/2017/01/2007_01.jpg' ),
	array( 'id' => 902, 'title' => '', 'file' => 'sites/4/2017/01/2007_02.jpg' ),
	array( 'id' => 910, 'title' => '', 'file' => 'sites/4/2017/01/2007_10.jpg' ),
	// Stolperfallen:
	array( 'id' => 700, 'title' => 'Hirschen Soufleuse', 'file' => 'sites/4/2016/03/hirschen.jpg' ),
	array( 'id' => 701, 'title' => '', 'file' => 'sites/4/2019/06/Logo_theaterauw_transparent.png' ),
	array( 'id' => 2632, 'title' => '', 'file' => 'sites/4/2025/12/2025_Theater_Auw_Programm_web-6.png' ),
);
Tha_Chronik::forget_images();

check( '1937 findet sein Foto',        '379',         implode( ',', Tha_Chronik::images( 1937 ) ) );
check( '1958 findet beide',            '383,384',     implode( ',', Tha_Chronik::images( 1958 ) ) );
check( '1963 findet alle vier',        '388,389,390,391', implode( ',', Tha_Chronik::images( 1963 ) ) );

// Der Unterstrich liegt nach dem Bindestrich — das Titelbild steht vorn,
// und 2007_10 gehoert hinter 2007_02, nicht dazwischen.
check( '2007: Titelbild zuerst, dann der Reihe nach', '900,901,902,910', implode( ',', Tha_Chronik::images( 2007 ) ) );

check( 'Jahr ohne Foto bleibt leer',   '',   implode( ',', Tha_Chronik::images( 1940 ) ) );
check( 'Bild ohne Jahr wird ignoriert', 'nein', in_array( 700, Tha_Chronik::images( 1937 ), true ) ? 'ja' : 'nein' );

// Der Upload-Ordner heisst 2019 — das darf kein Jahrgang werden.
$alle_bilder = Tha_Chronik::images_by_year();
check( 'Upload-Ordner 2019 wird kein Jahrgang', 'nein', isset( $alle_bilder[2019] ) ? 'ja' : 'nein' );
// 2025 liegt gar nicht in der Chronik — der Saalplan darf nicht auftauchen.
check( 'Saalplan 2025 landet nicht in der Chronik', 'nein', isset( $alle_bilder[2025] ) ? 'ja' : 'nein' );

// Zweiter Aufruf kommt aus dem Zwischenspeicher.
$GLOBALS['tha_test_attachments'] = array();
check( 'zweiter Aufruf nutzt den Speicher', '383,384', implode( ',', Tha_Chronik::images( 1958 ) ) );
Tha_Chronik::forget_images();
check( 'nach dem Vergessen ist er leer',    '',        implode( ',', Tha_Chronik::images( 1958 ) ) );

echo "\nTheme-Navigation ausblenden:\n\n";

check( 'ist standardmaessig aus', '', Tha_Settings::defaults()['nav_replace'] ? '1' : '' );

// Mit gewaehltem Menue laesst sie sich einschalten.
update_option( Tha_Settings::OPTION, array_merge( Tha_Settings::defaults(), array( 'nav_menu_id' => 7 ) ) );
$an = Tha_Settings::sanitize( array( 'nav_replace_present' => '1', 'nav_replace' => '1' ) );
check( 'mit Menue laesst sie sich einschalten', '1', $an['nav_replace'] ? '1' : '' );

// Ohne Menue nicht — sonst stuende die Seite ohne jede Navigation da.
update_option( Tha_Settings::OPTION, array_merge( Tha_Settings::defaults(), array( 'nav_menu_id' => 0 ) ) );
$aus = Tha_Settings::sanitize( array( 'nav_replace_present' => '1', 'nav_replace' => '1' ) );
check( 'ohne Menue bleibt sie aus', '', $aus['nav_replace'] ? '1' : '' );

// Und wer das Menue nachtraeglich wieder abwaehlt, schaltet sie mit ab.
update_option( Tha_Settings::OPTION, array_merge( Tha_Settings::defaults(), array( 'nav_menu_id' => 7, 'nav_replace' => true ) ) );
$weg = Tha_Settings::sanitize( array( 'nav_menu_id' => 0 ) );
check( 'Menue abwaehlen schaltet sie ab', '', $weg['nav_replace'] ? '1' : '' );

echo "\nShortcodes an EINER Stelle:\n\n";

$erwartet = array(
	'theater_auw',
	'theater_auw_seite',
	'theater_auw_gruppe',
	'theater_auw_rueckblick',
	'theater_auw_chronik',
	'theater_auw_nav',
	'theater_auw_kontakt',
	'theater_auw_karte',
	'theater_auw_updates',
);
check( 'alle sind gelistet', (string) count( $erwartet ), (string) count( Tha_Shortcode::TAGS ) );
foreach ( $erwartet as $tag ) {
	check( $tag . ' ist dabei', 'ja', in_array( $tag, Tha_Shortcode::TAGS, true ) ? 'ja' : 'nein' );
}

// Genau die Luecke, die «2025 – 2008» das alte Menue beschert hat: die
// Erkennung kannte Rueckblick und Chronik nicht und setzte keine Klassen.
$erkennung = file_get_contents( THA_DIR . 'includes/class-tha-assets.php' );
check( 'die Erkennung nutzt dieselbe Liste', 'ja', false !== strpos( $erkennung, 'Tha_Shortcode::TAGS' ) ? 'ja' : 'nein' );
check( 'und hat keine eigene mehr',          'ja', false === strpos( $erkennung, "'theater_auw_seite', 'theater_auw_gruppe' )" ) ? 'ja' : 'nein' );

// Jeder Shortcode, der eine eigene Wurzel oeffnet, muss die Navigation
// anbieten — sonst steht die Seite ohne da, sobald das Theme-Menue weg ist.
$shortcodes = file_get_contents( THA_DIR . 'includes/class-tha-shortcode.php' );
check( 'jede Wurzel bekommt die Navigation', (string) substr_count( $shortcodes, 'font_link_fallback' ), (string) substr_count( $shortcodes, 'Tha_Renderer::nav_once' ) );

echo "\nTheater-Typografie fuer den uebrigen Inhalt:\n\n";

check( 'ist standardmaessig an', '1', Tha_Settings::defaults()['content_typo'] ? '1' : '' );
check( 'laesst sich abschalten',  '',  Tha_Settings::sanitize( array( 'content_typo_present' => '1' ) )['content_typo'] ? '1' : '' );
check( 'und wieder einschalten',  '1', Tha_Settings::sanitize( array( 'content_typo_present' => '1', 'content_typo' => '1' ) )['content_typo'] ? '1' : '' );

// Bestehende Installationen bekommen sie per Migration.
$vor5 = Tha_Settings::defaults();
$vor5['schema_version'] = 4;
$vor5['content_typo']   = false;
update_option( Tha_Settings::OPTION, $vor5 );
Tha_Settings::upgrade();
check( 'Migration schaltet sie ein', '1', get_option( Tha_Settings::OPTION )['content_typo'] ? '1' : '' );

// Die Regeln duerfen den eigenen Baum nicht anfassen — sonst verliert die
// Navigation ihre Schriftgroesse.
$css = file_get_contents( THA_DIR . 'assets/css/theater-auw.css' );
preg_match_all( '/^:is\(#left-area, body\)\.tha-typo[^{]*\{/m', $css, $typo );
$ohne_schutz = 0;
foreach ( $typo[0] as $regel ) {
	// Regeln auf den Container selbst brauchen keinen Schutz.
	if ( false !== strpos( $regel, '#main-content' ) || false !== strpos( $regel, '.site-main' ) ) {
		continue;
	}
	if ( false === strpos( $regel, ':not(.tha *)' ) ) {
		$ohne_schutz++;
	}
}
check( 'jede Inhaltsregel schuetzt den eigenen Baum', '0', (string) $ohne_schutz );
check( 'und es gibt ueberhaupt welche', 'ja', count( $typo[0] ) > 3 ? 'ja' : 'nein' );

echo "\nJahrgangsseiten des Rueckblicks:\n\n";

check( 'werden standardmaessig erkannt', '1', Tha_Settings::defaults()['rueckblick_singles'] ? '1' : '' );
check( 'lassen sich abschalten',          '',  Tha_Settings::sanitize( array( 'rueckblick_singles_present' => '1' ) )['rueckblick_singles'] ? '1' : '' );
check( 'und wieder einschalten',          '1', Tha_Settings::sanitize( array( 'rueckblick_singles_present' => '1', 'rueckblick_singles' => '1' ) )['rueckblick_singles'] ? '1' : '' );

// Die Erkennung muss den Beitragstyp aus den Einstellungen nehmen, nicht
// «project» fest verdrahten — sonst bricht es bei jedem anderen Theme.
$erkennung = file_get_contents( THA_DIR . 'includes/class-tha-assets.php' );
check( 'Erkennung liest den Beitragstyp aus den Einstellungen', 'ja', false !== strpos( $erkennung, "is_singular( \$settings['rueckblick_post_type'] )" ) ? 'ja' : 'nein' );
check( 'und respektiert den Schalter',                          'ja', false !== strpos( $erkennung, "\$settings['rueckblick_singles']" ) ? 'ja' : 'nein' );

echo "\nDie Leiste im Page-Builder-Modul:\n\n";

$sc = file_get_contents( THA_DIR . 'includes/class-tha-shortcode.php' );
check( 'die Leiste traegt einen eigenen Marker', 'ja', false !== strpos( $sc, "'tha--bar'" ) ? 'ja' : 'nein' );

$css = file_get_contents( THA_DIR . 'assets/css/theater-auw.css' );
check( 'und das CSS greift ihn auf',             'ja', false !== strpos( $css, ':has(> .tha--bar)' ) ? 'ja' : 'nein' );
// Der Marker darf NUR die alleinstehende Leiste treffen. Wuerde die Regel
// auf `.tha` zielen, verloere jedes Modul mit echtem Inhalt seine Abstaende.
check( 'aber nicht jede Wurzel',                  '0',  (string) preg_match_all( '/:has\(> \.tha\)/', $css ) );

printf( "\n%d Prüfungen, %d Fehler\n", $runs, $fails );
exit( $fails ? 1 : 0 );
