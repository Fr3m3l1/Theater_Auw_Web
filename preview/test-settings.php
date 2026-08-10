<?php
/**
 * Test des Sanitizers.
 *
 * Der wichtigste Fall: die Einstellungsseite hat Tabs, die alle in DIESELBE
 * Option schreiben. Wer «Stück & Texte» speichert, überträgt die Felder von
 * «Phasen & Tickets» gar nicht mit — die dürfen dabei nicht verloren gehen.
 *
 * Aufruf:  php preview/test-settings.php
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
require THA_DIR . 'includes/class-tha-rueckblick.php';

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

// Ausgangslage: eine vollständig eingerichtete Saison.
$saved = Tha_Settings::defaults();
$saved['ticket_url']  = 'https://reservation.ticketleo.com/event/33310/karriere';
$saved['phase_mode']  = 'manual';
$saved['phase_manual'] = 'spielzeit';
$saved['phase_schedule']['vorverkauf_start'] = '2026-09-01 10:00';
$saved['isolation']   = 'stark';
$saved['sticky_cta']  = false;
$saved['font_source'] = 'theme';
update_option( Tha_Settings::OPTION, $saved );

echo "Szenario: der Tab «Stück & Texte» wird gespeichert.\n";
echo "Er überträgt NUR seine eigenen Felder.\n\n";

$submitted = array(
	'play_title'  => 'Karriere',
	'play_author' => 'Hans Muster',
);

$result = Tha_Settings::sanitize( $submitted );

check( 'neuer Stücktitel wird übernommen',      'Karriere',   $result['play_title'] );
check( 'Ticketleo-URL bleibt erhalten',         $saved['ticket_url'], $result['ticket_url'] );
check( 'manueller Phasen-Modus bleibt',         'manual',     $result['phase_mode'] );
check( 'gewählte Phase bleibt',                 'spielzeit',  $result['phase_manual'] );
check( 'Vorverkaufsdatum bleibt',               '2026-09-01 10:00', $result['phase_schedule']['vorverkauf_start'] );
check( 'Abschottung bleibt auf stark',          'stark',      $result['isolation'] );
check( 'Schriftquelle bleibt auf theme',        'theme',      $result['font_source'] );
check( 'Ticket-Leiste bleibt ausgeschaltet',    '',           $result['sticky_cta'] ? '1' : '' );
check( 'Spieldaten bleiben vollzählig',         6,            count( $result['dates'] ) );

echo "\nSzenario: der Tab «Darstellung» schaltet die Ticket-Leiste aus.\n\n";

$saved['sticky_cta'] = true;
update_option( Tha_Settings::OPTION, $saved );

$result = Tha_Settings::sanitize( array( 'sticky_cta_present' => '1' ) );
check( 'Checkbox abgehakt schaltet wirklich aus', '',         $result['sticky_cta'] ? '1' : '' );

$result = Tha_Settings::sanitize( array( 'sticky_cta_present' => '1', 'sticky_cta' => '1' ) );
check( 'Checkbox angehakt schaltet ein',          '1',        $result['sticky_cta'] ? '1' : '' );

echo "\nSchweizer Rechtschreibung und Validierung:\n\n";

$result = Tha_Settings::sanitize( array( 'play_title' => "Gru\u{00DF} aus Gie\u{00DF}en" ) );
check( 'Eszett wird zu ss',                       'Gruss aus Giessen', $result['play_title'] );

$result = Tha_Settings::sanitize( array( 'ticket_url' => 'http://unsicher.example/event' ) );
check( 'http-Ticketlink wird verworfen',          '',         $result['ticket_url'] );

$result = Tha_Settings::sanitize( array( 'doors_time' => '18:30' ) );
check( 'Uhrzeit wird auf Punktnotation gebracht', '18.30',    $result['doors_time'] );

$result = Tha_Settings::sanitize( array( 'phase_manual' => 'quatsch' ) );
check( 'unbekannte Phase fällt zurück',           'spielzeit', $result['phase_manual'] );

echo "\nRückblick — Titel aus dem Portfolio zerlegen:\n\n";

// So heissen die Beiträge im Divi-Portfolio von stvauw.ch.
$cases = array(
	'2025 Karriere'                    => array( '2025', 'Karriere' ),
	'2023 D´ Putzfrau als Detektivin'  => array( '2023', 'D´ Putzfrau als Detektivin' ),
	'1937 Der erste Auftritt'          => array( '1937', 'Der erste Auftritt' ),
	'2019 – Architekte und anderi Vögel' => array( '2019', 'Architekte und anderi Vögel' ),
	'Ohne Jahreszahl'                  => array( '', 'Ohne Jahreszahl' ),
);
foreach ( $cases as $raw => $want ) {
	$got = Tha_Rueckblick::split_title( $raw );
	check( 'Jahr aus «' . substr( $raw, 0, 26 ) . '»',  $want[0], $got['year'] );
	check( 'Titel aus «' . substr( $raw, 0, 26 ) . '»', $want[1], $got['title'] );
}

echo "\nBildergruppen — Beschriftung aus dem Bild:\n\n";

$cases = array(
	'Hanna Gehrig'                     => array( 'Hanna Gehrig', '' ),
	'Franz Troxler | Grilleur'         => array( 'Franz Troxler', 'Grilleur' ),
	'Christine Jauner — Regie'         => array( 'Christine Jauner', 'Regie' ),
	'Gabi + Gertrud Leu'               => array( 'Gabi + Gertrud Leu', '' ),
	'Aargauische Kantonalbank'         => array( 'Aargauische Kantonalbank', '' ),
	'DSC_0421'                         => array( '', '' ),
	// Die Schreibweise von stvauw.ch: Rolle vorn, Mensch hinten.
	'Sohlenberger alias Markus Ineichen' => array( 'Markus Ineichen', 'Sohlenberger' ),
	'Michèle Rippman | Souffleuse'     => array( 'Michèle Rippman', 'Souffleuse' ),
	'Roswita Beinfritz alias Melanie Zürcher' => array( 'Melanie Zürcher', 'Roswita Beinfritz' ),
	'Max Brügger alias Pascal von Flüe' => array( 'Pascal von Flüe', 'Max Brügger' ),
);
foreach ( $cases as $raw => $want ) {
	$got = Tha_Groups::split_label( $raw );
	check( 'Name aus «' . substr( $raw, 0, 26 ) . '»', $want[0], $got['name'] );
	if ( '' !== $want[1] ) {
		check( 'Rolle aus «' . substr( $raw, 0, 26 ) . '»', $want[1], $got['role'] );
	}
}

echo "\nSeitenrahmen — Standard und Migration:\n\n";

$d = Tha_Settings::defaults();
check( 'Seitentitel ausblenden ist Standard',   'ja', $d['hide_page_title'] ? 'ja' : 'nein' );
check( 'Seitenleiste ausblenden ist Standard',  'ja', $d['hide_sidebar'] ? 'ja' : 'nein' );
check( 'Volle Breite ist Standard',             'ja', $d['content_full_width'] ? 'ja' : 'nein' );

// Eine Installation aus 1.0.4: Schema 1, alle drei Schalter aus.
$alt = Tha_Settings::defaults();
$alt['schema_version']     = 1;
$alt['hide_page_title']    = false;
$alt['hide_sidebar']       = false;
$alt['content_full_width'] = false;
$alt['play_title']         = 'Nicht anfassen';
update_option( Tha_Settings::OPTION, $alt );

Tha_Settings::upgrade();
$after = get_option( Tha_Settings::OPTION );

check( 'Migration schaltet Seitentitel ein',    'ja', $after['hide_page_title'] ? 'ja' : 'nein' );
check( 'Migration schaltet Seitenleiste ein',   'ja', $after['hide_sidebar'] ? 'ja' : 'nein' );
check( 'Migration schaltet volle Breite ein',   'ja', $after['content_full_width'] ? 'ja' : 'nein' );
check( 'Migration hebt die Schema-Version',     (string) Tha_Settings::SCHEMA_VERSION, (string) $after['schema_version'] );
check( 'Migration lässt Inhalte in Ruhe',       'Nicht anfassen', $after['play_title'] );

// Zweiter Lauf darf nichts mehr tun.
$after['hide_sidebar'] = false;
update_option( Tha_Settings::OPTION, $after );
Tha_Settings::upgrade();
$twice = get_option( Tha_Settings::OPTION );
check( 'Migration läuft nicht zweimal',         'nein', $twice['hide_sidebar'] ? 'ja' : 'nein' );

echo "\nRückblick — Jahrgänge eingrenzen:\n\n";

// Der Bestand, wie ihn wp-stubs nachbildet: 2025 … 2016.
$fix = json_decode( file_get_contents( __DIR__ . '/fixtures.json' ), true );

$alle = Tha_Rueckblick::entries( $fix );
check( 'ohne Eingrenzung kommen alle',        '8', (string) count( $alle ) );

$neu = Tha_Rueckblick::entries( $fix, array( 'von' => 2020, 'bis' => 2026 ) );
check( '2020–2026 liefert vier Jahrgänge',    '4', (string) count( $neu ) );
check( 'davon ist der neueste 2025',          '2025', $neu ? $neu[0]['year'] : '-' );

$alt = Tha_Rueckblick::entries( $fix, array( 'von' => 1937, 'bis' => 2017 ) );
$jahre = array();
foreach ( $alt as $r ) { $jahre[] = $r['year']; }
check( '1937–2017 lässt die neuen Jahre weg', '2017,2016', implode( ',', $jahre ) );

$leer = Tha_Rueckblick::entries( $fix, array( 'von' => 1900, 'bis' => 1910 ) );
check( 'leerer Zeitraum liefert nichts',      '0', (string) count( $leer ) );

echo "\nGruppenbild:\n\n";

$in = array( 'groups' => array( array(
	'key' => 'ensemble', 'title' => 'Ensemble', 'type' => 'bilder', 'columns' => 2,
	'lead_image_id' => '301', 'lead_caption' => 'Das Ensemble 2026', 'ids' => array(),
) ) );
$res = Tha_Settings::sanitize( $in );
$g   = $res['groups'][0];
check( 'Gruppenbild wird gespeichert',        '301', (string) $g['lead_image_id'] );
check( 'Bildunterschrift wird gespeichert',   'Das Ensemble 2026', $g['lead_caption'] );
check( 'Gruppe ohne Einzelbilder bleibt',     '0',   (string) count( $g['ids'] ) );
check( 'Standard-Anordnung ist «neben»',      'neben', $g['lead_layout'] );

$res2 = Tha_Settings::sanitize( array( 'groups' => array( array(
	'key' => 'x', 'lead_layout' => 'oben', 'ids' => array(),
) ) ) );
check( '«oben» wird übernommen',              'oben',  $res2['groups'][0]['lead_layout'] );

$res3 = Tha_Settings::sanitize( array( 'groups' => array( array(
	'key' => 'x', 'lead_layout' => 'quatsch', 'ids' => array(),
) ) ) );
check( 'unbekannte Anordnung fällt zurück',   'neben', $res3['groups'][0]['lead_layout'] );

// Eine alte Gruppe ohne die neuen Schlüssel darf nicht in eine Notice laufen.
update_option( Tha_Settings::OPTION, array( 'groups' => array(
	array( 'key' => 'alt', 'title' => 'Alt', 'type' => 'personen', 'columns' => 3, 'ids' => array( 1 ) ),
) ) );
$alt = Tha_Groups::get( 'alt' );
check( 'alte Gruppe bekommt Gruppenbild-Feld', '0', (string) $alt['lead_image_id'] );
check( 'alte Gruppe behält ihre Spalten',      '3', (string) $alt['columns'] );

echo "\nBeschriftungen und «gross»:\n\n";

// Name, Rolle und Text kommen aus getrennten Feldern — im Backend tippt
// niemand mehr einen senkrechten Strich.
$res = Tha_Settings::sanitize( array( 'groups' => array( array(
	'key'    => 'kulissen',
	'ids'    => array( 201, 202, 207 ),
	'labels' => array(
		201 => array( 'name' => 'Gabi und Gertrud Leu', 'role' => 'Kostüme', 'text' => '' ),
		202 => array( 'name' => '', 'role' => '', 'text' => '' ),
		207 => array( 'name' => 'Das Tombolateam', 'role' => '', 'text' => '' ),
	),
	'wide'   => array( 207 => '1' ),
) ) ) );
$g = $res['groups'][0];

check( 'eigener Name wird gespeichert',        'Gabi und Gertrud Leu', $g['labels'][201]['name'] );
check( 'eigene Rolle wird gespeichert',        'Kostüme',              $g['labels'][201]['role'] );
check( 'leere Karte wird verworfen',           'nein', isset( $g['labels'][202] ) ? 'ja' : 'nein' );
check( '«gross» merkt sich die Bild-ID',       '207',  implode( ',', $g['wide'] ) );

// Wirkung in der Ausgabe. Settings direkt übergeben — Tha_Settings::get()
// cached statisch und hätte hier noch den alten Stand.
$items = Tha_Groups::items( Tha_Groups::get( 'kulissen', array( 'groups' => array( $g ) ) ) );

check( 'Anzahl Einträge stimmt',               '3', (string) count( $items ) );
check( 'eigener Name schlägt die Mediathek',   'Gabi und Gertrud Leu', $items[0]['name'] );
check( 'Rolle aus eigener Beschriftung',       'Kostüme',              $items[0]['role'] );
check( 'ohne eigenen Text greift die Mediathek', 'Maya Baumann',       $items[1]['name'] );
check( 'Tombolateam steht gross',              'ja',   $items[2]['wide'] ? 'ja' : 'nein' );
check( 'die anderen nicht',                    'nein', $items[0]['wide'] ? 'ja' : 'nein' );

echo "\nBeschriftung Feld für Feld:\n\n";

// Der häufigste Handgriff: an einem Bild stimmt EINE Angabe nicht. Wer nur die
// Rolle richtigstellt, soll den Namen nicht abschreiben müssen — er kommt
// weiterhin aus der Mediathek. Bild 202 heisst dort
// «Maya Baumann | Souffleuse | «Als Souffleuse …»».
$teil = Tha_Settings::sanitize( array( 'groups' => array( array(
	'key'    => 'kulissen',
	'ids'    => array( 202 ),
	'labels' => array( 202 => array( 'name' => '', 'role' => 'Souffleuse und Requisite', 'text' => '' ) ),
) ) ) );
$teil_items = Tha_Groups::items( $teil['groups'][0] );

check( 'nur die Rolle ersetzt',        'Souffleuse und Requisite', $teil_items[0]['role'] );
check( 'Name bleibt aus der Mediathek', 'Maya Baumann',            $teil_items[0]['name'] );
check( 'Zitat bleibt ebenfalls stehen', 'ja', '' !== $teil_items[0]['text'] ? 'ja' : 'nein' );

// Bei den Sponsoren steht in derselben Zeile der Ort. Die Ausgabe zeigt ihn —
// also muss er sich auch erfassen lassen; im Backend heisst das Feld dort «Ort».
$ort = Tha_Settings::sanitize( array( 'groups' => array( array(
	'key'    => 'sponsoren',
	'type'   => 'logos',
	'ids'    => array( 402 ),
	'labels' => array( 402 => array( 'name' => 'Metzgerei Villiger', 'role' => 'Auw', 'text' => '' ) ),
) ) ) );
$ort_items = Tha_Groups::items( $ort['groups'][0] );
check( 'Sponsor behält seinen Namen',  'Metzgerei Villiger', $ort_items[0]['name'] );
check( 'und trägt den Ort daneben',    'Auw',                $ort_items[0]['role'] );

// Installationen vor 1.1 haben dort noch einen einzigen String stehen.
$alt_labels = Tha_Settings::sanitize( array( 'groups' => array( array(
	'key'    => 'kulissen',
	'ids'    => array( 201 ),
	'labels' => array( 201 => 'Gabi und Gertrud Leu | Kostüme' ),
) ) ) );
$alt_items = Tha_Groups::items( $alt_labels['groups'][0] );

check( 'alte Schreibweise wird aufgeteilt', 'Gabi und Gertrud Leu', $alt_items[0]['name'] );
check( 'und die Rolle daraus erkannt',      'Kostüme',              $alt_items[0]['role'] );
check( 'gespeichert wird sie getrennt',     'Kostüme',              $alt_labels['groups'][0]['labels'][201]['role'] );

// Auch ungespeichert, direkt aus der Option gelesen, muss der alte String noch
// funktionieren — nach dem Update ist noch niemand im Backend gewesen.
$ungespeichert = Tha_Groups::items( array(
	'ids'    => array( 201 ),
	'labels' => array( 201 => 'Gabi und Gertrud Leu | Kostüme' ),
	'wide'   => array(),
) );
check( 'alter Stand rendert ohne Speichern', 'Gabi und Gertrud Leu', $ungespeichert[0]['name'] );

echo "\nDreiteilige Bildunterschriften:\n\n";

// Genau so stehen sie in der Mediathek von stvauw.ch.
$drei = Tha_Groups::split_label( 'Michèle Rippman | Souffleuse | „Als Souffleuse kann ich immer unterstützen."' );
check( 'Name ist nur der Name',       'Michèle Rippman', $drei['name'] );
check( 'Funktion steht für sich',     'Souffleuse',      $drei['role'] );
check( 'Text wird eigenes Feld',      '„Als Souffleuse kann ich immer unterstützen."', $drei['text'] );

$vier = Tha_Groups::split_label( 'A | B | C | D' );
check( 'weitere Striche bleiben im Text', 'C | D', $vier['text'] );

$zwei = Tha_Groups::split_label( 'Franz Troxler | Grilleur' );
check( 'zweiteilig hat keinen Text',  '',  $zwei['text'] );

$eins = Tha_Groups::split_label( 'Tombolateam' );
check( 'einteilig bleibt Name',       'Tombolateam', $eins['name'] );
check( 'einteilig ohne Funktion',     '',  $eins['role'] );

echo "\nAlphabetische Reihenfolge:\n\n";

// Die Namen kommen über `labels`, damit der Test nicht von den Stubs abhängt.
$sponsoren = static function ( $sort ) {
	$res = Tha_Settings::sanitize( array( 'groups' => array( array(
		'key'    => 'sponsoren',
		'type'   => 'logos',
		'sort'   => $sort,
		'ids'    => array( 301, 302, 303, 304, 305 ),
		'labels' => array(
			301 => 'Zurkirchen Bau',
			302 => "\u{00C4}gerter Garage",
			303 => 'Amrein Metzgerei',
			304 => "\u{00AB}R\u{00F6}ssli\u{00BB} Auw",
			305 => 'Odermatt AG',
		),
	) ) ) );
	$namen = array();
	foreach ( Tha_Groups::items( $res['groups'][0] ) as $item ) {
		$namen[] = $item['name'];
	}
	return $namen;
};

check(
	'alphabetisch sortiert die Sponsoren',
	"\u{00C4}gerter Garage,Amrein Metzgerei,Odermatt AG,\u{00AB}R\u{00F6}ssli\u{00BB} Auw,Zurkirchen Bau",
	implode( ',', $sponsoren( 'alpha' ) )
);
check(
	'«Wie ausgewählt» rührt nichts an',
	"Zurkirchen Bau,\u{00C4}gerter Garage,Amrein Metzgerei,\u{00AB}R\u{00F6}ssli\u{00BB} Auw,Odermatt AG",
	implode( ',', $sponsoren( 'manuell' ) )
);
check( 'unbekannte Reihenfolge fällt zurück', 'manuell', Tha_Settings::sanitize( array( 'groups' => array( array( 'key' => 'x', 'sort' => 'quatsch', 'ids' => array() ) ) ) )['groups'][0]['sort'] );

// Ein Bild ohne jede Beschriftung darf nicht nach vorn rutschen.
$ohne = Tha_Settings::sanitize( array( 'groups' => array( array(
	'key'    => 'sponsoren',
	'type'   => 'logos',
	'sort'   => 'alpha',
	'ids'    => array( 9901, 402 ),
	'labels' => array( 9901 => '', 402 => 'Amrein Metzgerei' ),
) ) ) );
$namen = array();
foreach ( Tha_Groups::items( $ohne['groups'][0] ) as $item ) {
	$namen[] = '' === $item['name'] ? '(ohne)' : $item['name'];
}
check( 'unbeschriftete Bilder stehen hinten', 'Amrein Metzgerei,(ohne)', implode( ',', $namen ) );

// Migration v4 — bestehende Installationen ohne den Schlüssel.
$vor4 = Tha_Settings::defaults();
$vor4['schema_version'] = 3;
$vor4['groups'] = array(
	array( 'key' => 'sponsoren', 'type' => 'logos', 'ids' => array() ),
	array( 'key' => 'schauspieler', 'type' => 'personen', 'ids' => array() ),
	array( 'key' => 'partner', 'type' => 'logos', 'sort' => 'manuell', 'ids' => array() ),
);
update_option( Tha_Settings::OPTION, $vor4 );
Tha_Settings::upgrade();
$nach4 = get_option( Tha_Settings::OPTION );

check( 'Migration stellt Logo-Gruppen alphabetisch', 'alpha',   $nach4['groups'][0]['sort'] );
check( 'Personen-Gruppen bleiben wie gewählt',       'manuell', $nach4['groups'][1]['sort'] );
check( 'eine eigene Wahl bleibt unberührt',          'manuell', $nach4['groups'][2]['sort'] );

echo "\nAbgeschaffte Felder (Schema 8):\n\n";

// Die Scans aus dem Programmheft und ihre Beschriftung sind weg: Eintritte
// stehen als Preiszeilen, die Speisekarte auf ihrer eigenen Seite. Ein Feld
// weniger im Backend ist hier die ganze Verbesserung — zwei Orte fuer
// dieselbe Angabe waren einer zu viel.
$d2 = Tha_Settings::defaults();
foreach ( Tha_Settings::RETIRED as $weg ) {
	check( '«' . $weg . '» ist kein Feld mehr', 'nein', array_key_exists( $weg, $d2 ) ? 'ja' : 'nein' );
}
check( 'das Tombola-Bild bleibt', '0', (string) $d2['tombola_image_id'] );

// Eine Installation aus 1.6.1 traegt sie noch in der Datenbank.
$vor8 = Tha_Settings::defaults();
$vor8['schema_version']   = 7;
$vor8['price_note']       = 'Bargeld, Karte oder Twint';
$vor8['price_image_id']   = 4711;
$vor8['gallery_ids']      = array( 1, 2, 3 );
$vor8['play_title']       = 'Bleibt stehen';
update_option( Tha_Settings::OPTION, $vor8 );
Tha_Settings::upgrade();
$nach8 = get_option( Tha_Settings::OPTION );

check( 'Migration wirft «Text Preise» weg',   'nein',          array_key_exists( 'price_note', $nach8 ) ? 'ja' : 'nein' );
check( 'und das Eintritte-Bild',              'nein',          array_key_exists( 'price_image_id', $nach8 ) ? 'ja' : 'nein' );
check( 'und die tote Galerie',                'nein',          array_key_exists( 'gallery_ids', $nach8 ) ? 'ja' : 'nein' );
check( 'alles andere bleibt unberührt',       'Bleibt stehen', $nach8['play_title'] );

// Ein Speichern darf sie nicht wieder einschleusen.
$geschmuggelt = Tha_Settings::sanitize( array( 'price_note' => 'doch wieder', 'play_title' => 'Neu' ) );
check( 'Speichern nimmt sie nicht zurueck',   'nein',          array_key_exists( 'price_note', $geschmuggelt ) ? 'ja' : 'nein' );

echo "\nDetails — Preise, Saalplan, Dokumente:\n\n";

$res = Tha_Settings::sanitize( array(
	'prices' => array(
		array( 'label' => 'Abendaufführung, Erwachsene', 'amount' => 'Fr. 22.—', 'note' => '' ),
		array( 'label' => 'Kinder', 'amount' => 'Fr. 12.—', 'note' => 'bis 16 Jahre' ),
		array( 'label' => 'Ohne Betrag', 'amount' => '', 'note' => '' ),
		array( 'label' => '', 'amount' => 'Fr. 5.—', 'note' => '' ),
	),
	'documents' => array(
		array( 'label' => 'Speisekarte', 'url' => 'https://stvauw.ch/karte.pdf', 'file_id' => 0 ),
		array( 'label' => 'Ohne Ziel',  'url' => '', 'file_id' => 0 ),
		array( 'label' => '',           'url' => 'https://stvauw.ch/x.pdf', 'file_id' => 0 ),
	),
	'seatmap_title' => 'Platz-Layout',
) );

check( 'Preiszeilen ohne Bezeichnung fallen weg', '3', (string) count( $res['prices'] ) );

// Die echten Zahlen aus dem Programmheft 2025 — seit 1.9.5 als EIN Preis
// mit Ausnahme statt einer Tabelle je Aufführungsart.
$d3 = Tha_Settings::defaults();
$map = array();
foreach ( $d3['prices'] as $r ) { $map[ $r['group'] . '/' . $r['label'] ] = $r['amount']; }
check( 'Vorpremiere Kinder',        'Fr. 2.–',  $map['Vorpremiere/Kinder bis 16 Jahre'] );
check( 'Vorpremiere Erwachsene',    'Fr. 10.–', $map['Vorpremiere/Erwachsene'] );
check( 'Abend und Sonntag: ein Preis', 'Fr. 20.–', $map['/Abendaufführung und Sonntag'] );
check( 'Sonntagskinder als Ausnahme',  'Fr. 12.–', $map['/Kinder bis 16 Jahre'] );
check( 'Premiere',                  'Fr. 60.–', $map['/Premiere'] );
$menue = '';
foreach ( $d3['prices'] as $r ) { if ( 'Premiere' === $r['label'] ) { $menue = $r['note']; } }
check( 'Menü steht als Zusatz',     'inkl. 4-Gang-Menü', $menue );
check( 'drei Kassaöffnungszeiten',  '3', (string) count( $d3['openings'] ) );
check( 'Abendkasse ab 18.30',       'ab 18.30 Uhr', $d3['openings'][1]['time'] );
check( 'Betrag bleibt erhalten',                  'Fr. 22.—', $res['prices'][0]['amount'] );
check( 'Zusatz bleibt erhalten',                  'bis 16 Jahre', $res['prices'][1]['note'] );
check( 'Zeile ohne Betrag wird gespeichert',      'Ohne Betrag', $res['prices'][2]['label'] );
check( 'Dokument ohne Ziel fällt weg',            '1', (string) count( $res['documents'] ) );
check( 'Dokument behält seine Adresse',           'https://stvauw.ch/karte.pdf', $res['documents'][0]['url'] );

echo "\nMigration der Preise (Schema 3):\n\n";

// Installation mit leerem Preisgerüst — soll die echten Zahlen bekommen.
$leer = Tha_Settings::defaults();
$leer['schema_version'] = 2;
$leer['prices'] = array( array( 'group' => '', 'label' => 'Platzhalter', 'amount' => '', 'note' => '' ) );
$leer['openings'] = array();
update_option( Tha_Settings::OPTION, $leer );
Tha_Settings::upgrade();
$n = get_option( Tha_Settings::OPTION );
check( 'leeres Gerüst bekommt echte Preise', '5', (string) count( $n['prices'] ) );
check( 'und die Kassaöffnungszeiten',        '3', (string) count( $n['openings'] ) );

// Installation mit eigenen Beträgen — darf NICHT überschrieben werden.
$eigen = Tha_Settings::defaults();
$eigen['schema_version'] = 2;
$eigen['prices'] = array( array( 'group' => 'Eigen', 'label' => 'Meins', 'amount' => 'Fr. 99.–', 'note' => '' ) );
update_option( Tha_Settings::OPTION, $eigen );
Tha_Settings::upgrade();
$e = get_option( Tha_Settings::OPTION );
check( 'eigene Preise bleiben unangetastet', 'Fr. 99.–', $e['prices'][0]['amount'] );
check( 'und werden nicht ergänzt',           '1', (string) count( $e['prices'] ) );

echo "\nMigration der Preise (Schema 9):\n\n";

// Installation, die noch die alten Standardzeilen von 1.5.0 trägt
// («Abendaufführung / alle», «Sonntag» als Gruppe) — bekommt die neue
// Gliederung: ein Preis, die Sonntagskinder als Ausnahme.
$alt = Tha_Settings::defaults();
$alt['schema_version'] = 8;
$alt['prices'] = array(
	array( 'group' => 'Vorpremiere', 'label' => 'Kinder bis 16 Jahre', 'amount' => 'Fr. 2.–', 'note' => '' ),
	array( 'group' => 'Vorpremiere', 'label' => 'Erwachsene', 'amount' => 'Fr. 10.–', 'note' => 'am Samstagnachmittag' ),
	array( 'group' => 'Sonntag', 'label' => 'Kinder bis 16 Jahre', 'amount' => 'Fr. 12.–', 'note' => '' ),
	array( 'group' => 'Sonntag', 'label' => 'Erwachsene', 'amount' => 'Fr. 20.–', 'note' => '' ),
	array( 'group' => 'Abendaufführung', 'label' => 'alle', 'amount' => 'Fr. 20.–', 'note' => '' ),
	array( 'group' => 'Premiere', 'label' => 'inkl. 4-Gang-Menü', 'amount' => 'Fr. 60.–', 'note' => '' ),
);
update_option( Tha_Settings::OPTION, $alt );
Tha_Settings::upgrade();
$n9 = get_option( Tha_Settings::OPTION );
check( 'alte Standardzeilen werden umgebaut', '5', (string) count( $n9['prices'] ) );
check( 'ein Preis für Abend und Sonntag',     'Abendaufführung und Sonntag', $n9['prices'][2]['label'] );
check( 'Sonntagskinder als Ausnahme',         'Fr. 12.–', $n9['prices'][3]['amount'] );

// Dieselben Zeilen, aber ein Betrag von Hand geändert — Finger weg.
$eigen9 = Tha_Settings::defaults();
$eigen9['schema_version'] = 8;
$eigen9['prices'] = $alt['prices'];
$eigen9['prices'][4]['amount'] = 'Fr. 25.–';
update_option( Tha_Settings::OPTION, $eigen9 );
Tha_Settings::upgrade();
$e9 = get_option( Tha_Settings::OPTION );
check( 'geänderte Preise bleiben stehen',     '6', (string) count( $e9['prices'] ) );
check( 'samt dem eigenen Betrag',             'Fr. 25.–', $e9['prices'][4]['amount'] );

echo "\nKeine doppelten Preisangaben:\n\n";

$mit = Tha_Settings::defaults();
check( 'mit echten Preisen: Karte entfällt', 'ja', Tha_Settings::has_prices( $mit ) ? 'ja' : 'nein' );

$ohne = Tha_Settings::defaults();
$ohne['prices'] = array( array( 'group' => '', 'label' => 'Noch offen', 'amount' => '', 'note' => '' ) );
check( 'ohne Beträge: Karte bleibt',        'nein', Tha_Settings::has_prices( $ohne ) ? 'ja' : 'nein' );

$keine = Tha_Settings::defaults();
$keine['prices'] = array();
check( 'gar keine Zeilen: Karte bleibt',    'nein', Tha_Settings::has_prices( $keine ) ? 'ja' : 'nein' );

echo "\nSpeisekarte — die Zeilen der Karte:\n\n";

$karte = Tha_Settings::sanitize( array(
	'menu' => array(
		array( 'kind' => 'speisen',   'group' => 'Hauptgang', 'label' => 'Schnitzel mit Pommes', 'note' => 'mit Salat', 'amount' => 'Fr. 18.–' ),
		array( 'kind' => 'speisen',   'group' => 'Dessert',   'label' => 'Dessertbuffet',        'note' => '',          'amount' => '' ),
		array( 'kind' => 'getraenke', 'group' => 'Bier',      'label' => 'Stange',               'note' => '3 dl',      'amount' => 'Fr. 4.–' ),
		// Ohne Bezeichnung ist es eine leere Formularzeile, kein Gericht.
		array( 'kind' => 'speisen',   'group' => 'Vorspeise', 'label' => '',                     'note' => '',          'amount' => 'Fr. 8.–' ),
		// Ein unbekannter Kartentyp darf nicht durchrutschen.
		array( 'kind' => 'kaffee',    'group' => '',          'label' => 'Espresso',             'note' => '',          'amount' => 'Fr. 4.–' ),
	),
) );

check( 'leere Zeile faellt weg',                    '4', (string) count( $karte['menu'] ) );
check( 'ein Gericht ohne Preis bleibt',             'Dessertbuffet', $karte['menu'][1]['label'] );
check( 'Beschreibung bleibt erhalten',              'mit Salat', $karte['menu'][0]['note'] );
check( 'unbekannter Kartentyp wird zu Speisen',     'speisen', $karte['menu'][3]['kind'] );

check( 'Speisen kommen in ihre Spalte',    '3', (string) count( Tha_Settings::menu_rows( 'speisen', $karte ) ) );
check( 'Getraenke in ihre',                '1', (string) count( Tha_Settings::menu_rows( 'getraenke', $karte ) ) );
check( 'und die Karte gilt als gefuellt',  'ja', Tha_Settings::has_menu( $karte ) ? 'ja' : 'nein' );

// Eine wirklich leere Liste zeigt auf der Seite nur den Text.
$leer_karte = Tha_Settings::defaults();
$leer_karte['menu'] = array();
check( 'ohne Zeilen bleibt sie leer',      'nein', Tha_Settings::has_menu( $leer_karte ) ? 'ja' : 'nein' );
check( 'Ueberschrift ist vorbelegt',       'Speisekarte', $leer_karte['menu_title'] );

echo "\nDie echte Karte 2025 ist ausgeliefert:\n\n";

// Aus «docs/Speise und Getraenkekarte_2025.pdf», Stand 16.10.2025. Der Test
// haelt die Zahlen fest: ein Preis, der beim Umbauen still verrutscht, faellt
// hier auf und nicht erst an der Kasse.
$k = Tha_Settings::defaults();
$preise = array();
foreach ( $k['menu'] as $row ) {
	$preise[ $row['label'] ] = $row['amount'];
}
check( 'Blattsalat mit Brot',              'Fr. 6.–',  $preise['Kleiner Blattsalat mit Brot'] );
check( 'Pastetli mit Fuellung',            'Fr. 16.–', $preise['1 Pastetli mit hausgemachter Füllung'] );
check( 'zweites Pastetli',                 'Fr. 4.–',  $preise['Plus 1 Pastetli auf gleichem Teller'] );
check( 'Schweinshalsbraten',               'Fr. 20.–', $preise['Schweinshalsbraten'] );
check( 'Sandwich mit Kaese oder Schinken', 'Fr. 7.50', $preise['Sandwich mit Käse oder Schinken'] );
check( 'Theater-Kafi',                     'Fr. 7.50', $preise['Theater-Kafi'] );
check( 'Cremeschnitte',                    'Fr. 5.50', $preise['Cremeschnitte'] );
check( 'Mineral in zwei Groessen',         'Fr. 4.– / 8.–', $preise['Mineral'] );

check( 'elf Zeilen auf der Speisekarte',   '11', (string) count( Tha_Settings::menu_rows( 'speisen', $k ) ) );
check( 'siebzehn auf der Getraenkekarte',  '17', (string) count( Tha_Settings::menu_rows( 'getraenke', $k ) ) );

// Die Gaenge in der Reihenfolge der Karte — die Spalte darf nicht mit dem
// Dessert anfangen.
$gaenge = array();
foreach ( Tha_Settings::menu_rows( 'speisen', $k ) as $row ) {
	if ( ! in_array( $row['group'], $gaenge, true ) ) {
		$gaenge[] = $row['group'];
	}
}
check( 'Reihenfolge der Gaenge', 'Warme Küche|Für den kleinen Hunger|Dessert', implode( '|', $gaenge ) );

// Die Lieferanten stehen auf der Karte und gehoeren dazu.
check( 'Lieferanten stehen unter der Karte', 'ja', false !== strpos( $k['menu_note'], 'Metzgerei Huwyler' ) ? 'ja' : 'nein' );
check( 'warme Kueche nur vor der Auffuehrung', 'ja', false !== strpos( $k['menu_intro'], 'nur vor der Aufführung' ) ? 'ja' : 'nein' );

echo "\nMigration der Speisekarte (Schema 6):\n\n";

// Installation aus 1.6.0: Seitentyp vorhanden, Karte leer.
$vor6 = Tha_Settings::defaults();
$vor6['schema_version'] = 5;
$vor6['menu']           = array();
$vor6['menu_intro']     = '';
$vor6['menu_note']      = '';
update_option( Tha_Settings::OPTION, $vor6 );
Tha_Settings::upgrade();
$nach6 = get_option( Tha_Settings::OPTION );
check( 'leere Karte bekommt die echte',      '28', (string) count( $nach6['menu'] ) );
check( 'samt Einleitung',                    'ja', '' !== $nach6['menu_intro'] ? 'ja' : 'nein' );

// Wer schon selbst getippt hat, behaelt seine Karte.
$eigene = Tha_Settings::defaults();
$eigene['schema_version'] = 5;
$eigene['menu']           = array( array( 'kind' => 'speisen', 'group' => '', 'label' => 'Nur eines', 'note' => '', 'amount' => 'Fr. 9.–' ) );
update_option( Tha_Settings::OPTION, $eigene );
Tha_Settings::upgrade();
$behalten = get_option( Tha_Settings::OPTION );
check( 'eigene Karte bleibt unangetastet',   '1', (string) count( $behalten['menu'] ) );
check( 'und behaelt ihren Eintrag',          'Nur eines', $behalten['menu'][0]['label'] );

// Zeilen aus einer aelteren Installation kennen `kind` noch nicht.
$alt = Tha_Settings::defaults();
$alt['menu'] = array( array( 'label' => 'Suppe', 'amount' => 'Fr. 6.–' ) );
check( 'Zeile ohne Kartentyp gilt als Speise', '1', (string) count( Tha_Settings::menu_rows( 'speisen', $alt ) ) );

echo "\nUpdates — Reihenfolge und leere Eintraege:\n\n";

$ups = Tha_Settings::sanitize( array(
	'updates' => array(
		array( 'date' => '2026-03-01', 'title' => 'Mitte',   'text' => '' ),
		array( 'date' => '',           'title' => 'Ohne Datum', 'text' => '' ),
		array( 'date' => '2026-09-15', 'title' => 'Neuestes', 'text' => 'Der Vorverkauf laeuft.' ),
		array( 'date' => '2025-11-01', 'title' => 'Aeltestes', 'text' => '' ),
		// Weder Ueberschrift noch Text: leere Formularzeile.
		array( 'date' => '2026-05-05', 'title' => '', 'text' => '' ),
		// Kaputtes Datum darf den Eintrag nicht mitnehmen — nur sein Datum.
		array( 'date' => '2026-13-45', 'title' => 'Krummes Datum', 'text' => '' ),
	),
) );

check( 'leere Zeile faellt weg',        '5', (string) count( $ups['updates'] ) );
check( 'neuestes zuerst',               'Neuestes',  $ups['updates'][0]['title'] );
check( 'dann das mittlere',             'Mitte',     $ups['updates'][1]['title'] );
check( 'dann das aelteste',             'Aeltestes', $ups['updates'][2]['title'] );
check( 'undatierte ans Ende',           'ja', in_array( $ups['updates'][3]['title'], array( 'Ohne Datum', 'Krummes Datum' ), true ) ? 'ja' : 'nein' );
check( 'krummes Datum wird verworfen',  '', $ups['updates'][4]['date'] );

update_option( Tha_Settings::OPTION, array_merge( Tha_Settings::defaults(), array( 'updates' => $ups['updates'] ) ) );
$geladen = Tha_Settings::updates( 0, array_merge( Tha_Settings::defaults(), array( 'updates' => $ups['updates'] ) ) );
check( 'gelesen wird dieselbe Reihenfolge', 'Neuestes', $geladen[0]['title'] );
check( 'anzahl schneidet ab',               '2', (string) count( Tha_Settings::updates( 2, array_merge( Tha_Settings::defaults(), array( 'updates' => $ups['updates'] ) ) ) ) );

// Ein Eintrag ohne Ueberschrift, aber mit Text, ist ein gueltiger Eintrag.
$nur_text = Tha_Settings::sanitize( array(
	'updates' => array( array( 'date' => '2026-02-02', 'title' => '', 'text' => 'Kurze Mitteilung.' ) ),
) );
check( 'Eintrag nur mit Text bleibt', '1', (string) count( $nur_text['updates'] ) );

// Ein anderer Tab darf die Listen nicht loeschen — derselbe Fall wie ueberall.
update_option( Tha_Settings::OPTION, array_merge( Tha_Settings::defaults(), array(
	'menu'    => $karte['menu'],
	'updates' => $ups['updates'],
) ) );
$anderer_tab = Tha_Settings::sanitize( array( 'play_title' => 'Etwas anderes' ) );
check( 'Karte ueberlebt fremdes Speichern',   '4', (string) count( $anderer_tab['menu'] ) );
check( 'Updates ueberleben ebenso',           '5', (string) count( $anderer_tab['updates'] ) );

echo "\nSpieldaten — zwei Vorstellungen am selben Tag:\n\n";

$dd = Tha_Settings::sanitize( array(
	'dates' => array(
		array( 'date' => '2026-11-18', 'time' => '20.00' ),
		array( 'date' => '2026-11-14', 'time' => '20.00', 'label' => 'Premiere' ),
		array( 'date' => '2026-11-14', 'time' => '14.00', 'label' => 'Vorpremiere' ),
		array( 'date' => '2026-11-14', 'time' => '9.00', 'label' => 'Schulvorstellung' ),
	),
) );
check( 'gleicher Tag: 9.00 vor 14.00',   'Schulvorstellung', $dd['dates'][0]['label'] );
check( 'dann die Vorpremiere um 14.00',  'Vorpremiere',      $dd['dates'][1]['label'] );
check( 'dann die Premiere um 20.00',     'Premiere',         $dd['dates'][2]['label'] );
check( 'anderer Tag ans Ende',           '2026-11-18',       $dd['dates'][3]['date'] );

// Eine vor dieser Version gespeicherte Liste steht evtl. unsortiert in der
// Datenbank — get() sortiert deshalb auch beim Lesen.
update_option( Tha_Settings::OPTION, array_merge( Tha_Settings::defaults(), array(
	'dates' => array(
		array( 'date' => '2026-11-14', 'time' => '20.00', 'label' => 'Premiere', 'note' => '', 'status' => 'offen', 'ticket_url' => '' ),
		array( 'date' => '2026-11-14', 'time' => '14.00', 'label' => 'Vorpremiere', 'note' => '', 'status' => 'offen', 'ticket_url' => '' ),
	),
) ) );
Tha_Settings::flush_cache();
$gelesen = Tha_Settings::get();
check( 'gelesen: Vorpremiere zuerst',    'Vorpremiere', $gelesen['dates'][0]['label'] );

printf( "\n%d Prüfungen, %d Fehler\n", $runs, $fails );
exit( $fails > 0 ? 1 : 0 );
