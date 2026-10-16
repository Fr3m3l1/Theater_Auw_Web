<?php
/**
 * Feindliches-Theme-Test.
 *
 * Rendert dieselbe Seite noch einmal, aber eingebettet in einen Container, der
 * sich verhält wie ein sehr meinungsstarkes WordPress-Theme: schmaler
 * Inhaltsbereich, eigene Schrift, content-box, aggressive Resets, !important.
 *
 * Das ist die Abnahmehürde für die Abschottung: sieht es HIER richtig aus,
 * überlebt es auch das unbekannte Theme auf stvauw.ch.
 *
 * Aufruf:  php preview/hostile.php > preview/hostile.html
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

update_option( Tha_Settings::OPTION, json_decode( file_get_contents( __DIR__ . '/fixtures.json' ), true ) );
$GLOBALS['tha_fake_now'] = '2026-11-01 12:00';

$body = Tha_Renderer::render( array( 'phase' => 'vorverkauf', 'width' => 'viewport' ) );

/**
 * Die zwei Seitentypen mit eigenem Inhalt gehören mit in den Test.
 *
 * Sie sind es, die am ehesten etwas abbekommen: die Speisekarte besteht aus
 * <dl>/<dt>/<dd>, die Updates aus einer <ol> — genau die Elemente, für die ein
 * Theme Innenmasse, Aufzählungspunkte und Zeilenabstände mitbringt.
 */
$karte   = Tha_Renderer::render_subpage( array( 'titel' => 'Speisekarte', 'kicker' => 'Aus unserer Küche', 'section' => 'karte', 'width' => 'viewport', 'kontakt' => false ) );
$updates = Tha_Renderer::render_subpage( array( 'titel' => 'Updates', 'kicker' => 'Neues vom Theater', 'section' => 'updates', 'width' => 'viewport' ) );
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Theater Auw — Test gegen ein feindliches Theme</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Big+Shoulders:opsz,wght@10..72,600;10..72,700&family=Libre+Franklin:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap">

<!-- Das Plugin-CSS ZUERST — `wp_enqueue_style()` ist hier nur eine Attrappe,
     die Datei muss also von Hand herein. Und sie kommt bewusst vor dem Theme:
     bei gleicher Spezifität gewinnt dann das Theme. Genau das soll der Test
     aushalten. -->
<link rel="stylesheet" href="../plugin/theater-auw/assets/css/theater-auw.css">

<style>
/* ================= Das "feindliche Theme" ================= */
* { box-sizing: content-box; }

body {
	margin: 0;
	font-family: Georgia, serif;
	font-size: 19px;
	line-height: 2.1;
	color: #334;
	background: #e8e4dd;
}

.site-header {
	background: #223;
	color: #fff;
	padding: 18px 24px;
	font-family: Georgia, serif;
}

.entry-content {
	max-width: 720px;      /* der schmale Container, der uns zerdrücken will */
	margin: 40px auto;
	background: #fff;
	padding: 32px;
	border: 1px solid #ccc;
}

/* Genau die Regeln, die in freier Wildbahn wehtun */
.entry-content h1,
.entry-content h2,
.entry-content h3 {
	font-family: Georgia, serif !important;
	color: #900 !important;
	text-transform: none !important;
	letter-spacing: 0 !important;
	margin: 2em 0 1em !important;
	font-weight: 400 !important;
}

.entry-content p {
	margin: 0 !important;
	font-family: Georgia, serif;
	text-indent: 2em;
}

.entry-content a {
	color: #06c !important;
	text-decoration: underline !important;
}

.entry-content ul { list-style: disc outside !important; padding-left: 40px !important; }
.entry-content li { display: list-item !important; margin-bottom: 1em !important; }
.entry-content img { border: 6px solid #f0c !important; }
.entry-content button, .entry-content .tha-btn { font: inherit; }

.site-footer { background: #223; color: #aab; padding: 24px; text-align: center; }
</style>
</head>
<body>

<header class="site-header">STV Auw — simuliertes Theme (Georgia, 720px, content-box, !important)</header>

<div class="entry-content">
	<h2>Ein Titel des Themes</h2>
	<p>Ein Absatz des Themes, damit man den Unterschied sieht. Das Plugin beginnt darunter.</p>

	<?php echo $body; // phpcs:ignore ?>

	<h2>Und weiter unten: die Speisekarte</h2>
	<p>Ein Absatz des Themes davor. Darunter beginnt der Seitentyp «Speisekarte».</p>

	<?php echo $karte; // phpcs:ignore ?>

	<h2>Und die Updates</h2>
	<p>Wieder ein Absatz des Themes. Die Liste darunter muss trotz
		<code>list-style: disc !important</code> ohne Punkte auskommen.</p>

	<?php echo $updates; // phpcs:ignore ?>

	<p>Und noch ein Absatz des Themes, nach dem Plugin.</p>
</div>

<footer class="site-footer">Footer des Themes</footer>

<script src="../plugin/theater-auw/assets/js/theater-auw.js"></script>
</body>
</html>
