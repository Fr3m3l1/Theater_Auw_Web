<?php
/**
 * Tab — Darstellung. Alles, was mit dem Theme zu tun hat und nicht mit dem
 * Theater: Navigation, Seitenrahmen, wo das Plugin überhaupt greift, und die
 * Notausgänge für ein unbekanntes Theme.
 *
 * Die Navigation stand vorher in einem eigenen Reiter, zusammen mit dem
 * Rückblick — zwei Themen, die nichts miteinander zu tun haben, ausser dass
 * beide übrig geblieben waren.
 *
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;

$menus = wp_get_nav_menus();
?>
<h2><?php esc_html_e( 'Navigation', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:74ch">
	<?php esc_html_e( 'Das Plugin kann ein bestehendes WordPress-Menü im Theater-Look darstellen — dunkle Leiste, Untermenüs als Aufklapper, auf dem Handy zusammengeklappt. Gepflegt wird das Menü weiterhin unter Design → Menüs; es gibt also nur eine Navigation, nicht zwei.', 'theater-auw' ); ?>
</p>

<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><label for="tha-nav_menu_id"><?php esc_html_e( 'Menü', 'theater-auw' ); ?></label></th>
		<td>
			<?php if ( $menus ) : ?>
				<select id="tha-nav_menu_id" name="<?php echo esc_attr( Tha_Admin::name( 'nav_menu_id' ) ); ?>">
					<option value="0" <?php selected( 0, (int) $settings['nav_menu_id'] ); ?>>
						<?php esc_html_e( '— keine eigene Navigation —', 'theater-auw' ); ?>
					</option>
					<?php
					// An welcher Stelle im Theme haengt welches Menue? Ohne
					// diese Angabe sehen «Theater», «Homepages» und «Footer»
					// gleich aus — und man waehlt die graue Netzwerkleiste
					// statt der eigenen Navigation.
					$orte   = function_exists( 'get_nav_menu_locations' ) ? (array) get_nav_menu_locations() : array();
					$namen  = function_exists( 'get_registered_nav_menus' ) ? (array) get_registered_nav_menus() : array();
					$wo_ist = array();
					foreach ( $orte as $ort => $term_id ) {
						if ( $term_id ) {
							$wo_ist[ (int) $term_id ][] = isset( $namen[ $ort ] ) ? $namen[ $ort ] : $ort;
						}
					}
					?>
					<?php foreach ( $menus as $menu ) : ?>
						<?php
						$zusatz = isset( $wo_ist[ (int) $menu->term_id ] )
							? sprintf(
								/* translators: %s: Position im Theme */
								__( 'steht im Theme unter %s', 'theater-auw' ),
								implode( ', ', $wo_ist[ (int) $menu->term_id ] )
							)
							: __( 'keiner Theme-Position zugewiesen', 'theater-auw' );
						?>
						<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $menu->term_id, (int) $settings['nav_menu_id'] ); ?>>
							<?php
							printf(
								/* translators: 1: Menüname, 2: Anzahl Einträge, 3: Position im Theme */
								esc_html__( '%1$s — %2$d Einträge, %3$s', 'theater-auw' ),
								esc_html( $menu->name ),
								(int) $menu->count,
								esc_html( $zusatz )
							);
							?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description">
					<?php esc_html_e( 'Bleibt «keine eigene Navigation» gewählt, zeigt die Seite gar kein Menü und die Navigation deines Themes ist allein zuständig. Wähle das Theater-Menü, wenn du die Navigation im Theater-Design haben willst — erkennbar an der Position «Primary Menu».', 'theater-auw' ); ?>
				</p>
			<?php else : ?>
				<p><em><?php esc_html_e( 'Es gibt noch kein Menü. Lege zuerst unter Design → Menüs eines an.', 'theater-auw' ); ?></em></p>
			<?php endif; ?>
		</td>
	</tr>

	<tr>
		<th scope="row"><?php esc_html_e( 'Menü des Themes', 'theater-auw' ); ?></th>
		<td>
			<input type="hidden" name="<?php echo esc_attr( Tha_Admin::name( 'nav_replace_present' ) ); ?>" value="1">
			<label>
				<input type="checkbox" name="<?php echo esc_attr( Tha_Admin::name( 'nav_replace' ) ); ?>" value="1"
					<?php checked( ! empty( $settings['nav_replace'] ) ); ?>
					<?php disabled( empty( $settings['nav_menu_id'] ) ); ?>>
				<?php esc_html_e( 'Auf den Theater-Seiten ausblenden', 'theater-auw' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Sonst stehen zwei Navigationen übereinander — die des Themes und die im Theater-Design. Ausgeblendet wird nur die Menüleiste, und nur auf Seiten mit Theater-Inhalt. Logo, Suche und die graue Vereinsleiste ganz oben bleiben stehen; ohne sie käme niemand mehr zu stvauw.ch zurück.', 'theater-auw' ); ?>
			</p>
			<?php if ( empty( $settings['nav_menu_id'] ) ) : ?>
				<p class="description">
					<strong><?php esc_html_e( 'Erst ein Menü wählen.', 'theater-auw' ); ?></strong>
					<?php esc_html_e( 'Ohne eigene Navigation würde die Seite sonst gar keine mehr haben.', 'theater-auw' ); ?>
				</p>
			<?php endif; ?>
		</td>
	</tr>

	<tr>
		<th scope="row"><?php esc_html_e( 'Mitscrollen', 'theater-auw' ); ?></th>
		<td>
			<input type="hidden" name="<?php echo esc_attr( Tha_Admin::name( 'nav_sticky_present' ) ); ?>" value="1">
			<label>
				<input type="checkbox" name="<?php echo esc_attr( Tha_Admin::name( 'nav_sticky' ) ); ?>" value="1" <?php checked( ! empty( $settings['nav_sticky'] ) ); ?>>
				<?php esc_html_e( 'Die Leiste beim Scrollen oben stehen lassen', 'theater-auw' ); ?>
			</label>

			<p style="margin-left:24px">
				<input type="hidden" name="<?php echo esc_attr( Tha_Admin::name( 'nav_sticky_top_present' ) ); ?>" value="1">
				<label>
					<input type="checkbox" name="<?php echo esc_attr( Tha_Admin::name( 'nav_sticky_top' ) ); ?>" value="1"
						<?php checked( ! empty( $settings['nav_sticky_top'] ) ); ?>
						<?php disabled( empty( $settings['nav_sticky'] ) ); ?>>
					<?php esc_html_e( 'Ganz oben, an der Bildschirmkante', 'theater-auw' ); ?>
				</label>
			</p>
			<p class="description" style="margin-left:24px;max-width:74ch">
				<?php esc_html_e( 'Ohne diesen Haken hält die Theater-Leiste Abstand: über ihr hängen die graue Vereinsleiste und die Kopfzeile des Themes, beide fest verankert — ohne Abstand verschwände sie beim Scrollen dahinter. Mit Haken scrollen jene weg und die Theater-Leiste klebt an der Oberkante des Bildschirms.', 'theater-auw' ); ?>
			</p>
			<p class="description" style="margin-left:24px;max-width:74ch">
				<?php esc_html_e( 'Am Handy ist das meist die bessere Wahl — dort ist der Platz oben knapp und zwei Leisten übereinander fressen den halben Bildschirm. Die Admin-Leiste von WordPress bleibt in jedem Fall frei; sonst käme man als Angemeldete nicht mehr aus der Seite heraus.', 'theater-auw' ); ?>
			</p>
		</td>
	</tr>

	<?php
	// Das Logo steht in der Navigationsleiste und in jedem Kopfbereich —
	// also hier, direkt bei der Leiste, in der man es sieht.
	Tha_Admin::image_field(
		'logo_id',
		$settings['logo_id'],
		__( 'Logo', 'theater-auw' ),
		__( 'Die Wortmarke in der Leiste und im dunklen Kopfbereich. Ohne Auswahl wird «theater auw» typografisch gesetzt — weiss mit rotem «auw». Das Logo gehört nur auf dunkle Flächen.', 'theater-auw' )
	);
	?>
</table>

<h2><?php esc_html_e( 'Seitenrahmen', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:74ch">
	<?php esc_html_e( 'Die Theater-Seite ist eine Plakat-Seite: dunkler Kopfbereich über die volle Breite, grosse Schrift. Steckt dein Theme sie in eine schmale Spalte neben eine Blog-Seitenleiste und schreibt den Seitentitel darüber, wirkt sie zusammengedrückt. Diese drei Schalter räumen das auf — nur auf den Seiten mit Theater-Inhalt, sonst ändert sich nichts.', 'theater-auw' ); ?>
</p>

<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><?php esc_html_e( 'Aufräumen', 'theater-auw' ); ?></th>
		<td>
			<input type="hidden" name="<?php echo esc_attr( Tha_Admin::name( 'cleanup_present' ) ); ?>" value="1">
			<fieldset>
				<label style="display:block;margin-bottom:8px">
					<input type="checkbox" name="<?php echo esc_attr( Tha_Admin::name( 'hide_page_title' ) ); ?>" value="1" <?php checked( ! empty( $settings['hide_page_title'] ) ); ?>>
					<?php esc_html_e( 'Seitentitel ausblenden', 'theater-auw' ); ?>
					<span class="description"><?php esc_html_e( '— der Titel steht schon gross im Kopfbereich', 'theater-auw' ); ?></span>
				</label>
				<label style="display:block;margin-bottom:8px">
					<input type="checkbox" name="<?php echo esc_attr( Tha_Admin::name( 'hide_sidebar' ) ); ?>" value="1" <?php checked( ! empty( $settings['hide_sidebar'] ) ); ?>>
					<?php esc_html_e( 'Seitenleiste ausblenden', 'theater-auw' ); ?>
					<span class="description"><?php esc_html_e( '— «Neueste Beiträge», «Archiv» und Co. gehören nicht neben ein Theaterplakat', 'theater-auw' ); ?></span>
				</label>
				<label style="display:block">
					<input type="checkbox" name="<?php echo esc_attr( Tha_Admin::name( 'content_full_width' ) ); ?>" value="1" <?php checked( ! empty( $settings['content_full_width'] ) ); ?>>
					<?php esc_html_e( 'Inhaltsbereich auf volle Breite', 'theater-auw' ); ?>
					<span class="description"><?php esc_html_e( '— damit der dunkle Kopfbereich randlos läuft', 'theater-auw' ); ?></span>
				</label>
			</fieldset>
			<p class="description">
				<?php esc_html_e( 'Bietet dein Theme eine Seitenvorlage «Volle Breite» an, ist die der sauberere Weg — bei Divi in den Seiten-Einstellungen unter «Seitenlayout». Diese Schalter sind der Ersatz, wenn es die nicht gibt oder sie nicht reicht.', 'theater-auw' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row"><?php esc_html_e( 'Theater-Typografie', 'theater-auw' ); ?></th>
		<td>
			<input type="hidden" name="<?php echo esc_attr( Tha_Admin::name( 'content_typo_present' ) ); ?>" value="1">
			<label>
				<input type="checkbox" name="<?php echo esc_attr( Tha_Admin::name( 'content_typo' ) ); ?>" value="1" <?php checked( ! empty( $settings['content_typo'] ) ); ?>>
				<?php esc_html_e( 'Auch auf den übrigen Seiteninhalt anwenden', 'theater-auw' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Für Seiten, deren Inhalt nicht im Shortcode steckt — etwa im Divi-Builder gebaute wie «Speisekarte» oder «Berichte». Ohne diesen Schalter steht dort weiterhin die Schrift des Themes unter einer Theater-Navigationsleiste, und die Seite wirkt halb umgestellt.', 'theater-auw' ); ?>
			</p>
			<p class="description">
				<?php esc_html_e( 'Übernommen werden nur Schrift, Grösse, Farbe und der Papierhintergrund — keine Abstände und keine Rasterangaben. Deine Divi-Module behalten also ihren Aufbau; sie bekommen nur die Handschrift des Theaters. Wo du in einem Modul selbst eine Farbe oder Grösse gesetzt hast, bleibt deine Einstellung stehen.', 'theater-auw' ); ?>
			</p>
		</td>
	</tr>
</table>

<h2><?php esc_html_e( 'Notausgänge', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:74ch">
	<?php esc_html_e( 'Diese Einstellungen brauchst du nur, wenn die Seite in deinem Theme nicht richtig sitzt. Im Normalfall bleibt alles, wie es ist. Welche Seiten das Plugin überhaupt gestaltet, steht unter «Übersicht».', 'theater-auw' ); ?>
</p>

<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><label for="tha-page_id"><?php esc_html_e( 'Theater-Seite', 'theater-auw' ); ?></label></th>
		<td>
			<?php
			wp_dropdown_pages(
				array(
					'name'              => Tha_Admin::name( 'page_id' ),
					'id'                => 'tha-page_id',
					'selected'          => (int) $settings['page_id'],
					'show_option_none'  => __( '— nicht gesetzt —', 'theater-auw' ),
					'option_none_value' => 0,
				)
			);
			?>
			<p class="description"><?php esc_html_e( 'Nur nötig, wenn du einen Page-Builder benutzt: dann findet das Plugin den Shortcode nicht im Seiteninhalt und lädt seine Stile sonst nicht. Setzt ausserdem die Vorschau-Links oben.', 'theater-auw' ); ?></p>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="tha-width_mode"><?php esc_html_e( 'Breite', 'theater-auw' ); ?></label></th>
		<td>
			<select id="tha-width_mode" name="<?php echo esc_attr( Tha_Admin::name( 'width_mode' ) ); ?>">
				<option value="auto" <?php selected( 'auto', $settings['width_mode'] ); ?>><?php esc_html_e( 'Automatisch — dem Theme überlassen', 'theater-auw' ); ?></option>
				<option value="viewport" <?php selected( 'viewport', $settings['width_mode'] ); ?>><?php esc_html_e( 'Volle Fensterbreite erzwingen', 'theater-auw' ); ?></option>
				<option value="contained" <?php selected( 'contained', $settings['width_mode'] ); ?>><?php esc_html_e( 'Im Container des Themes bleiben', 'theater-auw' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'Probiere zuerst die Seitenvorlage «Volle Breite» deines Themes — das ist immer die sauberste Lösung. Erst wenn es die nicht gibt, hier auf «Volle Fensterbreite erzwingen» stellen.', 'theater-auw' ); ?></p>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="tha-isolation"><?php esc_html_e( 'Abschottung', 'theater-auw' ); ?></label></th>
		<td>
			<select id="tha-isolation" name="<?php echo esc_attr( Tha_Admin::name( 'isolation' ) ); ?>">
				<option value="normal" <?php selected( 'normal', $settings['isolation'] ); ?>><?php esc_html_e( 'Normal', 'theater-auw' ); ?></option>
				<option value="stark" <?php selected( 'stark', $settings['isolation'] ); ?>><?php esc_html_e( 'Stark — gegen dominante Themes', 'theater-auw' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'Wenn Schriften oder Abstände vom Theme überschrieben werden, hilft «Stark».', 'theater-auw' ); ?></p>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="tha-font_source"><?php esc_html_e( 'Schriften', 'theater-auw' ); ?></label></th>
		<td>
			<select id="tha-font_source" name="<?php echo esc_attr( Tha_Admin::name( 'font_source' ) ); ?>">
				<option value="google" <?php selected( 'google', $settings['font_source'] ); ?>><?php esc_html_e( 'Von Google Fonts laden', 'theater-auw' ); ?></option>
				<option value="theme" <?php selected( 'theme', $settings['font_source'] ); ?>><?php esc_html_e( 'Schriften des Themes verwenden', 'theater-auw' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'Big Shoulders und Libre Franklin kommen von Google Fonts. Wer das aus Datenschutzgründen nicht möchte, stellt auf die Theme-Schriften um — das Layout bleibt, die Plakat-Wirkung geht etwas verloren.', 'theater-auw' ); ?></p>
		</td>
	</tr>

	<tr>
		<th scope="row"><?php esc_html_e( 'Ticket-Leiste', 'theater-auw' ); ?></th>
		<td>
			<input type="hidden" name="<?php echo esc_attr( Tha_Admin::name( 'sticky_cta_present' ) ); ?>" value="1">
			<label>
				<input type="checkbox" name="<?php echo esc_attr( Tha_Admin::name( 'sticky_cta' ) ); ?>" value="1" <?php checked( ! empty( $settings['sticky_cta'] ) ); ?>>
				<?php esc_html_e( 'Beim Scrollen eine kleine Ticket-Leiste unten einblenden', 'theater-auw' ); ?>
			</label>
			<p class="description"><?php esc_html_e( 'Nur in den Phasen Vorverkauf und Spielzeit, und nur wenn ein Ticketlink hinterlegt ist.', 'theater-auw' ); ?></p>
		</td>
	</tr>
</table>
