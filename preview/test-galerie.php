<?php
/**
 * Test der Sponsorenwand: Logos gross ansehen.
 *
 * Eine Logokachel ist ein Link auf das Bild in voller Grösse; ein Dialog
 * zeigt es. Zwei Dinge können dabei kaputtgehen, ohne dass es beim
 * Durchklicken des Backends auffällt:
 *
 *   - der Link zeigt auf ein anderes Bild als die Kachel darüber,
 *   - der Klick hängt an Kacheln, die gar keine Logos sind (Porträts,
 *     Gruppenfotos) — oder an Kacheln ohne Bild, wo er ins Leere führt.
 *
 * Geprüft wird die Ausgabe der echten Vorlage, nicht die Absicht im Quelltext.
 *
 * Aufruf:  php preview/test-galerie.php
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
require THA_DIR . 'includes/class-tha-renderer.php';

$fails = 0;
$runs  = 0;

function check( $label, $expected, $actual ) {
	global $fails, $runs;
	$runs++;
	$ok = ( (string) $expected === (string) $actual );
	if ( ! $ok ) {
		$fails++;
	}
	printf( "%s  %-46s erwartet=%-24s erhalten=%s\n", $ok ? 'ok  ' : 'FAIL', $label, $expected, $actual );
}

$fixtures = json_decode( file_get_contents( __DIR__ . '/fixtures.json' ), true );
update_option( Tha_Settings::OPTION, $fixtures );

/**
 * Eine Gruppe rendern — genau so, wie die Unterseite es tut.
 */
function galerie( $key ) {
	$group = Tha_Groups::get( $key );
	$ctx   = array( 'uid' => '', 'settings' => Tha_Settings::get() );

	ob_start();
	include THA_DIR . 'public/views/section-gruppe.php';

	return ob_get_clean();
}

// ---------------------------------------------------------------------
echo "\nOhne Bildadressen: kein Klick, kein leerer Dialog\n\n";
// ---------------------------------------------------------------------

// Die Vorschau hat keine Mediathek, also liefert `wp_get_attachment_image_url`
// nichts. Genau der Zustand, in dem die Seite zuerst läuft: die Kacheln sind
// gestaltete Platzhalter. Ein Link ins Leere wäre hier schlimmer als keiner.
$leer = galerie( 'sponsoren' );

check( 'Sponsorenkacheln sind keine Links', '0', substr_count( $leer, 'data-tha-zoom' ) );
check( 'und kein Dialog wartet darauf',     '0', substr_count( $leer, '<dialog' ) );

// ---------------------------------------------------------------------
echo "\nMit Bildadressen: jede Kachel öffnet ihr eigenes Bild\n\n";
// ---------------------------------------------------------------------

$ids = Tha_Groups::get( 'sponsoren' )['ids'];
$GLOBALS['tha_preview_urls'] = array();
foreach ( $ids as $id ) {
	$GLOBALS['tha_preview_urls'][ $id ] = 'https://stvauw.ch/wp-content/uploads/gross-' . $id . '.jpg';
}

$wand = galerie( 'sponsoren' );

check( 'jede Kachel ist ein Link',      count( $ids ), substr_count( $wand, 'data-tha-zoom' ) );
check( 'und es gibt genau einen Dialog', '1',          substr_count( $wand, '<dialog' ) );
check( 'mit Bild und Beschriftung darin',
	'2',
	substr_count( $wand, 'data-tha-lightbox-img' ) + substr_count( $wand, 'data-tha-lightbox-caption' )
);

// Der Kern: die Zuordnung Kachel → Bild. Wäre sie verschoben, sähe man in der
// Wand das erste Logo und im Dialog das zweite.
$falsch = array();
foreach ( $ids as $id ) {
	if ( 1 !== substr_count( $wand, 'gross-' . $id . '.jpg' ) ) {
		$falsch[] = $id;
	}
}
check( 'jedes Logo zeigt auf sein Bild', '', implode( ',', $falsch ) );

// Der Dialog liegt hinter der Liste im selben `.tha-wrap` — dort findet ihn
// auch das JavaScript (`link.closest('.tha-wrap')`).
check( 'Dialog steht hinter der Liste im selben Kasten', '1',
	preg_match( '/<ul class="tha-people[^>]*>.*<\/ul>.*<dialog class="tha-lightbox"/s', $wand )
);

// Der Hinweis für Screenreader: der Name steht nicht nur im Bild.
check( 'der Link nennt den Namen', '1',
	preg_match( '/data-tha-zoom[^>]*aria-label="Aargauische Kantonalbank — in gross ansehen"/s', $wand )
);
check( 'der Link trägt Name und Ort für die Beschriftung', '1',
	preg_match( '/data-tha-name="Aargauische Kantonalbank"/', $wand )
);

// Die Masse der grossen Fassung: nur mit ihnen hat der Dialog gleich die
// richtige Grösse und zieht ein kleines Logo nicht auf Kastenbreite.
check( 'die Masse des grossen Bildes stehen im Link', '1',
	preg_match( '/data-tha-zoom[^>]*data-tha-w="1600"[^>]*data-tha-h="1200"[^>]*data-tha-name="Aargauische Kantonalbank"/s', $wand )
);
check( 'und keine Kachel hat Masse ohne Adresse', '0',
	substr_count( $wand, 'data-tha-w="0"' ) + substr_count( $wand, 'data-tha-h="0"' )
);

// ---------------------------------------------------------------------
echo "\nPorträts und Gruppenfotos bleiben Kacheln\n\n";
// ---------------------------------------------------------------------

// Dort ist das Gesicht in der Kachel schon zu sehen; ein Dialog, der nichts
// Neues zeigt, wäre nur ein Umweg.
$personen = galerie( 'schauspieler' );

check( 'Schauspieler sind keine Links', '0', substr_count( $personen, 'data-tha-zoom' ) );
check( 'und haben keinen Dialog',       '0', substr_count( $personen, '<dialog' ) );

// ---------------------------------------------------------------------
echo "\nEin Bild, das aus der Mediathek verschwunden ist\n\n";
// ---------------------------------------------------------------------

// Ein einzelnes Logo ohne Adresse — etwa weil das Bild gelöscht wurde. Nur
// diese eine Kachel verliert den Klick, der Rest der Wand bleibt.
$GLOBALS['tha_preview_urls'] = array( 402 => 'https://stvauw.ch/wp-content/uploads/gross-402.jpg' );
$teil = galerie( 'sponsoren' );

check( 'nur die eine Kachel ist ein Link', '1',  substr_count( $teil, 'data-tha-zoom' ) );
check( 'und der Dialog bleibt für sie',    '1',  substr_count( $teil, '<dialog' ) );

$GLOBALS['tha_preview_urls'] = array();

printf( "\n%d Prüfungen, %d Fehler\n", $runs, $fails );
exit( $fails ? 1 : 0 );
