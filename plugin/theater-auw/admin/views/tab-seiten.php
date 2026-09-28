<?php
/**
 * Tab — Seiten.
 *
 * Beantwortet die Frage, die sich beim Umbau als erstes stellt: welche Seite
 * ist schon umgestellt und welche noch nicht? Das Plugin gestaltet nur Seiten,
 * auf denen einer seiner Shortcodes steht — alle anderen bleiben im alten
 * Design. Ohne diese Übersicht sucht man sich einen Wolf.
 *
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;

$tags = Tha_Shortcode::TAGS;

$pages = get_pages(
	array(
		'sort_column' => 'menu_order,post_title',
		'number'      => 200,
	)
);

/**
 * Welche Shortcodes stehen auf dieser Seite — und zwar WÖRTLICH?
 *
 * Vorher stand hier nur der nackte Name, also dreimal `[theater_auw_seite]`
 * für Schauspieler, Kulissen und OK. Damit war die Spalte wertlos: sie sagte
 * nicht, welche Gruppe auf welcher Seite liegt, und wer eine Seite duplizieren
 * wollte, musste sie erst öffnen. Jetzt steht dort, was wirklich drin steht.
 *
 * Die längeren Namen kommen zuerst in die Alternative, sonst würde
 * `theater_auw` schon das `theater_auw_seite` abfangen. Der schliessende
 * `[/theater_auw_seite]` wird nicht erfasst — nach der Klammer steht ein
 * Schrägstrich, kein Buchstabe.
 */
$found_on = static function ( $content ) use ( $tags ) {
	$sortiert = $tags;
	usort(
		$sortiert,
		static function ( $a, $b ) {
			return strlen( $b ) - strlen( $a );
		}
	);
	$muster = '/\[(?:' . implode( '|', array_map( 'preg_quote', $sortiert ) ) . ')(?:\s[^\]]*)?\]/';

	if ( ! preg_match_all( $muster, (string) $content, $treffer ) ) {
		return array();
	}
	return array_values( array_unique( $treffer[0] ) );
};

// Vorschläge: welcher Shortcode passt zu welcher Seite? Erkannt am Titel.
$suggestions = array(
	'schauspieler' => '[theater_auw_seite titel="Die Schauspieler" gruppe="schauspieler"][/theater_auw_seite]',
	'kulissen'     => '[theater_auw_seite titel="Hinter den Kulissen" gruppe="kulissen"][/theater_auw_seite]',
	'ok'           => '[theater_auw_seite titel="Organisation Komitee" gruppe="ok"][/theater_auw_seite]',
	'sponsoren'    => '[theater_auw_seite titel="Sponsoren" gruppe="sponsoren"][/theater_auw_seite]',
	'spieldaten'   => '[theater_auw sections="hero,spieldaten"]',
	// Diese zwei Seiten waren am 26.07.2026 die letzten im alten Design. Ihr
	// Inhalt liegt in Divi-Modulen — dort sieht das Plugin nichts von seinem
	// Shortcode. Seit es für beide einen eigenen Seitentyp gibt, stehen sie
	// hier mit ihrem Vorschlag.
	'karte'        => '[theater_auw_karte]',
	'updates'      => '[theater_auw_updates]',
);

/**
 * Seiten, die etwas TUN statt etwas zu ZEIGEN.
 *
 * «Anmeldung Sponsoring» trägt das Wort «Sponsor» im Titel, ist aber das
 * Buchungsformular für die Premieren- und Spenderpakete — mit Preisen,
 * Menüfolge und Bankverbindung. Wer dort die Sponsorengalerie einsetzt,
 * stellt 31 Logos vor das Formular und verdeckt genau das, wofür die Seite
 * da ist. Ein Vorschlag, der so etwas empfiehlt, ist schlimmer als keiner.
 */
$ist_funktionsseite = static function ( $title ) {
	$t = strtolower( $title );
	foreach ( array( 'anmeldung', 'anmelden', 'formular', 'erfolgreich', 'impressum', 'datenschutz' ) as $wort ) {
		if ( false !== strpos( $t, $wort ) ) {
			return true;
		}
	}
	return false;
};

/** Trägt der Titel überhaupt einen Theater-Begriff? */
$hat_theaterbezug = static function ( $title ) {
	$t = strtolower( $title );
	foreach ( array( 'sponsor', 'schauspieler', 'kulissen', 'komitee', 'spieldaten', 'premiere', 'theater' ) as $wort ) {
		if ( false !== strpos( $t, $wort ) ) {
			return true;
		}
	}
	return false;
};

$suggest_for = static function ( $title ) use ( $suggestions, $ist_funktionsseite, $settings ) {
	$t = strtolower( $title );

	if ( $ist_funktionsseite( $title ) ) {
		return '';
	}

	// «Sponsoren Archiv» sammelt vergangene Jahrgänge. Dort die aktuelle
	// Sponsorenwand einzusetzen würde die Geschichte durch die Gegenwart
	// ersetzen.
	if ( false !== strpos( $t, 'archiv' ) ) {
		return '';
	}

	// «Sponsoren 2018» ist ein Archiv, «Sponsoren 2025» die laufende Saison.
	// Eine Jahreszahl, die weder zur aktuellen noch zur letzten Saison passt,
	// bedeutet: alter Jahrgang, Finger weg.
	if ( preg_match( '/(?<!\d)(19|20)\d{2}(?!\d)/', $title, $j )
		&& ! preg_match( '/(\d{4})\s*[–—-]\s*(\d{4})/u', $title ) ) {
		$jahr   = (int) $j[0];
		$aktuell = (int) $settings['season_year'];
		if ( $jahr !== $aktuell && $jahr !== $aktuell - 1 ) {
			return '';
		}
	}

	if ( preg_match( '/(\d{4})\s*[–—-]\s*(\d{4})/u', $title, $m ) ) {
		$a   = (int) $m[1];
		$b   = (int) $m[2];
		$von = min( $a, $b );
		$bis = max( $a, $b );

		// Zwei Bestände, zwei Shortcodes. Was vor dem Portfolio liegt, steht
		// fest im Plugin — dort gibt es keine Plakate, dafür Autor, Regie und
		// Besetzung. Der Rückblick hätte für diese Jahre nichts zu zeigen und
		// bliebe leer, wie es auf «2007 – 1987» tatsächlich passiert ist.
		if ( Tha_Chronik::covers( $von, $bis ) ) {
			return sprintf(
				'[theater_auw_chronik von="%d" bis="%d" titel="%s"]',
				$von,
				$bis,
				esc_attr( $title )
			);
		}

		return sprintf(
			'[theater_auw_rueckblick von="%d" bis="%d" titel="%s"]',
			$von,
			$bis,
			esc_attr( $title )
		);
	}
	if ( false !== strpos( $t, 'schauspieler' ) ) {
		return $suggestions['schauspieler'];
	}
	if ( false !== strpos( $t, 'kulissen' ) ) {
		return $suggestions['kulissen'];
	}
	if ( false !== strpos( $t, 'komitee' ) || preg_match( '/\bok\b/', $t ) ) {
		return $suggestions['ok'];
	}
	if ( false !== strpos( $t, 'sponsor' ) ) {
		return $suggestions['sponsoren'];
	}
	if ( false !== strpos( $t, 'spieldaten' ) ) {
		return $suggestions['spieldaten'];
	}
	// «Speisekarte», «Menü», «Menu» — die Karte der Küche. Eine
	// «Getränkekarte» als eigene Seite bekäme denselben Shortcode und damit
	// auch die Speisen; die stehen im Plugin auf EINER Seite in zwei Spalten,
	// wie im Programmheft. Deshalb bewusst kein Vorschlag dafür.
	if ( false !== strpos( $t, 'speisekarte' ) || preg_match( '/\bmen(?:u|ü)\b/u', $t ) ) {
		return $suggestions['karte'];
	}
	if ( false !== strpos( $t, 'update' ) || false !== strpos( $t, 'neuigkeit' ) || false !== strpos( $t, 'aktuelles' ) ) {
		return $suggestions['updates'];
	}
	return '';
};
?>

<h2><?php esc_html_e( 'Welche Seite ist schon umgestellt?', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:76ch">
	<?php esc_html_e( 'Das Plugin gestaltet nur Seiten, auf denen einer seiner Shortcodes steht. Alle anderen bleiben unverändert im bisherigen Design — auch wenn sie im Menü direkt daneben stehen. Diese Liste zeigt, wo schon etwas drin ist und was auf den Rest gehört.', 'theater-auw' ); ?>
</p>

<table class="widefat striped tha-pages">
	<thead>
		<tr>
			<th style="width:26%"><?php esc_html_e( 'Seite', 'theater-auw' ); ?></th>
			<th style="width:14%"><?php esc_html_e( 'Zustand', 'theater-auw' ); ?></th>
			<th><?php esc_html_e( 'Shortcode', 'theater-auw' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $pages as $page ) : ?>
			<?php
			$hits    = $found_on( $page->post_content );
			$suggest = $hits ? '' : $suggest_for( $page->post_title );

			// Formularseiten mit Theater-Bezug bleiben in der Liste, aber mit
			// klarer Ansage. Sie einfach wegzulassen wäre auch eine Antwort —
			// nur nicht die Frage «warum steht die hier nicht?».
			$funktion = ! $hits && $ist_funktionsseite( $page->post_title ) && $hat_theaterbezug( $page->post_title );

			// Seiten ohne Theater-Bezug gar nicht erst auflisten.
			if ( ! $hits && ! $funktion && '' === $suggest ) {
				continue;
			}
			?>
			<tr>
				<td>
					<strong><?php echo esc_html( $page->post_title ); ?></strong><br>
					<a href="<?php echo esc_url( get_edit_post_link( $page->ID ) ); ?>"><?php esc_html_e( 'bearbeiten', 'theater-auw' ); ?></a> ·
					<a href="<?php echo esc_url( get_permalink( $page->ID ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'ansehen', 'theater-auw' ); ?></a>
				</td>
				<td>
					<?php if ( $hits ) : ?>
						<span class="tha-badge-ok"><?php esc_html_e( 'umgestellt', 'theater-auw' ); ?></span>
					<?php elseif ( $funktion ) : ?>
						<span class="tha-badge-todo"><?php esc_html_e( 'Formular', 'theater-auw' ); ?></span>
					<?php else : ?>
						<span class="tha-badge-todo"><?php esc_html_e( 'altes Design', 'theater-auw' ); ?></span>
					<?php endif; ?>
				</td>
				<td>
					<?php if ( $hits ) : ?>
						<?php foreach ( $hits as $hit ) : ?>
							<code class="tha-code-line"><?php echo esc_html( $hit ); ?></code>
						<?php endforeach; ?>
					<?php elseif ( $funktion ) : ?>
						<p class="description">
							<?php esc_html_e( 'Hier gehört kein Shortcode hin. Auf dieser Seite steht ein Formular — sie soll etwas entgegennehmen, nicht etwas ausstellen. Sie wird deshalb nicht umgebaut, sondern von aussen gestaltet: Seitenkopf, Schrift und Anmeldefrist stellst du unter «Sponsoring» ein.', 'theater-auw' ); ?>
						</p>
					<?php else : ?>
						<textarea readonly rows="2" class="large-text code" onclick="this.select()"><?php echo esc_textarea( $suggest ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Anklicken, kopieren, auf der Seite in einen Shortcode-Block einfügen.', 'theater-auw' ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<h2><?php esc_html_e( 'Berichte', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:76ch">
	<?php esc_html_e( 'Berichte sind Beiträge, keine Seiten — sie stehen unter Adressen wie /theater/2026/06/2738/ und tauchen deshalb in der Liste oben nicht auf. Im Menü kommen sie ebenfalls nicht vor: «Berichte» zeigt auf die Update-Seite. Genau darum sind sie beim Umbau übrig geblieben.', 'theater-auw' ); ?>
</p>
<p class="description" style="max-width:76ch">
	<?php esc_html_e( 'Erfasst werden nur Beiträge unter der Adresse der Theater-Seite. Ein Blog neben dem Theater — auf stvauw.ch der Vereinsblog unter /blog/ — bleibt unangetastet.', 'theater-auw' ); ?>
</p>

<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><?php esc_html_e( 'Berichte umstellen', 'theater-auw' ); ?></th>
		<td>
			<input type="hidden" name="<?php echo esc_attr( Tha_Admin::name( 'post_singles_present' ) ); ?>" value="1">
			<label>
				<input type="checkbox" name="<?php echo esc_attr( Tha_Admin::name( 'post_singles' ) ); ?>" value="1" <?php checked( ! empty( $settings['post_singles'] ) ); ?>>
				<?php esc_html_e( 'Beiträge als Theater-Seiten behandeln', 'theater-auw' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Damit gelten dort dieselben Einstellungen wie auf den übrigen Theater-Seiten: Schrift und Farben, volle Breite, keine Blog-Seitenleiste mit «Neueste Beiträge» und «Archiv». In keinen Bericht muss dafür etwas eingesetzt werden — auch der nächste ist automatisch dabei.', 'theater-auw' ); ?>
			</p>
			<p class="description">
				<?php esc_html_e( 'Die Überschrift des Berichts bleibt stehen, auch wenn Seitentitel sonst ausgeblendet sind. Auf einer Theater-Seite trägt der Kopfbereich den Titel; in einem Bericht tut das niemand sonst.', 'theater-auw' ); ?>
			</p>
			<p class="description">
				<?php
				printf(
					/* translators: %s: Shortcode */
					esc_html__( 'Die Navigationsleiste kommt wie bei den Jahrgangsseiten aus der Theme-Builder-Vorlage — dort steht %s bereits.', 'theater-auw' ),
					'<code>[theater_auw_nav]</code>'
				);
				?>
			</p>
		</td>
	</tr>
</table>

<?php
$berichte = get_posts(
	array(
		'post_type'   => 'post',
		'post_status' => array( 'publish', 'draft', 'private' ),
		'numberposts' => 50,
	)
);
// Dieselbe Abgrenzung wie in der Erkennung: nur Beiträge unter der
// Theater-Adresse sind Berichte. Blogbeiträge daneben gehoeren nicht in
// diese Liste — sie bekommen auch keine Theater-Stile.
$berichte = array_filter(
	$berichte,
	static function ( $b ) use ( $settings ) {
		return Tha_Assets::post_in_theater( (int) $b->ID, $settings );
	}
);
?>
<?php if ( $berichte ) : ?>
	<table class="widefat striped tha-pages">
		<thead>
			<tr>
				<th style="width:40%"><?php esc_html_e( 'Bericht', 'theater-auw' ); ?></th>
				<th style="width:16%"><?php esc_html_e( 'Datum', 'theater-auw' ); ?></th>
				<th><?php esc_html_e( 'Adresse', 'theater-auw' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $berichte as $bericht ) : ?>
				<tr>
					<td>
						<strong><?php echo esc_html( $bericht->post_title ); ?></strong><br>
						<a href="<?php echo esc_url( get_edit_post_link( $bericht->ID ) ); ?>"><?php esc_html_e( 'bearbeiten', 'theater-auw' ); ?></a> ·
						<a href="<?php echo esc_url( get_permalink( $bericht->ID ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'ansehen', 'theater-auw' ); ?></a>
					</td>
					<td><?php echo esc_html( get_the_date( '', $bericht ) ); ?></td>
					<td><code class="tha-code-line"><?php echo esc_html( wp_make_link_relative( get_permalink( $bericht->ID ) ) ); ?></code></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php else : ?>
	<p class="description"><?php esc_html_e( 'Es gibt noch keine Berichte.', 'theater-auw' ); ?></p>
<?php endif; ?>

<h2><?php esc_html_e( 'Jahrgangsseiten', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:76ch">
	<?php esc_html_e( 'Der zweite Fall derselben Sorte: die Seiten hinter den Kacheln des Rückblicks — bei Divi die Einzelansicht des Portfolios. Sie tragen keinen Shortcode, weil ihr Aufbau aus einer Vorlage kommt, und stehen deshalb auch nicht in der Liste oben.', 'theater-auw' ); ?>
</p>

<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><?php esc_html_e( 'Jahrgangsseiten umstellen', 'theater-auw' ); ?></th>
		<td>
			<input type="hidden" name="<?php echo esc_attr( Tha_Admin::name( 'rueckblick_singles_present' ) ); ?>" value="1">
			<label>
				<input type="checkbox" name="<?php echo esc_attr( Tha_Admin::name( 'rueckblick_singles' ) ); ?>" value="1" <?php checked( ! empty( $settings['rueckblick_singles'] ) ); ?>>
				<?php esc_html_e( 'Jahrgangsseiten als Theater-Seiten behandeln', 'theater-auw' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Eingeschaltet greifen dort Schrift, Farben und das Ausblenden der Theme-Navigation genauso wie auf den übrigen Theater-Seiten. Was der Rückblick zeigt und woher er es nimmt, steht unter «Rückblick».', 'theater-auw' ); ?>
			</p>
			<p class="description">
				<?php
				printf(
					/* translators: %s: Shortcode */
					esc_html__( 'Damit dort auch die Navigationsleiste erscheint, setzt du %s einmal in die Theme-Builder-Vorlage für diesen Beitragstyp — das gilt dann für alle Jahrgänge auf einmal.', 'theater-auw' ),
					'<code>[theater_auw_nav]</code>'
				);
				?>
			</p>
		</td>
	</tr>
</table>

<h2><?php esc_html_e( 'Alle Shortcodes', 'theater-auw' ); ?></h2>

<table class="widefat striped">
	<tbody>
		<tr>
			<td style="width:34%"><code>[theater_auw]</code></td>
			<td><?php esc_html_e( 'Die Hauptseite mit den vier Saison-Phasen.', 'theater-auw' ); ?></td>
		</tr>
		<tr>
			<td><code>[theater_auw_seite titel="…" gruppe="…"]</code></td>
			<td><?php esc_html_e( 'Rahmen für eine Unterseite: Kopfbereich, Bildergruppe, dann dein Text.', 'theater-auw' ); ?></td>
		</tr>
		<tr>
			<td><code>[theater_auw_gruppe key="…"]</code></td>
			<td><?php esc_html_e( 'Nur eine Bildergruppe, ohne Kopfbereich.', 'theater-auw' ); ?></td>
		</tr>
		<tr>
			<td><code>[theater_auw_rueckblick von="2008" bis="2026"]</code></td>
			<td><?php esc_html_e( 'Die Jahrgänge aus dem Portfolio, auf einen Zeitraum eingegrenzt — für die bestehenden Rückblick-Seiten.', 'theater-auw' ); ?></td>
		</tr>
		<tr>
			<td><code>[theater_auw_nav]</code></td>
			<td><?php esc_html_e( 'Nur die Navigationsleiste. Für Seiten, die schon einen eigenen Aufbau haben — im Divi-Builder als erstes Modul ganz oben einsetzen. Der übrige Inhalt bleibt, wie er ist.', 'theater-auw' ); ?></td>
		</tr>
		<tr>
			<td><code>[theater_auw_kontakt]</code></td>
			<td><?php esc_html_e( 'Nur der Abschluss mit der Kontaktzeile. Das Gegenstück dazu, ans Ende derselben Seite.', 'theater-auw' ); ?></td>
		</tr>
		<tr>
			<td><code>[theater_auw_chronik von="1987" bis="2007"]</code></td>
			<td>
				<?php
				$ch_span = Tha_Chronik::span();
				printf(
					/* translators: 1: Anzahl Jahrgaenge, 2: aeltestes Jahr, 3: juengstes Jahr */
					esc_html__( 'Die eingebaute Chronik: %1$d Jahrgänge von %2$d bis %3$d, mit Gattung, Autor, Regie und Besetzung. Für die Jahre vor dem Portfolio — dort gibt es keine Plakate, deshalb eine Liste statt Kacheln. Ohne Zeitraum kommt der ganze Bestand.', 'theater-auw' ),
					count( Tha_Chronik::all() ),
					(int) $ch_span['min'],
					(int) $ch_span['max']
				);
				?>
			</td>
		</tr>
	</tbody>
</table>
