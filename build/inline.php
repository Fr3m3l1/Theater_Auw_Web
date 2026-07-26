<?php
/**
 * Erzeugt zwei eigenständige HTML-Dateien, beide ohne externe Dateien
 * (CSS und JS sind eingebettet):
 *
 *   build/dist/theater-auw-vorschau.html
 *       Alle vier Phasen mit Umschalter. Zum Anschauen und Abnehmen —
 *       einfach im Browser öffnen, funktioniert auch per E-Mail verschickt.
 *
 *   build/dist/theater-auw-inline.html
 *       Rückfallebene, falls sich das Plugin auf dem Hosting nicht
 *       installieren lässt: der Inhalt zum Einfügen in einen
 *       «Custom HTML»-Block auf der Theater-Seite.
 *
 * Aufruf:  php build/inline.php
 */

$root = dirname( __DIR__ );

require $root . '/preview/wp-stubs.php';

$plugin = $root . '/plugin/theater-auw';

define( 'THA_VERSION', '1.6.1' );
define( 'THA_FILE', $plugin . '/theater-auw.php' );
define( 'THA_DIR', $plugin . '/' );
define( 'THA_URL', '' );

require THA_DIR . 'includes/helpers.php';
require THA_DIR . 'includes/class-tha-settings.php';
require THA_DIR . 'includes/class-tha-phase.php';
require THA_DIR . 'includes/class-tha-groups.php';
require THA_DIR . 'includes/class-tha-rueckblick.php';
require THA_DIR . 'includes/class-tha-renderer.php';
require THA_DIR . 'includes/class-tha-assets.php';

update_option(
	Tha_Settings::OPTION,
	json_decode( file_get_contents( $root . '/preview/fixtures.json' ), true )
);

$css = file_get_contents( $plugin . '/assets/css/theater-auw.css' );
$js  = file_get_contents( $plugin . '/assets/js/theater-auw.js' );

$fonts = 'https://fonts.googleapis.com/css2?family=Big+Shoulders:opsz,wght@10..72,600;10..72,700'
	. '&family=Libre+Franklin:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap';

$phases = array(
	'vorbereitung' => array( 'Vorbereitung', '2026-08-01 12:00', 'Vor dem Theater — wo wir jetzt stehen' ),
	'vorverkauf'   => array( 'Vorverkauf', '2026-10-01 12:00', 'Verkauf läuft, Countdown auf die Premiere' ),
	'spielzeit'    => array( 'Spielzeit', '2026-11-26 15:00', 'Während des Theaters, heute Vorstellung' ),
	'danke'        => array( 'Danke', '2026-12-05 12:00', 'Nach der Derniere' ),
);

// ------------------------------------------------------------------
// 1. Vorschau mit allen vier Phasen
// ------------------------------------------------------------------

$panels = '';
$tabs   = '';

foreach ( $phases as $slug => $meta ) {
	$GLOBALS['tha_fake_now'] = $meta[1];

	$panels .= '<div class="pv-panel" data-panel="' . $slug . '"'
		. ( 'vorbereitung' === $slug ? '' : ' hidden' ) . '>'
		. Tha_Renderer::render( array( 'phase' => $slug ) )
		. '</div>';

	$tabs .= '<button type="button" data-go="' . $slug . '"'
		. ( 'vorbereitung' === $slug ? ' class="on"' : '' )
		. ' title="' . esc_attr( $meta[2] ) . '">' . esc_html( $meta[0] ) . '</button>';
}

// Unterseiten — gleiche Navigation, kompakter Kopf. Die letzten beiden haben
// keine Bildergruppe, sondern einen eigenen Abschnitt: Speisekarte und Updates.
$subpages = array(
	'schauspieler' => array( 'Schauspieler', 'Die Schauspieler', 'Theater 2026', 'schauspieler',
		'Zwölf Spielerinnen und Spieler stehen dieses Jahr auf der Bühne.', '' ),
	'kulissen'     => array( 'Kulissen', 'Hinter den Kulissen', 'Ohne sie geht nichts', 'kulissen',
		'Damit auf der Bühne alles läuft, arbeiten hinter ihr viele Hände.', '' ),
	'sponsoren'    => array( 'Sponsoren', 'Sponsoren', 'Danke', 'sponsoren', '', '' ),
	'karte'        => array( 'Speisekarte', 'Speisekarte', 'Aus unserer Küche', '', '', 'karte' ),
	'updates'      => array( 'Updates', 'Updates', 'Neues vom Theater', '', '', 'updates' ),
);

foreach ( $subpages as $slug => $sp ) {
	$GLOBALS['tha_fake_now'] = '2026-08-01 12:00';

	$panels .= '<div class="pv-panel" data-panel="' . $slug . '" hidden>'
		. Tha_Renderer::render_subpage(
			array( 'titel' => $sp[1], 'kicker' => $sp[2], 'gruppe' => $sp[3], 'lead' => $sp[4], 'section' => $sp[5] ),
			''
		)
		. '</div>';

	$tabs .= '<button type="button" data-go="' . $slug . '" class="pv-sub">' . esc_html( $sp[0] ) . '</button>';
}

$shell = '<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Theater Auw — Entwurf der neuen Seite</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="' . $fonts . '">
<style>
body{margin:0;background:#faf8f4}
.pv-bar{position:fixed;top:14px;right:14px;z-index:100002;display:flex;gap:4px;
  background:#1e1a1b;padding:4px;border-radius:999px;box-shadow:0 12px 32px rgba(0,0,0,.35);
  font-family:"Libre Franklin",system-ui,sans-serif}
.pv-bar button{padding:7px 14px;border:0;border-radius:999px;background:transparent;color:#b5aeaa;
  font:600 12.5px/1 inherit;cursor:pointer;transition:background .12s,color .12s}
.pv-bar button.on{background:#e02424;color:#fff}
.pv-bar button.pv-sub{color:#8d8683}
.pv-bar button.pv-sub.on{background:#3d3738;color:#f8f6f2}
.pv-bar::before{content:"Phasen";align-self:center;padding:0 8px 0 6px;color:#6d6664;font:600 11px/1 inherit;text-transform:uppercase;letter-spacing:.09em}
.pv-bar button:hover:not(.on){color:#f8f6f2}
.pv-hint{position:fixed;bottom:14px;left:14px;z-index:100002;background:#1e1a1b;color:#b5aeaa;
  font:12px/1.5 "Libre Franklin",system-ui,sans-serif;padding:9px 13px;border-radius:8px;max-width:280px}
.pv-hint b{color:#f8f6f2;display:block}
@media print{.pv-bar,.pv-hint{display:none}}
' . $css . '
</style>
</head>
<body>
<nav class="pv-bar">' . $tabs . '</nav>
<div class="pv-hint"><b>Entwurf</b>Oben rechts umschalten: links die vier Saison-Phasen der Hauptseite, rechts zwei Unterseiten. Bilder sind Platzhalter — die Namen darunter kommen aus der Mediathek.</div>
' . $panels . '
<script>' . $js . '</script>
<script>
document.querySelectorAll(".pv-bar button").forEach(function(b){
  b.addEventListener("click",function(){
    var id=b.dataset.go;
    document.querySelectorAll(".pv-bar button").forEach(function(x){x.classList.toggle("on",x===b)});
    document.querySelectorAll(".pv-panel").forEach(function(p){
      p.hidden = (p.dataset.panel!==id);
    });
    window.scrollTo({top:0});
  });
});
</script>
</body>
</html>';

@mkdir( $root . '/build/dist', 0777, true );
file_put_contents( $root . '/build/dist/theater-auw-vorschau.html', $shell );

// ------------------------------------------------------------------
// 2. Rückfallebene: Inhalt für einen «Custom HTML»-Block
// ------------------------------------------------------------------

$GLOBALS['tha_fake_now'] = null;
unset( $GLOBALS['tha_fake_now'] );

$fallback = "<!--\n"
	. "  Theater Auw — Rückfallebene\n"
	. "  ---------------------------\n"
	. "  Nur benutzen, wenn sich das Plugin NICHT installieren lässt.\n"
	. "  Diesen ganzen Block in einen «Custom HTML»-Block auf der Theater-Seite\n"
	. "  einfügen. Achtung: die Phase ist dann fest verdrahtet — zum Wechseln\n"
	. "  unten in der letzten Zeile data-phase und den Inhalt austauschen.\n"
	. "  Erfordert Administrator-Rechte (unfiltered_html), sonst entfernt\n"
	. "  WordPress <style> und <script> beim Speichern.\n"
	. "-->\n"
	. '<link rel="stylesheet" href="' . $fonts . '">' . "\n"
	. '<style>' . $css . '</style>' . "\n"
	. Tha_Renderer::render( array( 'phase' => 'vorbereitung' ) ) . "\n"
	. '<script>' . $js . '</script>' . "\n";

file_put_contents( $root . '/build/dist/theater-auw-inline.html', $fallback );

printf(
	"Geschrieben:\n  build/dist/theater-auw-vorschau.html  (%s)\n  build/dist/theater-auw-inline.html    (%s)\n",
	size_format_local( strlen( $shell ) ),
	size_format_local( strlen( $fallback ) )
);

function size_format_local( $bytes ) {
	return $bytes > 1024 ? round( $bytes / 1024 ) . ' KB' : $bytes . ' B';
}
