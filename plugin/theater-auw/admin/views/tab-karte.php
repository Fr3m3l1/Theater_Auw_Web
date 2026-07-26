<?php
/**
 * Tab — Speisekarte.
 *
 * Die Seite /theater/speisekarte war zuletzt die einzige neben dem
 * Theater-Menü, die noch im alten Design stand: ihr Inhalt liegt in
 * Divi-Modulen, das Plugin sah dort nichts von seinem Shortcode. Hier wird
 * die Karte erfasst, auf der Seite steht danach nur noch ein Shortcode.
 *
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;

$kinds = array(
	'speisen'   => __( 'Speisen', 'theater-auw' ),
	'getraenke' => __( 'Getränke', 'theater-auw' ),
);

// Eine leere Zeile zum Losschreiben, wenn noch nichts erfasst ist.
$rows = $settings['menu'] ? $settings['menu'] : array(
	array( 'kind' => 'speisen', 'group' => '', 'label' => '', 'note' => '', 'amount' => '' ),
);
?>
<h2><?php esc_html_e( 'Speisekarte', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:76ch">
	<?php esc_html_e( 'Der Inhalt der Seite «Speisekarte». Die Gerichte stehen hier als echte Zeilen und nicht als Foto der Karte — ein Bild kann niemand vorlesen lassen, niemand durchsuchen und auf dem Handy schlecht entziffern. Auf der Seite selbst steht danach nur noch ein Shortcode.', 'theater-auw' ); ?>
</p>

<table class="form-table" role="presentation">
	<?php
	Tha_Admin::text_field( 'menu_title', $settings['menu_title'], __( 'Überschrift', 'theater-auw' ), __( 'Steht im Kopfbereich der Seite.', 'theater-auw' ) );
	Tha_Admin::text_field( 'menu_kicker', $settings['menu_kicker'], __( 'Kicker', 'theater-auw' ), __( 'Die kleine Zeile über der Überschrift.', 'theater-auw' ) );
	Tha_Admin::textarea_field( 'menu_intro', $settings['menu_intro'], __( 'Einleitung', 'theater-auw' ), __( 'Optional. Bleibt das Feld leer, steht dort der Küchen-Text aus «Stück &amp; Texte».', 'theater-auw' ) );
	?>
</table>

<h2><?php esc_html_e( 'Die Zeilen', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:76ch">
	<?php esc_html_e( 'Vorbelegt mit der Speise- und Getränkekarte 2025 — geh sie einmal durch, sobald die Karte für 2026 steht. Speisen und Getränke stehen auf der Seite in zwei Spalten. Zeilen mit demselben Gang werden darunter zusammengefasst — «Warme Küche» steht dann einmal da, die Gerichte darunter. Ein Gericht ohne Preis ist erlaubt; eine Zeile ohne Bezeichnung wird verworfen.', 'theater-auw' ); ?>
</p>
<p class="description" style="max-width:76ch">
	<?php esc_html_e( 'Wird ein Getränk in zwei Grössen ausgeschenkt, stehen beide Masse in der Beschreibung und beide Beträge in derselben Reihenfolge im Preis — «1 dl / 7 dl» und «Fr. 7.– / 36.–». Sonst müsste der Weinname zweimal dastehen.', 'theater-auw' ); ?>
</p>

<table class="widefat tha-repeat" data-tha-repeat="menu">
	<thead>
		<tr>
			<th style="width:12%"><?php esc_html_e( 'Karte', 'theater-auw' ); ?></th>
			<th style="width:16%"><?php esc_html_e( 'Gang', 'theater-auw' ); ?></th>
			<th style="width:24%"><?php esc_html_e( 'Bezeichnung', 'theater-auw' ); ?></th>
			<th><?php esc_html_e( 'Beschreibung', 'theater-auw' ); ?></th>
			<th style="width:12%"><?php esc_html_e( 'Preis', 'theater-auw' ); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody data-tha-repeat-body>
		<?php foreach ( $rows as $i => $row ) : ?>
			<?php $row = wp_parse_args( (array) $row, array( 'kind' => 'speisen', 'group' => '', 'label' => '', 'note' => '', 'amount' => '' ) ); ?>
			<tr data-tha-repeat-row>
				<td>
					<select name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[menu][<?php echo (int) $i; ?>][kind]">
						<?php foreach ( $kinds as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $row['kind'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
				<td><input type="text" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[menu][<?php echo (int) $i; ?>][group]" value="<?php echo esc_attr( $row['group'] ); ?>" placeholder="<?php esc_attr_e( 'Hauptgang', 'theater-auw' ); ?>"></td>
				<td><input type="text" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[menu][<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr( $row['label'] ); ?>" placeholder="<?php esc_attr_e( 'Schnitzel mit Pommes', 'theater-auw' ); ?>"></td>
				<td><input type="text" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[menu][<?php echo (int) $i; ?>][note]" value="<?php echo esc_attr( $row['note'] ); ?>" placeholder="<?php esc_attr_e( 'mit Salat', 'theater-auw' ); ?>"></td>
				<td><input type="text" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[menu][<?php echo (int) $i; ?>][amount]" value="<?php echo esc_attr( $row['amount'] ); ?>" placeholder="Fr. 18.–"></td>
				<td><button type="button" class="button-link tha-drop" data-tha-repeat-drop><?php esc_html_e( 'Entfernen', 'theater-auw' ); ?></button></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<p><button type="button" class="button" data-tha-repeat-add><?php esc_html_e( 'Zeile hinzufügen', 'theater-auw' ); ?></button></p>

<table class="form-table" role="presentation">
	<?php Tha_Admin::text_field( 'menu_note', $settings['menu_note'], __( 'Hinweis unter der Karte', 'theater-auw' ), __( 'Zum Beispiel «Änderungen vorbehalten».', 'theater-auw' ) ); ?>
</table>

<h2><?php esc_html_e( 'Auf die Seite setzen', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:76ch">
	<?php esc_html_e( 'Auf der Seite «Speisekarte» einen Shortcode-Block einfügen. Ist die Seite mit dem Divi-Builder gebaut, geht auch ein Text- oder Code-Modul — dann aber die alten Module mit der abgetippten Karte entfernen, sonst steht sie zweimal da.', 'theater-auw' ); ?>
</p>
<p><textarea readonly rows="1" class="large-text code" onclick="this.select()">[theater_auw_karte]</textarea></p>
