<?php
/**
 * Rendert die vier Phasen mit den ECHTEN Plugin-Templates in statische
 * HTML-Dateien unter preview/.
 *
 * Aufruf:  php preview/render.php [YYYY-MM-DD HH:MM]
 *
 * Der optionale Zeitpunkt setzt die "aktuelle" Zeit — damit lässt sich
 * ansehen, wie die Seite an einem beliebigen Tag der Saison aussieht.
 */

require __DIR__ . '/wp-stubs.php';

$plugin = dirname( __DIR__ ) . '/plugin/theater-auw';

define( 'THA_VERSION', '1.6.1' );
define( 'THA_FILE', $plugin . '/theater-auw.php' );
define( 'THA_DIR', $plugin . '/' );
define( 'THA_URL', '../plugin/theater-auw/' );

require THA_DIR . 'includes/helpers.php';
require THA_DIR . 'includes/class-tha-settings.php';
require THA_DIR . 'includes/class-tha-phase.php';
require THA_DIR . 'includes/class-tha-groups.php';
require THA_DIR . 'includes/class-tha-rueckblick.php';
require THA_DIR . 'includes/class-tha-renderer.php';
require THA_DIR . 'includes/class-tha-assets.php';
require THA_DIR . 'includes/class-tha-anmeldung.php';

// Fixtures = exakt die Form von theater_auw_settings.
$fixtures = json_decode( file_get_contents( __DIR__ . '/fixtures.json' ), true );
if ( ! is_array( $fixtures ) ) {
	fwrite( STDERR, "fixtures.json konnte nicht gelesen werden\n" );
	exit( 1 );
}
update_option( Tha_Settings::OPTION, $fixtures );

$phases = array(
	'vorbereitung' => 'Vorbereitung',
	'vorverkauf'   => 'Vorverkauf',
	'spielzeit'    => 'Spielzeit',
	'danke'        => 'Danke',
);

/**
 * Jede Phase wird zu einem Zeitpunkt gerendert, an dem sie auch wirklich
 * gelten würde — sonst zeigt der Countdown Unsinn.
 */
$when = array(
	'vorbereitung' => '2026-08-01 12:00',
	'vorverkauf'   => '2026-10-01 12:00',
	'spielzeit'    => '2026-11-26 15:00', // Tag mit Vorstellung
	'danke'        => '2026-12-05 12:00',
);

$written = array();

foreach ( $phases as $slug => $label ) {
	tha_preview_set_now( $when[ $slug ] );

	$body = Tha_Renderer::render( array( 'phase' => $slug ) );
	$html = tha_preview_shell( $label, $slug, $body, $when[ $slug ] );

	$file = __DIR__ . '/' . $slug . '.html';
	file_put_contents( $file, $html );
	$written[] = basename( $file );
}

/**
 * Die zwei Seitentypen mit eigenem Inhalt: Speisekarte und Updates.
 *
 * Sie hängen nicht an der Phase — gerendert wird zu einem Zeitpunkt im
 * Vorverkauf, weil dann die Navigation ihren Ticket-Knopf zeigt und man sieht,
 * wie die Seite mit Leiste aussieht.
 */
tha_preview_set_now( '2026-10-01 12:00' );

$seiten = array(
	'karte'   => array( 'label' => 'Speisekarte', 'section' => 'karte' ),
	'updates' => array( 'label' => 'Updates', 'section' => 'updates' ),
);

$fix = Tha_Settings::get();

foreach ( $seiten as $slug => $seite ) {
	$body = Tha_Renderer::render_subpage(
		array(
			'titel'   => 'karte' === $slug ? $fix['menu_title'] : $fix['updates_title'],
			'kicker'  => 'karte' === $slug ? $fix['menu_kicker'] : $fix['updates_kicker'],
			'section' => $seite['section'],
		)
	);
	$html = tha_preview_shell( $seite['label'], $slug, $body, '2026-10-01 12:00' );

	$file = __DIR__ . '/' . $slug . '.html';
	file_put_contents( $file, $html );
	$written[] = basename( $file );
}

/**
 * Die Anmeldeseite. Die Stubs kennen nur eine Seitennummer (0) — die
 * Einstellung der Vorschau wird deshalb auf 0 gesetzt, damit die Erkennung
 * zugreift. Gezeigt wird der Seitenkopf über einer Attrappe des Divi-
 * Inhalts; das echte Formular kann die Vorschau nicht nachbauen.
 */
$fix['anmeldung_page_id'] = 0;
update_option( Tha_Settings::OPTION, $fix );
Tha_Settings::flush_cache();

$anmelde_inhalt = '<h2>Sponsor Anmeldung</h2>'
	. '<p>Platzhalter für das Divi-Formular — auf der echten Seite steht hier das Anmeldeformular.</p>'
	. '<p><input type="text" placeholder="Vorname"></p>';

$body = Tha_Anmeldung::render_pagehead( $anmelde_inhalt );
$html = tha_preview_shell( 'Anmeldung', 'anmeldung', $body, '2026-10-01 12:00' );
file_put_contents( __DIR__ . '/anmeldung.html', $html );
$written[] = 'anmeldung.html';

echo "Gerendert: " . implode( ', ', $written ) . "\n";

// ---------------------------------------------------------------------

/**
 * `tha_now()` cached nicht, liest aber die echte Uhr. Für die Vorschau
 * überschreiben wir sie über eine globale Variable, die helpers.php kennt.
 */
function tha_preview_set_now( $datetime ) {
	$GLOBALS['tha_fake_now'] = $datetime;
}

function tha_preview_shell( $label, $slug, $body, $when ) {
	$nav = '';
	foreach ( array( 'vorbereitung' => 'Vorbereitung', 'vorverkauf' => 'Vorverkauf', 'spielzeit' => 'Spielzeit', 'danke' => 'Danke', 'karte' => 'Speisekarte', 'updates' => 'Updates', 'anmeldung' => 'Anmeldung' ) as $s => $l ) {
		$active = $s === $slug ? ' class="on"' : '';
		$nav   .= '<a href="' . $s . '.html"' . $active . '>' . $l . '</a>';
	}

	return '<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Theater Auw — ' . esc_html( $label ) . '</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Big+Shoulders:opsz,wght@10..72,600;10..72,700&family=Libre+Franklin:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap">
<link rel="stylesheet" href="../plugin/theater-auw/assets/css/theater-auw.css">
<style>
  body{margin:0;background:#faf8f4}
  .pv{position:fixed;top:12px;right:12px;z-index:100001;display:flex;gap:4px;background:#1e1a1b;padding:4px;border-radius:999px;box-shadow:0 12px 32px rgba(0,0,0,.3);font-family:system-ui,sans-serif}
  .pv a{padding:6px 13px;border-radius:999px;color:#b5aeaa;text-decoration:none;font-size:12.5px;font-weight:600}
  .pv a.on{background:#e02424;color:#fff}
  .pv-when{position:fixed;bottom:12px;left:12px;z-index:100001;background:#1e1a1b;color:#b5aeaa;font:12px/1 system-ui,sans-serif;padding:7px 12px;border-radius:6px}
</style>
</head>
<body>
<nav class="pv">' . $nav . '</nav>
<div class="pv-when">Vorschau-Zeitpunkt: ' . esc_html( $when ) . '</div>
' . $body . '
<script src="../plugin/theater-auw/assets/js/theater-auw.js"></script>
</body>
</html>';
}
