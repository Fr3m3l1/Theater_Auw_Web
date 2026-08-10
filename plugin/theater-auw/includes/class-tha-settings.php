<?php
/**
 * Options-Schema, Defaults und Sanitizing.
 *
 * Alles liegt in EINER autoloaded Option (`theater_auw_settings`). Das hält
 * Speichern atomar und vermeidet 25 einzelne autoloaded Zeilen in wp_options.
 */

defined( 'ABSPATH' ) || exit;

class Tha_Settings {

	const OPTION         = 'theater_auw_settings';
	const GROUP          = 'theater_auw';
	const SCHEMA_VERSION = 8;

	const PHASES   = array( 'vorbereitung', 'vorverkauf', 'spielzeit', 'danke' );
	const STATUSES = array( 'offen', 'wenige', 'ausverkauft', 'vorbei' );

	/**
	 * Felder, die es einmal gab und die niemand mehr braucht.
	 *
	 * Sie standen fast alle fuer dieselbe ueberholte Idee: Angaben aus dem
	 * Programmheft als Scan zeigen, statt sie zu erfassen. Seit es die
	 * Preistabelle und die Speisekarte als echte Zeilen gibt, waeren es zwei
	 * Orte fuer dieselbe Sache — und im Backend zwei Felder, bei denen niemand
	 * weiss, welches gilt.
	 */
	const RETIRED = array(
		'price_note',       // «Text Preise» — die Preiszeilen sagen dasselbe genauer
		'price_image_id',   // Scan «Eintritte und Kassaoeffnung» — ersetzt durch die Preiszeilen
		'food_image_id',    // Scan der Speisekarte — ersetzt durch die Seite «Speisekarte»
		'info_image_label', // die Beschriftung dieser zwei Scans
		'hero_image_id',    // hat nie eine Vorlage gelesen
		'gallery_ids',      // desgleichen
	);

	/**
	 * Die zwei Karten: was aus der Kueche kommt und was aus dem Keller.
	 * Beides steht auf derselben Seite, aber in eigenen Spalten — im
	 * Programmheft ist es auch nicht durchmischt.
	 */
	const MENU_KINDS = array( 'speisen', 'getraenke' );

	/**
	 * Standardwerte. Enthält die echten Spieldaten 2026, damit die Seite direkt
	 * nach der Aktivierung etwas Sinnvolles zeigt.
	 */
	public static function defaults() {
		return array(
			'schema_version' => self::SCHEMA_VERSION,

			// ---- Phase ----------------------------------------------------
			'phase_mode'     => 'auto',            // auto | manual
			'phase_manual'   => 'vorbereitung',
			'phase_schedule' => array(
				'vorverkauf_start' => '2026-09-15 09:00',
				'spielzeit_start'  => '2026-11-14 00:00', // Premierentag
				'danke_start'      => '2026-11-29 00:00', // Tag nach der Derniere
			),

			// ---- Ticketing ------------------------------------------------
			'ticket_sale_start' => '2026-09-15 09:00',
			'ticket_cta_mode'   => 'reservieren',  // reservieren | kaufen
			'ticket_url'        => '',
			'ticket_note'       => 'An der Abendkasse: Bargeld, Karte oder Twint.',

			// ---- Stück ----------------------------------------------------
			'season_year'   => 2026,
			'next_year'     => 2027,
			'founded_year'  => 1937,
			'play_title'    => "s'Jubiläum",
			'play_author'   => 'Dieter Gygli',
			'play_genre'    => 'Lustspiel in 3 Akten',
			'play_director' => 'Christine Jauner',
			'play_synopsis' => "Die Rollen sind fast alle verteilt und die Proben laufen — wir sind gespannt, was uns diese Komödie alles zu bieten hat. Mehr möchten wir hier noch nicht verraten.\n\nMit Christine Jauner konnten wir eine erfahrene, seit 15 Jahren tätige Theaterpädagogin als Regisseurin engagieren. Sie betreibt ein eigenes Kinder- und Jugendtheater und freut sich, mit uns das Theater 2026 zu erarbeiten.",

			// ---- Ort & Ablauf ---------------------------------------------
			'venue_name'     => 'Mehrzweckhalle Auw',
			'venue_maps_url' => '',
			'doors_time'     => '18.30',
			'food_from'      => '18.45',
			'food_to'        => '19.30',
			'food_note'      => 'Unser Küchenteam ist bei den Abendaufführungen bereit, dich kulinarisch zu verwöhnen. Am Sonntag servieren wir nach der Vorstellung.',
			'tombola_note'   => 'Auch dieses Jahr gibt es unsere beliebte Tombola. Beim Loskauf wünschen wir dir das nötige Glück zum Soforttreffer — tolle Preise warten auf dich.',
			// Das einzige Bild, das hier bleibt: für die Tombola gibt es keine
			// erfasste Entsprechung, ein Foto der Preise ist selbst der Inhalt.
			// Eintritte und Speisekarte lagen einmal genauso als Scan da —
			// siehe RETIRED.
			'tombola_image_id' => 0,

			// ---- Detaillierte Informationen ----------------------------------
			// Eintrittspreise als echte Zeilen statt als Bild — Suchmaschinen,
			// Vorlesegeräte und das Handy können damit etwas anfangen, ein Scan
			// nicht. Beträge leer lassen blendet die Zeile aus.
			'details_title' => 'Gut zu wissen',
			'details_intro' => '',
			// Aus dem Programmheft 2025. Mehrere Zeilen mit derselben Gruppe
			// werden zusammengefasst — die Gruppe steht dann nur einmal da.
			'prices'        => array(
				array( 'group' => 'Vorpremiere', 'label' => 'Kinder bis 16 Jahre', 'amount' => 'Fr. 2.–', 'note' => '' ),
				array( 'group' => 'Vorpremiere', 'label' => 'Erwachsene', 'amount' => 'Fr. 10.–', 'note' => 'am Samstagnachmittag' ),
				array( 'group' => 'Sonntag', 'label' => 'Kinder bis 16 Jahre', 'amount' => 'Fr. 12.–', 'note' => '' ),
				array( 'group' => 'Sonntag', 'label' => 'Erwachsene', 'amount' => 'Fr. 20.–', 'note' => '' ),
				// Ohne Gruppe: eine Aufführungsart mit nur einem Preis braucht
				// keine Zwischenüberschrift, unter der eine einzige Zeile steht.
				array( 'group' => '', 'label' => 'Abendaufführung', 'amount' => 'Fr. 20.–', 'note' => '' ),
				array( 'group' => '', 'label' => 'Premiere', 'amount' => 'Fr. 60.–', 'note' => 'inkl. 4-Gang-Menü' ),
			),
			'price_hint'    => '',

			// Kassaöffnung — eigene Liste, weil das keine Beträge sind.
			'openings'      => array(
				array( 'label' => 'Vorpremiere', 'time' => 'ab 13.00 Uhr' ),
				array( 'label' => 'Abendaufführungen', 'time' => 'ab 18.30 Uhr' ),
				array( 'label' => 'Sonntag', 'time' => 'ab 13.00 Uhr' ),
			),

			// Der Saalplan ändert sich nie. Ohne Bild zeichnet ihn das Plugin
			// selbst — die Halle steht in partial-saalplan.php. Ein Bild hier
			// ersetzt den gezeichneten Plan, für den Fall, dass umgebaut wird.
			'seatmap_image_id' => 0,
			'seatmap_title'    => 'Platz-Layout',
			'seatmap_note'     => '',
			// Farbe der Bühnenleiste. Leer = Theaterrot. Das Programmheft
			// wechselt die Farbe pro Jahrgang; wer mitziehen will, trägt sie
			// hier ein.
			'seatmap_color'    => '',

			// Speise- und Getränkekarte, Programmheft: verlinkt statt abgetippt.
			'documents' => array(),

			// ---- Speisekarte -------------------------------------------------
			// Eigene Seite, eigener Seitentyp. Vorher lag ihr Inhalt in
			// Divi-Modulen: das Plugin sah dort nichts von seinem Shortcode,
			// gestaltete nichts, und die Seite blieb als einzige neben dem
			// Theater-Menü im alten Design stehen.
			//
			// Die Gerichte stehen deshalb als echte Zeilen hier — dieselbe
			// Entscheidung wie bei den Eintrittspreisen und aus denselben
			// Gründen: ein Scan lässt sich nicht vorlesen, nicht durchsuchen
			// und auf dem Handy nicht entziffern.
			'menu_title'  => 'Speisekarte',
			'menu_kicker' => 'Aus unserer Küche',
			'menu_intro'  => 'Die warme Küche gibt es nur vor der Aufführung. Sandwiches sind immer erhältlich, der kleine Blattsalat auch nach der Aufführung — solange es hat.',
			// Die Lieferanten stehen auf der Karte, und sie gehören dazu: es
			// sind dieselben Betriebe aus dem Dorf, die auch die Tombola
			// beliefern und das Theater unterstützen.
			'menu_note'   => 'Fleisch von der Metzgerei Huwyler, Beinwil. Brot und Butterpastetli von Emma’s Bäckerei, Sins. Süsse Backwaren von Kreyenbühl, Muri und Auw. Wein und Getränke von der Staubli-Banz Getränke AG, Muri/Sins.',

			// Die echte Karte 2025 — «Speise und Getränkekarte_2025.pdf»,
			// Stand 16.10.2025. Damit die Seite direkt nach dem Einbau
			// vollständig ist; für 2026 gehen Küchenteam und Preise noch
			// einmal darüber.
			//
			// Zwei Karten in einer Liste: `kind` entscheidet, in welcher
			// Spalte die Zeile landet. `group` ist der Gang beziehungsweise
			// die Rubrik der Getränkekarte.
			//
			// Wo ein Getränk in zwei Grössen ausgeschenkt wird, stehen beide
			// Masse in der Beschreibung und beide Beträge in derselben
			// Reihenfolge im Preis — eine Zeile pro Grösse würde den
			// Weinnamen doppelt hinschreiben.
			'menu'        => array(
				// --- Speisen ---
				array( 'kind' => 'speisen', 'group' => 'Warme Küche', 'label' => 'Kleiner Blattsalat mit Brot', 'note' => 'auch nach der Aufführung, solange es hat', 'amount' => 'Fr. 6.–' ),
				array( 'kind' => 'speisen', 'group' => 'Warme Küche', 'label' => 'Pommes frites mit Ketchup', 'note' => '', 'amount' => 'Fr. 6.–' ),
				array( 'kind' => 'speisen', 'group' => 'Warme Küche', 'label' => '1 Pastetli mit hausgemachter Füllung', 'note' => 'mit Pommes frites und Mischgemüse', 'amount' => 'Fr. 16.–' ),
				array( 'kind' => 'speisen', 'group' => 'Warme Küche', 'label' => 'Plus 1 Pastetli auf gleichem Teller', 'note' => '', 'amount' => 'Fr. 4.–' ),
				array( 'kind' => 'speisen', 'group' => 'Warme Küche', 'label' => 'Schweinshalsbraten', 'note' => 'mit Pommes frites und Mischgemüse', 'amount' => 'Fr. 20.–' ),
				array( 'kind' => 'speisen', 'group' => 'Für den kleinen Hunger', 'label' => 'Sandwich mit Käse oder Schinken', 'note' => 'immer erhältlich', 'amount' => 'Fr. 7.50' ),
				array( 'kind' => 'speisen', 'group' => 'Für den kleinen Hunger', 'label' => 'Sandwich mit Braten', 'note' => 'wenns no hed', 'amount' => 'Fr. 9.50' ),
				array( 'kind' => 'speisen', 'group' => 'Dessert', 'label' => 'Huus-Nussgipfel', 'note' => '', 'amount' => 'Fr. 4.–' ),
				array( 'kind' => 'speisen', 'group' => 'Dessert', 'label' => 'Linzerschnitte', 'note' => '', 'amount' => 'Fr. 4.50' ),
				array( 'kind' => 'speisen', 'group' => 'Dessert', 'label' => 'Cremeschnitte', 'note' => '', 'amount' => 'Fr. 5.50' ),
				array( 'kind' => 'speisen', 'group' => 'Dessert', 'label' => 'Torte', 'note' => 'Schwarzwälder oder Himbeer-Joghurt', 'amount' => 'Fr. 7.50' ),

				// --- Getränke ---
				array( 'kind' => 'getraenke', 'group' => 'Kaffee und Diverses', 'label' => 'Kaffee Crème oder Espresso', 'note' => '', 'amount' => 'Fr. 3.50' ),
				array( 'kind' => 'getraenke', 'group' => 'Kaffee und Diverses', 'label' => 'Kafi-Schnaps', 'note' => 'Zwetschge, Chrüter oder Träsch', 'amount' => 'Fr. 6.–' ),
				array( 'kind' => 'getraenke', 'group' => 'Kaffee und Diverses', 'label' => 'Theater-Kafi', 'note' => 'Caramel Vodka mit Schlagrahm', 'amount' => 'Fr. 7.50' ),
				array( 'kind' => 'getraenke', 'group' => 'Kaffee und Diverses', 'label' => 'Kafi Baileys', 'note' => 'mit Schlagrahm', 'amount' => 'Fr. 7.50' ),
				array( 'kind' => 'getraenke', 'group' => 'Kaffee und Diverses', 'label' => 'Kafi Amaretto', 'note' => 'mit Schlagrahm', 'amount' => 'Fr. 7.50' ),
				array( 'kind' => 'getraenke', 'group' => 'Kaffee und Diverses', 'label' => 'Tee', 'note' => 'Minze, Früchte, Hagebutte oder Verbena', 'amount' => 'Fr. 3.50' ),
				array( 'kind' => 'getraenke', 'group' => 'Rotwein', 'label' => 'Pinot noir du Valais', 'note' => 'F.J. Mathier, CH · 5 dl', 'amount' => 'Fr. 18.–' ),
				array( 'kind' => 'getraenke', 'group' => 'Rotwein', 'label' => 'Primitivo Puglia Festivo', 'note' => 'IT · 5 dl', 'amount' => 'Fr. 18.–' ),
				array( 'kind' => 'getraenke', 'group' => 'Rotwein', 'label' => 'Cabernet Jura Aargau', 'note' => 'Bruno Hartmann, Remigen · 1 dl / 7 dl', 'amount' => 'Fr. 7.– / 36.–' ),
				array( 'kind' => 'getraenke', 'group' => 'Rotwein', 'label' => 'Valpolicella Ripasso Terre di Verona', 'note' => 'IT · 7 dl', 'amount' => 'Fr. 33.–' ),
				array( 'kind' => 'getraenke', 'group' => 'Weisswein', 'label' => 'St. Saphorin blanc Chappuis', 'note' => 'CH · 5 dl', 'amount' => 'Fr. 20.–' ),
				array( 'kind' => 'getraenke', 'group' => 'Weisswein', 'label' => 'Sauvignon blanc Aargau', 'note' => 'Bruno Hartmann, Remigen · 1 dl / 7 dl', 'amount' => 'Fr. 6.– / 34.–' ),
				array( 'kind' => 'getraenke', 'group' => 'Mineral, Bier und Most', 'label' => 'Mineral', 'note' => 'mit oder ohne Kohlensäure · 3 dl / 1 Liter', 'amount' => 'Fr. 4.– / 8.–' ),
				array( 'kind' => 'getraenke', 'group' => 'Mineral, Bier und Most', 'label' => 'Coca-Cola, Zero, Citro, Apfelschorle, Rivella rot', 'note' => '3 dl', 'amount' => 'Fr. 4.50' ),
				array( 'kind' => 'getraenke', 'group' => 'Mineral, Bier und Most', 'label' => 'Feldschlösschen', 'note' => 'mit und ohne Alkohol · 3 dl', 'amount' => 'Fr. 4.50' ),
				array( 'kind' => 'getraenke', 'group' => 'Mineral, Bier und Most', 'label' => 'Feldschlösschen', 'note' => '5 dl', 'amount' => 'Fr. 5.50' ),
				array( 'kind' => 'getraenke', 'group' => 'Mineral, Bier und Most', 'label' => 'Möhl, klarer Most', 'note' => 'mit und ohne Alkohol · 5 dl', 'amount' => 'Fr. 5.50' ),
			),

			// ---- Updates -----------------------------------------------------
			// Die Seite «Updates» hatte dasselbe Problem und bekommt dieselbe
			// Lösung: Einträge als Datensätze statt als Absätze im Builder.
			// Dadurch stehen sie in der richtigen Reihenfolge, tragen ein
			// maschinenlesbares Datum und können gar nicht erst unsortiert
			// verrutschen.
			'updates_title'  => 'Updates',
			'updates_kicker' => 'Neues vom Theater',
			'updates_intro'  => '',
			'updates'        => array(),

			// ---- Spieldaten ------------------------------------------------
			'dates' => array(
				array( 'date' => '2026-11-14', 'time' => '20.00', 'label' => 'Premiere', 'note' => 'Sponsoren-Abend', 'status' => 'offen', 'ticket_url' => '' ),
				array( 'date' => '2026-11-18', 'time' => '20.00', 'label' => '', 'note' => '', 'status' => 'offen', 'ticket_url' => '' ),
				array( 'date' => '2026-11-21', 'time' => '20.00', 'label' => '', 'note' => '', 'status' => 'offen', 'ticket_url' => '' ),
				array( 'date' => '2026-11-22', 'time' => '14.00', 'label' => '', 'note' => '', 'status' => 'offen', 'ticket_url' => '' ),
				array( 'date' => '2026-11-26', 'time' => '20.00', 'label' => '', 'note' => '', 'status' => 'offen', 'ticket_url' => '' ),
				array( 'date' => '2026-11-28', 'time' => '20.00', 'label' => 'Derniere', 'note' => '', 'status' => 'offen', 'ticket_url' => '' ),
			),

			// ---- Navigation --------------------------------------------------
			// 0 = aus. Sobald ein bestehendes WordPress-Menü gewählt ist,
			// rendert das Plugin es im Theater-Look — inklusive Untermenüs.
			// Bewusst KEIN eigener Menü-Bestand: die Theater-Navigation wird
			// weiterhin unter Design → Menüs gepflegt, wie gewohnt.
			'nav_menu_id' => 0,
			'nav_sticky'  => true,
			// Ganz oben kleben statt unter den Leisten des Themes.
			//
			// Normalerweise haelt die Theater-Leiste Abstand: ueber ihr haengen
			// die graue Vereinsleiste und Divis Kopfzeile, beide fest verankert
			// und mit einem z-index um 100000. Ohne Abstand verschwaende sie
			// beim Scrollen dahinter.
			//
			// Wer die Theater-Leiste lieber ganz oben haben moechte, schaltet
			// das hier ein: dann scrollen die Leisten des Themes weg und die
			// Theater-Leiste klebt an der Oberkante. Aus bleibt die Vorgabe —
			// eine Leiste, die eine andere verdeckt, waere schlimmer als eine,
			// die etwas tiefer haengt.
			'nav_sticky_top' => false,
			// Blendet die Navigation des Themes auf den Theater-Seiten aus.
			// Sinnvoll NUR, wenn oben ein Menue gewaehlt ist — sonst bliebe die
			// Seite ohne jede Navigation zurueck. Genau das prueft der Sanitizer.
			'nav_replace' => false,

			// Uebertraegt Schrift, Farbe und Papierhintergrund auf den uebrigen
			// Seiteninhalt. Fuer Seiten, deren Inhalt nicht im Shortcode steckt
			// — etwa im Divi-Theme-Builder gebaute. Standard AN: wer das Plugin
			// einsetzt, will die Seite im Theater-Design, nicht halb.
			'content_typo' => true,

			// Die Berichte — normale Beitraege (`post`) unter
			// /theater/2026/06/2738/. stvauw.ch/theater ist eine eigene
			// Website im Netzwerk; was hier als Beitrag liegt, gehoert zum
			// Theater, es gibt keinen fremden Vereinsblog daneben. Trotzdem
			// erkannte das Plugin sie nicht: kein Shortcode im Inhalt, nicht
			// die Hauptseite, nicht der Portfolio-Typ. Folge am 26.07.2026:
			// Navigationsleiste im Theater-Look, darunter aber 794 von 1270
			// Pixeln Inhalt neben «Neueste Beiträge», «Archiv», «Kategorien»,
			// «Meta» — und Open Sans in Grau.
			'post_singles' => true,

			// ---- Rückblick ----------------------------------------------------
			// `auto` liest den vorhandenen Bestand (auf stvauw.ch das
			// Divi-Portfolio: ein `project`-Beitrag pro Jahrgang mit Plakat als
			// Beitragsbild). `manual` nutzt die Liste unten.
			'rueckblick_source'    => 'auto',
			'rueckblick_post_type' => 'project',
			'rueckblick_count'     => 8,
			'rueckblick_archive_url' => '',
			// Bildformat der Kacheln. Im Divi-Portfolio von stvauw.ch liegen
			// Szenenfotos im Querformat, keine hochkanten Plakate — deshalb
			// «quer» als Standard. «auto» laesst jedes Bild sein eigenes
			// Format behalten; das Raster wird dann ungleich hoch.
			'rueckblick_ratio'     => 'quer',   // quer | hoch | auto
			// Die Jahrgangsseiten selbst — bei Divi die Einzelansicht des
			// Portfolios, etwa /theater/project/2694/. Sie gehoeren zum
			// Theaterteil der Website, tragen aber keinen Shortcode: ihr
			// Aufbau kommt aus einer Theme-Builder-Vorlage. Ohne diesen
			// Schalter erkennt das Plugin sie nicht und sie bleiben als
			// einzige im alten Design zurueck.
			'rueckblick_singles'   => true,

			// ---- Bildergruppen für die Unterseiten ---------------------------
			// Vorbelegt mit dem, was auf stvauw.ch existiert. Die Bilder wählt
			// die Admin-Person aus der Mediathek; die Namen stehen schon drin.
			'groups' => array(
				array( 'key' => 'schauspieler', 'title' => 'Die Schauspieler', 'kicker' => 'Auf der Bühne', 'intro' => '', 'type' => 'personen', 'columns' => 4, 'dark' => false, 'lead_image_id' => 0, 'lead_caption' => '', 'lead_layout' => 'neben', 'sort' => 'manuell', 'labels' => array(), 'wide' => array(), 'ids' => array() ),
				array( 'key' => 'kulissen', 'title' => 'Hinter den Kulissen', 'kicker' => 'Ohne sie geht nichts', 'intro' => '', 'type' => 'personen', 'columns' => 4, 'dark' => false, 'lead_image_id' => 0, 'lead_caption' => '', 'lead_layout' => 'neben', 'sort' => 'manuell', 'labels' => array(), 'wide' => array(), 'ids' => array() ),
				array( 'key' => 'ok', 'title' => 'Organisation Komitee', 'kicker' => 'Das OK-Team', 'intro' => '', 'type' => 'bilder', 'columns' => 2, 'dark' => false, 'lead_image_id' => 0, 'lead_caption' => '', 'lead_layout' => 'neben', 'sort' => 'manuell', 'labels' => array(), 'wide' => array(), 'ids' => array() ),
				array( 'key' => 'sponsoren', 'title' => 'Sponsoren', 'kicker' => 'Ein herzliches Dankeschön', 'intro' => 'Ohne die Unterstützung dieser Firmen und Betriebe gäbe es unser Theater nicht. Herzlichen Dank!', 'type' => 'logos', 'columns' => 5, 'dark' => false, 'lead_image_id' => 0, 'lead_caption' => '', 'lead_layout' => 'neben', 'sort' => 'alpha', 'labels' => array(), 'wide' => array(), 'ids' => array() ),
			),

			// ---- Bilder ------------------------------------------------------
			// Zwei Bilder, zwei Orte: das Logo steht im dunklen Kopfbereich und
			// wird deshalb unter «Darstellung» gewählt, das Ensemblebild beim
			// Stück und deshalb unter «Stück & Texte».
			'stueck_image_id' => 0,
			'logo_id'         => 0,
			'rueckblick'      => array(
				array( 'year' => '2025', 'title' => 'Karriere', 'image_id' => 0, 'url' => '' ),
				array( 'year' => '2024', 'title' => '', 'image_id' => 0, 'url' => '' ),
				array( 'year' => '2023', 'title' => '', 'image_id' => 0, 'url' => '' ),
				array( 'year' => '2022', 'title' => '', 'image_id' => 0, 'url' => '' ),
			),

			// ---- Kontakt ---------------------------------------------------
			'contact_email' => 'theaterauw@bluewin.ch',

			// ---- Darstellung ------------------------------------------------
			'page_id'     => 0,           // Theater-Seite; fängt Page-Builder ab

			// Seitenrahmen aufräumen — siehe class-tha-pagechrome.php.
			//
			// Standardmässig AN. Eine Theater-Seite ist eine Plakat-Seite; eine
			// Blog-Seitenleiste daneben und der Seitentitel darüber sind dort
			// praktisch nie gewollt. Wirkt ohnehin nur auf Seiten mit
			// Theater-Inhalt, der Rest der Website bleibt unberührt.
			'hide_page_title'    => true,
			'hide_sidebar'       => true,
			'content_full_width' => true,

			'width_mode'  => 'auto',      // auto | viewport | contained
			'isolation'   => 'normal',    // normal | stark
			'font_source' => 'google',    // google | theme
			'sticky_cta'  => true,
		);
	}

	/**
	 * Gespeicherte Werte, immer über die Defaults gelegt — ein Teil-Save kann
	 * so nie Schlüssel verlieren.
	 */
	private static $cache = null;

	public static function get() {
		if ( null !== self::$cache ) {
			return self::$cache;
		}
		$stored = get_option( self::OPTION, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		$cache = self::strip_retired( wp_parse_args( $stored, self::defaults() ) );

		// Auch beim Lesen sortieren, nicht nur beim Speichern: eine Liste, die
		// vor dieser Version gespeichert wurde, stand bei zwei Vorstellungen am
		// selben Tag sonst in Eingabe- statt in Spielreihenfolge.
		if ( is_array( $cache['dates'] ) ) {
			$cache['dates'] = self::sort_dates( $cache['dates'] );
		}

		// Verschachteltes Array separat mergen — wp_parse_args geht nicht tief.
		$cache['phase_schedule'] = wp_parse_args(
			isset( $stored['phase_schedule'] ) && is_array( $stored['phase_schedule'] ) ? $stored['phase_schedule'] : array(),
			self::defaults()['phase_schedule']
		);
		self::$cache = $cache;
		return $cache;
	}

	/**
	 * Abgeschaffte Felder aus einem Datensatz werfen.
	 *
	 * Eine Installation, die es lange gibt, traegt sie noch in der Datenbank.
	 * Sie hier zu entfernen ist billiger als jede Vorlage daran zu erinnern,
	 * dass es sie einmal gab — und verhindert, dass ein alter Wert nach einem
	 * spaeteren Umbau unbemerkt wieder auftaucht.
	 */
	private static function strip_retired( array $werte ) {
		foreach ( self::RETIRED as $key ) {
			unset( $werte[ $key ] );
		}
		return $werte;
	}

	public static function flush_cache() {
		// get() cached statisch; nach dem Speichern einmal neu laden.
		self::$cache = null;
		wp_cache_delete( self::OPTION, 'options' );
	}

	public static function register() {
		register_setting(
			self::GROUP,
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	/**
	 * Sanitizing. Whitelist-first: alles, was nicht erkannt wird, fällt auf den
	 * Standardwert zurück statt roh gespeichert zu werden.
	 */
	public static function sanitize( $input ) {
		$d   = self::defaults();
		$old = get_option( self::OPTION, array() );
		if ( ! is_array( $old ) ) {
			$old = array();
		}
		$out = wp_parse_args( $old, $d );

		if ( ! is_array( $input ) ) {
			return $out;
		}

		// Die Einstellungsseite hat Tabs, aber alle schreiben in DIESELBE Option.
		// Ein Formular überträgt nur die Felder seines eigenen Tabs — deshalb
		// darf hier ausschliesslich angefasst werden, was auch wirklich
		// mitgeschickt wurde. Sonst löscht ein Speichern unter «Stück & Texte»
		// die Ticketleo-URL und den ganzen Phasen-Fahrplan.
		$has = static function ( $key ) use ( $input ) {
			return array_key_exists( $key, $input );
		};

		// --- Phase ---------------------------------------------------------
		if ( $has( 'phase_mode' ) ) {
			$out['phase_mode'] = ( 'manual' === $input['phase_mode'] ) ? 'manual' : 'auto';
		}
		if ( $has( 'phase_manual' ) ) {
			$out['phase_manual'] = self::pick( $input, 'phase_manual', self::PHASES, $out['phase_manual'] );
		}

		if ( isset( $input['phase_schedule'] ) && is_array( $input['phase_schedule'] ) ) {
			foreach ( array( 'vorverkauf_start', 'spielzeit_start', 'danke_start' ) as $key ) {
				if ( array_key_exists( $key, $input['phase_schedule'] ) ) {
					$out['phase_schedule'][ $key ] = self::clean_datetime(
						$input['phase_schedule'][ $key ],
						$out['phase_schedule'][ $key ]
					);
				}
			}
		}

		// --- Ticketing -----------------------------------------------------
		if ( $has( 'ticket_sale_start' ) ) {
			$out['ticket_sale_start'] = self::clean_datetime( $input['ticket_sale_start'], $out['ticket_sale_start'] );
		}
		if ( $has( 'ticket_cta_mode' ) ) {
			$out['ticket_cta_mode'] = self::pick( $input, 'ticket_cta_mode', array( 'reservieren', 'kaufen' ), $out['ticket_cta_mode'] );
		}
		if ( $has( 'ticket_url' ) ) {
			$out['ticket_url'] = self::clean_url( $input['ticket_url'] );
		}
		if ( $has( 'ticket_note' ) ) {
			$out['ticket_note'] = self::clean_text( $input['ticket_note'] );
		}

		// --- Stück ---------------------------------------------------------
		foreach ( array( 'play_title', 'play_author', 'play_genre', 'play_director', 'venue_name', 'food_note', 'tombola_note', 'contact_email' ) as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$out[ $key ] = self::clean_text( $input[ $key ] );
			}
		}
		if ( isset( $input['play_synopsis'] ) ) {
			$out['play_synopsis'] = self::swiss( wp_kses_post( trim( (string) $input['play_synopsis'] ) ) );
		}
		if ( isset( $input['contact_email'] ) ) {
			$out['contact_email'] = sanitize_email( $input['contact_email'] );
		}
		$out['venue_maps_url'] = self::clean_url( isset( $input['venue_maps_url'] ) ? $input['venue_maps_url'] : '' );

		foreach ( array( 'season_year', 'next_year', 'founded_year' ) as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$year        = absint( $input[ $key ] );
				$out[ $key ] = ( $year >= 1900 && $year <= 2999 ) ? $year : $d[ $key ];
			}
		}
		foreach ( array( 'doors_time', 'food_from', 'food_to' ) as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$out[ $key ] = self::clean_time( $input[ $key ], $d[ $key ] );
			}
		}

		// --- Spieldaten ----------------------------------------------------
		if ( isset( $input['dates'] ) && is_array( $input['dates'] ) ) {
			$dates = array();
			foreach ( $input['dates'] as $row ) {
				if ( ! is_array( $row ) || empty( $row['date'] ) ) {
					continue;
				}
				$date = self::clean_date( $row['date'] );
				if ( '' === $date ) {
					continue;
				}
				$dates[] = array(
					'date'       => $date,
					'time'       => self::clean_time( isset( $row['time'] ) ? $row['time'] : '', '20.00' ),
					'label'      => self::clean_text( isset( $row['label'] ) ? $row['label'] : '' ),
					'note'       => self::clean_text( isset( $row['note'] ) ? $row['note'] : '' ),
					'status'     => in_array( isset( $row['status'] ) ? $row['status'] : '', self::STATUSES, true ) ? $row['status'] : 'offen',
					'ticket_url' => self::clean_url( isset( $row['ticket_url'] ) ? $row['ticket_url'] : '' ),
				);
			}
			$out['dates'] = self::sort_dates( $dates );
		}

		// --- Navigation ------------------------------------------------------
		if ( $has( 'nav_menu_id' ) ) {
			$out['nav_menu_id'] = absint( $input['nav_menu_id'] );
		}
		if ( $has( 'nav_sticky_present' ) ) {
			$out['nav_sticky'] = ! empty( $input['nav_sticky'] );
		}
		if ( $has( 'nav_sticky_top_present' ) ) {
			// Ohne Mitscrollen ist «ganz oben» gegenstandslos.
			$out['nav_sticky_top'] = ! empty( $input['nav_sticky_top'] ) && ! empty( $out['nav_sticky'] );
		}
		if ( $has( 'nav_replace_present' ) ) {
			$out['nav_replace'] = ! empty( $input['nav_replace'] );
		}
		if ( $has( 'content_typo_present' ) ) {
			$out['content_typo'] = ! empty( $input['content_typo'] );
		}

		if ( $has( 'post_singles_present' ) ) {
			$out['post_singles'] = ! empty( $input['post_singles'] );
		}

		// Sicherheitsnetz: die Navigation des Themes darf nur verschwinden,
		// wenn eine eigene an ihre Stelle tritt. Ohne gewähltes Menü stünde
		// die Theater-Seite sonst ohne jede Navigation da — und das fiele
		// erst auf, wenn jemand von der Startseite nicht mehr wegkommt.
		if ( empty( $out['nav_menu_id'] ) ) {
			$out['nav_replace'] = false;
		}

		// --- Rückblick -----------------------------------------------------
		if ( $has( 'rueckblick_source' ) ) {
			$out['rueckblick_source'] = self::pick( $input, 'rueckblick_source', array( 'auto', 'manual' ), $out['rueckblick_source'] );
		}
		if ( $has( 'rueckblick_post_type' ) ) {
			$out['rueckblick_post_type'] = sanitize_key( $input['rueckblick_post_type'] );
		}
		if ( $has( 'rueckblick_count' ) ) {
			$out['rueckblick_count'] = max( 1, min( 40, absint( $input['rueckblick_count'] ) ) );
		}
		if ( $has( 'rueckblick_singles_present' ) ) {
			$out['rueckblick_singles'] = ! empty( $input['rueckblick_singles'] );
		}
		if ( $has( 'rueckblick_ratio' ) ) {
			$out['rueckblick_ratio'] = self::pick( $input, 'rueckblick_ratio', array( 'quer', 'hoch', 'auto' ), $out['rueckblick_ratio'] );
		}
		if ( $has( 'rueckblick_archive_url' ) ) {
			$out['rueckblick_archive_url'] = self::clean_url( $input['rueckblick_archive_url'] );
		}

		if ( isset( $input['rueckblick'] ) && is_array( $input['rueckblick'] ) ) {
			$rows = array();
			foreach ( $input['rueckblick'] as $row ) {
				if ( ! is_array( $row ) || empty( $row['year'] ) ) {
					continue;
				}
				$rows[] = array(
					'year'     => self::clean_text( $row['year'] ),
					'title'    => self::clean_text( isset( $row['title'] ) ? $row['title'] : '' ),
					'image_id' => isset( $row['image_id'] ) ? absint( $row['image_id'] ) : 0,
					'url'      => self::clean_url( isset( $row['url'] ) ? $row['url'] : '' ),
				);
			}
			$out['rueckblick'] = $rows;
		}

		// --- Bildergruppen ---------------------------------------------------
		if ( isset( $input['groups'] ) && is_array( $input['groups'] ) ) {
			$groups = array();
			foreach ( $input['groups'] as $row ) {
				if ( ! is_array( $row ) || empty( $row['key'] ) ) {
					continue;
				}
				$ids = isset( $row['ids'] ) ? $row['ids'] : array();
				if ( ! is_array( $ids ) ) {
					$ids = array_filter( explode( ',', (string) $ids ) );
				}
				$groups[] = array(
					'key'     => sanitize_key( $row['key'] ),
					'title'   => self::clean_text( isset( $row['title'] ) ? $row['title'] : '' ),
					'kicker'  => self::clean_text( isset( $row['kicker'] ) ? $row['kicker'] : '' ),
					'intro'   => isset( $row['intro'] ) ? self::swiss( wp_kses_post( trim( (string) $row['intro'] ) ) ) : '',
					'type'    => in_array( isset( $row['type'] ) ? $row['type'] : '', Tha_Groups::TYPES, true ) ? $row['type'] : 'personen',
					'columns' => max( 1, min( 8, isset( $row['columns'] ) ? absint( $row['columns'] ) : 4 ) ),
					'dark'    => ! empty( $row['dark'] ),
					// Gruppenbild: ein Ensemble- oder Teamfoto über dem Raster.
					'lead_image_id' => isset( $row['lead_image_id'] ) ? absint( $row['lead_image_id'] ) : 0,
					'lead_caption'  => self::clean_text( isset( $row['lead_caption'] ) ? $row['lead_caption'] : '' ),
					'lead_layout'   => in_array( isset( $row['lead_layout'] ) ? $row['lead_layout'] : '', array( 'neben', 'oben' ), true ) ? $row['lead_layout'] : 'neben',
					// Reihenfolge: von Hand gelegt oder alphabetisch. Bei den
					// Sponsoren ist alphabetisch das Fairste — keine Firma steht
					// vorn, weil sie zufällig zuerst angeklickt wurde.
					'sort'          => in_array( isset( $row['sort'] ) ? $row['sort'] : '', array( 'manuell', 'alpha' ), true ) ? $row['sort'] : 'manuell',
					// Reihenfolge der Auswahl beibehalten — sie ist redaktionell.
					'ids'     => array_values( array_filter( array_map( 'absint', $ids ) ) ),
					// Beschriftungen, die von der Mediathek abweichen sollen.
					// Schlüssel ist die Bild-ID, leer heisst «aus dem Bild nehmen».
					'labels'  => self::clean_labels( isset( $row['labels'] ) ? $row['labels'] : array() ),
					// Bilder, die doppelt so breit stehen sollen — Gruppenaufnahmen
					// zwischen Einzelporträts, wie das Tombolateam.
					'wide'    => isset( $row['wide'] ) && is_array( $row['wide'] )
						? array_values( array_filter( array_map( 'absint', array_keys( array_filter( $row['wide'] ) ) ) ) )
						: array(),
				);
			}
			$out['groups'] = $groups;
		}

		// --- Detaillierte Informationen ---------------------------------------
		foreach ( array( 'details_title', 'seatmap_title', 'price_hint' ) as $key ) {
			if ( $has( $key ) ) {
				$out[ $key ] = self::clean_text( $input[ $key ] );
			}
		}
		foreach ( array( 'details_intro', 'seatmap_note' ) as $key ) {
			if ( $has( $key ) ) {
				$out[ $key ] = self::swiss( wp_kses_post( trim( (string) $input[ $key ] ) ) );
			}
		}
		if ( $has( 'seatmap_image_id' ) ) {
			$out['seatmap_image_id'] = self::clean_attachment( $input['seatmap_image_id'] );
		}
		// Unsinn im Farbfeld wird zu leer — dann nimmt der Plan das Theaterrot.
		if ( $has( 'seatmap_color' ) ) {
			$farbe = sanitize_hex_color( trim( (string) $input['seatmap_color'] ) );
			$out['seatmap_color'] = $farbe ? $farbe : '';
		}

		if ( isset( $input['prices'] ) && is_array( $input['prices'] ) ) {
			$rows = array();
			foreach ( $input['prices'] as $row ) {
				if ( ! is_array( $row ) || '' === trim( (string) ( isset( $row['label'] ) ? $row['label'] : '' ) ) ) {
					continue;
				}
				$rows[] = array(
					'group'  => self::clean_text( isset( $row['group'] ) ? $row['group'] : '' ),
					'label'  => self::clean_text( $row['label'] ),
					'amount' => self::clean_text( isset( $row['amount'] ) ? $row['amount'] : '' ),
					'note'   => self::clean_text( isset( $row['note'] ) ? $row['note'] : '' ),
				);
			}
			$out['prices'] = $rows;
		}

		if ( isset( $input['openings'] ) && is_array( $input['openings'] ) ) {
			$rows = array();
			foreach ( $input['openings'] as $row ) {
				if ( ! is_array( $row ) || '' === trim( (string) ( isset( $row['label'] ) ? $row['label'] : '' ) ) ) {
					continue;
				}
				$rows[] = array(
					'label' => self::clean_text( $row['label'] ),
					'time'  => self::clean_text( isset( $row['time'] ) ? $row['time'] : '' ),
				);
			}
			$out['openings'] = $rows;
		}

		if ( isset( $input['documents'] ) && is_array( $input['documents'] ) ) {
			$docs = array();
			foreach ( $input['documents'] as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$label = self::clean_text( isset( $row['label'] ) ? $row['label'] : '' );
				$url   = self::clean_url( isset( $row['url'] ) ? $row['url'] : '' );
				$file  = isset( $row['file_id'] ) ? absint( $row['file_id'] ) : 0;
				if ( '' === $label || ( '' === $url && ! $file ) ) {
					continue;
				}
				$docs[] = array(
					'label'   => $label,
					'url'     => $url,
					'file_id' => $file,
				);
			}
			$out['documents'] = $docs;
		}

		// --- Speisekarte ------------------------------------------------------
		foreach ( array( 'menu_title', 'menu_kicker', 'menu_note', 'updates_title', 'updates_kicker' ) as $key ) {
			if ( $has( $key ) ) {
				$out[ $key ] = self::clean_text( $input[ $key ] );
			}
		}
		foreach ( array( 'menu_intro', 'updates_intro' ) as $key ) {
			if ( $has( $key ) ) {
				$out[ $key ] = self::swiss( wp_kses_post( trim( (string) $input[ $key ] ) ) );
			}
		}

		if ( isset( $input['menu'] ) && is_array( $input['menu'] ) ) {
			$rows = array();
			foreach ( $input['menu'] as $row ) {
				if ( ! is_array( $row ) || '' === trim( (string) ( isset( $row['label'] ) ? $row['label'] : '' ) ) ) {
					continue;
				}
				// Ein Gericht OHNE Preis bleibt stehen. Auf einer Speisekarte
				// ist das ein Normalfall — «Dessertbuffet» steht auch ohne
				// Betrag da. Bei den Eintrittspreisen ist es umgekehrt: dort
				// ist eine Zeile ohne Betrag eine unfertige Zeile.
				$rows[] = array(
					'kind'   => in_array( isset( $row['kind'] ) ? $row['kind'] : '', self::MENU_KINDS, true ) ? $row['kind'] : 'speisen',
					'group'  => self::clean_text( isset( $row['group'] ) ? $row['group'] : '' ),
					'label'  => self::clean_text( $row['label'] ),
					'note'   => self::clean_text( isset( $row['note'] ) ? $row['note'] : '' ),
					'amount' => self::clean_text( isset( $row['amount'] ) ? $row['amount'] : '' ),
				);
			}
			$out['menu'] = $rows;
		}

		// --- Updates ----------------------------------------------------------
		if ( isset( $input['updates'] ) && is_array( $input['updates'] ) ) {
			$rows = array();
			foreach ( $input['updates'] as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$title = self::clean_text( isset( $row['title'] ) ? $row['title'] : '' );
				$text  = isset( $row['text'] ) ? self::swiss( wp_kses_post( trim( (string) $row['text'] ) ) ) : '';

				// Ein Eintrag ohne Überschrift UND ohne Text ist eine leere
				// Zeile aus dem Formular, kein Eintrag.
				if ( '' === $title && '' === $text ) {
					continue;
				}
				$rows[] = array(
					'date'     => self::clean_date( isset( $row['date'] ) ? $row['date'] : '' ),
					'title'    => $title,
					'text'     => $text,
					'image_id' => isset( $row['image_id'] ) ? self::clean_attachment( $row['image_id'] ) : 0,
					'url'      => self::clean_url( isset( $row['url'] ) ? $row['url'] : '' ),
					'link'     => self::clean_text( isset( $row['link'] ) ? $row['link'] : '' ),
				);
			}
			$out['updates'] = self::sort_updates( $rows );
		}

		// --- Bilder ---------------------------------------------------------
		foreach ( array( 'stueck_image_id', 'logo_id', 'tombola_image_id' ) as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$out[ $key ] = self::clean_attachment( $input[ $key ] );
			}
		}

		// --- Darstellung -----------------------------------------------------
		if ( $has( 'page_id' ) ) {
			$out['page_id'] = absint( $input['page_id'] );
		}
		if ( $has( 'cleanup_present' ) ) {
			$out['hide_page_title']    = ! empty( $input['hide_page_title'] );
			$out['hide_sidebar']       = ! empty( $input['hide_sidebar'] );
			$out['content_full_width'] = ! empty( $input['content_full_width'] );
		}
		if ( $has( 'width_mode' ) ) {
			$out['width_mode'] = self::pick( $input, 'width_mode', array( 'auto', 'viewport', 'contained' ), $out['width_mode'] );
		}
		if ( $has( 'isolation' ) ) {
			$out['isolation'] = self::pick( $input, 'isolation', array( 'normal', 'stark' ), $out['isolation'] );
		}
		if ( $has( 'font_source' ) ) {
			$out['font_source'] = self::pick( $input, 'font_source', array( 'google', 'theme' ), $out['font_source'] );
		}
		// Checkbox: ein nicht angehaktes Feld wird gar nicht übertragen. Ohne
		// den Marker liesse sich «abgehakt» nicht von «anderer Tab» trennen.
		if ( $has( 'sticky_cta_present' ) ) {
			$out['sticky_cta'] = ! empty( $input['sticky_cta'] );
		}

		$out                   = self::strip_retired( $out );
		$out['schema_version'] = self::SCHEMA_VERSION;

		self::purge_caches();

		return $out;
	}

	// ---------------------------------------------------------------------
	// Einzelne Sanitizer
	// ---------------------------------------------------------------------

	private static function pick( $input, $key, $allowed, $fallback ) {
		if ( isset( $input[ $key ] ) && in_array( $input[ $key ], $allowed, true ) ) {
			return $input[ $key ];
		}
		return $fallback;
	}

	/**
	 * Schweizer Rechtschreibung erzwingen: statt Eszett immer «ss». Greift
	 * automatisch, wenn jemand Text aus einer deutschen Quelle einfügt.
	 *
	 * Die Zeichen werden über ihre Codepoints gebildet, damit sie im Quelltext
	 * gar nicht erst vorkommen — build.sh prüft genau darauf und darf hier
	 * nicht über sich selbst stolpern.
	 */
	public static function swiss( $value ) {
		return str_replace(
			array( "\u{00DF}", "\u{1E9E}" ),
			array( 'ss', 'SS' ),
			(string) $value
		);
	}

	private static function clean_text( $value ) {
		return self::swiss( sanitize_text_field( (string) $value ) );
	}

	private static function clean_url( $value ) {
		$value = esc_url_raw( trim( (string) $value ) );
		if ( '' === $value ) {
			return '';
		}
		// Nur https nach aussen — ein Ticketlink über http wäre ein Downgrade.
		if ( 0 !== strpos( $value, 'https://' ) ) {
			return '';
		}
		return $value;
	}

	private static function clean_date( $value ) {
		$value = trim( (string) $value );
		$dt    = DateTimeImmutable::createFromFormat( 'Y-m-d|', $value, tha_timezone() );
		return ( $dt instanceof DateTimeImmutable && $dt->format( 'Y-m-d' ) === $value ) ? $value : '';
	}

	private static function clean_datetime( $value, $fallback ) {
		$value = trim( (string) $value );
		if ( '' === $value ) {
			return '';
		}
		// Das <input type="datetime-local"> liefert `Y-m-dTH:i`.
		$value = str_replace( 'T', ' ', $value );
		$dt    = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $value, tha_timezone() );
		return $dt instanceof DateTimeImmutable ? $dt->format( 'Y-m-d H:i' ) : $fallback;
	}

	/**
	 * Uhrzeit in Schweizer Punktnotation: `20.00`, nicht `20:00`.
	 */
	private static function clean_time( $value, $fallback ) {
		$value = str_replace( ':', '.', trim( (string) $value ) );
		if ( preg_match( '/^([01]?\d|2[0-3])\.([0-5]\d)$/', $value, $m ) ) {
			return sprintf( '%d.%s', (int) $m[1], $m[2] );
		}
		return $fallback;
	}

	/**
	 * Beschriftungen einer Gruppe: je Bild-ID Name, Rolle und Text.
	 *
	 * Leere Felder werden verworfen — dann greift für dieses Feld wieder die
	 * Beschriftung aus der Mediathek. Ein Feld leeren heisst also
	 * «zurücksetzen», nicht «keine Beschriftung».
	 *
	 * Kommt aus einer älteren Installation noch ein einzelner String
	 * «Name | Rolle», wird er hier ein für alle Mal in die drei Felder
	 * aufgeteilt.
	 */
	private static function clean_labels( $labels ) {
		if ( ! is_array( $labels ) ) {
			return array();
		}
		$out = array();
		foreach ( $labels as $id => $value ) {
			$id = absint( $id );
			if ( ! $id ) {
				continue;
			}

			$label = Tha_Groups::normalize_label( $value );
			foreach ( $label as $feld => $text ) {
				$label[ $feld ] = self::clean_text( $text );
			}

			if ( '' === $label['name'] && '' === $label['role'] && '' === $label['text'] ) {
				continue;
			}
			$out[ $id ] = $label;
		}
		return $out;
	}

	/**
	 * Attachment-ID nur behalten, wenn es das Bild wirklich noch gibt.
	 */
	private static function clean_attachment( $id ) {
		$id = absint( $id );
		if ( ! $id ) {
			return 0;
		}
		return wp_attachment_is_image( $id ) ? $id : 0;
	}

	// ---------------------------------------------------------------------

	/**
	 * Schema-Migration für spätere Versionen.
	 */
	public static function upgrade() {
		$stored = get_option( self::OPTION, null );
		if ( ! is_array( $stored ) ) {
			return;
		}
		$version = isset( $stored['schema_version'] ) ? (int) $stored['schema_version'] : 0;
		if ( $version >= self::SCHEMA_VERSION ) {
			return;
		}

		// v2 — Seitenrahmen aufräumen war zuerst standardmässig aus. Das war
		// falsch herum gedacht: wer das Plugin installiert, will die
		// Theater-Seite sehen, nicht eine Blog-Seitenleiste daneben. Beim
		// Hochladen der neuen Version einmalig einschalten.
		if ( $version < 2 ) {
			$stored['hide_page_title']    = true;
			$stored['hide_sidebar']       = true;
			$stored['content_full_width'] = true;
		}

		// v3 — die Preistabelle war zuerst nur ein leeres Gerüst. Wer noch
		// keinen einzigen Betrag eingetragen hat, bekommt die echten Zahlen
		// aus dem Programmheft. Wer schon etwas erfasst hat, behält es.
		if ( $version < 3 ) {
			$hat_betraege = false;
			foreach ( (array) ( isset( $stored['prices'] ) ? $stored['prices'] : array() ) as $row ) {
				if ( ! empty( $row['amount'] ) ) {
					$hat_betraege = true;
					break;
				}
			}
			if ( ! $hat_betraege ) {
				$stored['prices']   = self::defaults()['prices'];
				$stored['openings'] = self::defaults()['openings'];
			}
		}

		// v4 — Logo-Gruppen alphabetisch. Eine Sponsorenwand in der
		// Reihenfolge, in der die Bilder angeklickt wurden, liest sich wie
		// eine Rangliste, die niemand aufgestellt hat. Nur Gruppen anfassen,
		// die den Schlüssel noch gar nicht kennen — wer schon selbst
		// gewählt hat, behält seine Wahl.
		if ( $version < 4 && ! empty( $stored['groups'] ) && is_array( $stored['groups'] ) ) {
			foreach ( $stored['groups'] as $i => $gruppe ) {
				if ( is_array( $gruppe ) && ! isset( $gruppe['sort'] ) ) {
					$stored['groups'][ $i ]['sort'] = ( isset( $gruppe['type'] ) && 'logos' === $gruppe['type'] ) ? 'alpha' : 'manuell';
				}
			}
		}

		// v5 — die Theater-Typografie fuer den uebrigen Seiteninhalt gab es
		// vorher nicht. Bestehende Installationen bekommen sie eingeschaltet:
		// auf «Speisekarte» und «Berichte» stand sonst weiterhin Open Sans
		// in Grau unter einer Theater-Navigationsleiste.
		if ( $version < 5 ) {
			$stored['content_typo'] = true;
		}

		// v6 — die Speisekarte wurde zuerst leer ausgeliefert; die echte Karte
		// lag nur als PDF vor. Wer noch keine einzige Zeile erfasst hat,
		// bekommt sie jetzt mitsamt Einleitung und Lieferantenzeile. Wer schon
		// getippt hat, behält seine Karte — dieselbe Regel wie bei den
		// Eintrittspreisen in v3.
		if ( $version < 6 && empty( $stored['menu'] ) ) {
			$d6                   = self::defaults();
			$stored['menu']       = $d6['menu'];
			$stored['menu_intro'] = $d6['menu_intro'];
			$stored['menu_note']  = $d6['menu_note'];
		}

		// v7 — die Berichte. Sie waren die letzten Seiten im alten Design und
		// fielen lange nicht auf, weil sie im Menue nicht vorkommen: «Berichte»
		// zeigt auf die Update-Seite, die Beitraege selbst erreicht man nur
		// ueber Suchmaschine oder Link. Beim Hochladen einmalig einschalten,
		// wie schon der Seitenrahmen in v2.
		if ( $version < 7 ) {
			$stored['post_singles'] = true;
		}

		// v8 — aufgeraeumt. Die abgeschafften Felder fliegen aus der Datenbank,
		// nicht nur aus dem Formular: sonst stuende in wp_options weiter ein
		// «Text Preise», den niemand mehr sieht und niemand mehr aendern kann.
		// Was dort an Bildern gewaehlt war, bleibt in der Mediathek liegen —
		// entfernt wird nur die Auswahl, nie ein Bild.
		if ( $version < 8 ) {
			$stored = self::strip_retired( $stored );
		}

		$stored['schema_version'] = self::SCHEMA_VERSION;
		update_option( self::OPTION, wp_parse_args( $stored, self::defaults() ) );
	}

	/**
	 * Sind echte Eintrittspreise erfasst?
	 *
	 * Entscheidet, ob der Abschnitt «Gut zu wissen» die Preise übernimmt. Tut
	 * er das, hat die alte Karte «Preise & Zahlung» unter «Weiteres» nichts
	 * mehr zu sagen — sie stünde nur noch doppelt da.
	 */
	public static function has_prices( array $settings = null ) {
		$settings = $settings ? $settings : self::get();
		foreach ( (array) $settings['prices'] as $row ) {
			if ( '' !== trim( (string) $row['amount'] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Die Zeilen einer Karte — «speisen» oder «getraenke».
	 */
	public static function menu_rows( $kind, array $settings = null ) {
		$settings = $settings ? $settings : self::get();
		$kind     = in_array( $kind, self::MENU_KINDS, true ) ? $kind : 'speisen';

		$rows = array();
		foreach ( (array) $settings['menu'] as $row ) {
			if ( ! is_array( $row ) || '' === trim( (string) ( isset( $row['label'] ) ? $row['label'] : '' ) ) ) {
				continue;
			}
			// Alte Zeilen ohne `kind` sind Speisen — die Spalte kam erst
			// später dazu.
			$row_kind = ( isset( $row['kind'] ) && in_array( $row['kind'], self::MENU_KINDS, true ) ) ? $row['kind'] : 'speisen';
			if ( $row_kind === $kind ) {
				$rows[] = $row;
			}
		}
		return $rows;
	}

	/**
	 * Steht überhaupt etwas auf der Karte? Entscheidet, ob die Seite die
	 * Listen zeigt oder nur ihren Text.
	 */
	public static function has_menu( array $settings = null ) {
		$settings = $settings ? $settings : self::get();
		return (bool) self::menu_rows( 'speisen', $settings ) || (bool) self::menu_rows( 'getraenke', $settings );
	}

	/**
	 * Die Update-Einträge, neueste zuerst.
	 *
	 * @param int $limit 0 = alle.
	 */
	public static function updates( $limit = 0, array $settings = null ) {
		$settings = $settings ? $settings : self::get();

		$rows = array();
		foreach ( (array) $settings['updates'] as $row ) {
			if ( is_array( $row ) && ( ! empty( $row['title'] ) || ! empty( $row['text'] ) ) ) {
				$rows[] = wp_parse_args(
					$row,
					array(
						'date'     => '',
						'title'    => '',
						'text'     => '',
						'image_id' => 0,
						'url'      => '',
						'link'     => '',
					)
				);
			}
		}

		// Auch beim Lesen sortieren, nicht nur beim Speichern: eine Liste, die
		// direkt in der Datenbank geändert wurde, soll trotzdem in der
		// richtigen Reihenfolge stehen.
		$rows  = self::sort_updates( $rows );
		$limit = absint( $limit );

		return $limit ? array_slice( $rows, 0, $limit ) : $rows;
	}

	/**
	 * Spieldaten in Spielreihenfolge: erst Datum, dann Uhrzeit.
	 *
	 * Nur nach Datum reicht nicht — bei zwei Vorstellungen am selben Tag
	 * (Vorpremiere am Nachmittag, Premiere am Abend) bliebe sonst die
	 * Eingabereihenfolge stehen. Die Uhrzeit «H.MM» wird als Zahl verglichen,
	 * damit «9.00» vor «14.00» kommt.
	 */
	private static function sort_dates( array $dates ) {
		usort(
			$dates,
			static function ( $a, $b ) {
				$cmp = strcmp(
					isset( $a['date'] ) ? (string) $a['date'] : '',
					isset( $b['date'] ) ? (string) $b['date'] : ''
				);
				if ( 0 !== $cmp ) {
					return $cmp;
				}
				$ta = (float) str_replace( ':', '.', isset( $a['time'] ) ? (string) $a['time'] : '' );
				$tb = (float) str_replace( ':', '.', isset( $b['time'] ) ? (string) $b['time'] : '' );
				return $ta <=> $tb;
			}
		);
		return $dates;
	}

	/**
	 * Neuestes zuerst — und Einträge ohne Datum ans Ende.
	 *
	 * Ein Eintrag ohne Datum ist meist ein Hinweis, der stehen bleiben soll
	 * («Vorverkauf läuft»). Oben würde er die Neuigkeiten verdecken, die
	 * wirklich neu sind.
	 */
	private static function sort_updates( array $rows ) {
		usort(
			$rows,
			static function ( $a, $b ) {
				$da = isset( $a['date'] ) ? (string) $a['date'] : '';
				$db = isset( $b['date'] ) ? (string) $b['date'] : '';
				if ( '' === $da && '' === $db ) {
					return 0;
				}
				if ( '' === $da ) {
					return 1;
				}
				if ( '' === $db ) {
					return -1;
				}
				return strcmp( $db, $da );
			}
		);
		return $rows;
	}

	/**
	 * Caching-Plugins würden die Phase sonst einfrieren.
	 */
	public static function purge_caches() {
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}
		if ( has_action( 'litespeed_purge_all' ) ) {
			do_action( 'litespeed_purge_all' );
		}
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
		}
		if ( function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all();
		}
		if ( class_exists( 'autoptimizeCache' ) && method_exists( 'autoptimizeCache', 'clearall' ) ) {
			autoptimizeCache::clearall();
		}
	}
}
