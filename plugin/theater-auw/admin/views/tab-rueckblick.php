<?php
/**
 * Tab — Rückblick.
 *
 * Vorher stand das an zwei Enden der Reiterleiste: die Quelle unter
 * «Navigation & Rückblick», die Ersatzliste unter «Bilder & Rückblick». Wer
 * einstellen wollte, was der Rückblick zeigt, musste beide öffnen und dann
 * raten, welche Einstellung gerade greift. Es ist ein Thema, also ein Reiter —
 * und die Ersatzliste steht direkt unter der Quelle, die sie ersetzt.
 *
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;
?>
<h2><?php esc_html_e( 'Woher die Jahrgänge kommen', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:74ch">
	<?php esc_html_e( 'Die Jahrgänge müssen nicht neu erfasst werden. Auf stvauw.ch liegen sie bereits als Portfolio-Beiträge vor — ein Beitrag pro Jahr, das Plakat als Beitragsbild, der Titel in der Form «2025 Karriere». Das Plugin liest diesen Bestand direkt.', 'theater-auw' ); ?>
</p>

<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><label for="tha-rueckblick_source"><?php esc_html_e( 'Quelle', 'theater-auw' ); ?></label></th>
		<td>
			<select id="tha-rueckblick_source" name="<?php echo esc_attr( Tha_Admin::name( 'rueckblick_source' ) ); ?>">
				<option value="auto" <?php selected( 'auto', $settings['rueckblick_source'] ); ?>><?php esc_html_e( 'Aus vorhandenen Beiträgen (empfohlen)', 'theater-auw' ); ?></option>
				<option value="manual" <?php selected( 'manual', $settings['rueckblick_source'] ); ?>><?php esc_html_e( 'Von Hand gepflegte Liste unten', 'theater-auw' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'Findet das Plugin keine passenden Beiträge, nimmt es automatisch die Liste weiter unten — die Sektion verschwindet also nie einfach.', 'theater-auw' ); ?></p>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="tha-rueckblick_post_type"><?php esc_html_e( 'Beitragstyp', 'theater-auw' ); ?></label></th>
		<td>
			<?php $types = get_post_types( array( 'public' => true ), 'objects' ); ?>
			<select id="tha-rueckblick_post_type" name="<?php echo esc_attr( Tha_Admin::name( 'rueckblick_post_type' ) ); ?>">
				<?php foreach ( $types as $type ) : ?>
					<option value="<?php echo esc_attr( $type->name ); ?>" <?php selected( $type->name, $settings['rueckblick_post_type'] ); ?>>
						<?php echo esc_html( $type->labels->singular_name . ' (' . $type->name . ')' ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( 'Bei Divi heisst der Portfolio-Typ «project». Das ist der Standard.', 'theater-auw' ); ?></p>
		</td>
	</tr>

	<?php
	Tha_Admin::text_field( 'rueckblick_count', $settings['rueckblick_count'], __( 'Wie viele Jahrgänge', 'theater-auw' ), __( 'Die neuesten so vielen Jahre werden gezeigt.', 'theater-auw' ), 'number' );
	Tha_Admin::text_field( 'rueckblick_archive_url', $settings['rueckblick_archive_url'], __( 'Link «Ganzer Rückblick»', 'theater-auw' ), __( 'Zum Beispiel die Übersichtsseite mit allen Jahrgängen. Leer lassen blendet den Link aus.', 'theater-auw' ), 'url' );
	?>

	<tr>
		<th scope="row"><?php esc_html_e( 'Bildformat der Kacheln', 'theater-auw' ); ?></th>
		<td>
			<?php $rb_ratio = isset( $settings['rueckblick_ratio'] ) ? $settings['rueckblick_ratio'] : 'quer'; ?>
			<select name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[rueckblick_ratio]">
				<option value="quer" <?php selected( 'quer', $rb_ratio ); ?>><?php esc_html_e( 'Querformat 3:2 (empfohlen)', 'theater-auw' ); ?></option>
				<option value="hoch" <?php selected( 'hoch', $rb_ratio ); ?>><?php esc_html_e( 'Hochformat 3:4 — für Plakate', 'theater-auw' ); ?></option>
				<option value="auto" <?php selected( 'auto', $rb_ratio ); ?>><?php esc_html_e( 'Wie das Bild — nichts wird beschnitten', 'theater-auw' ); ?></option>
			</select>
			<p class="description">
				<?php esc_html_e( 'Alle Kacheln bekommen dasselbe Format, damit das Raster ruhig bleibt; was nicht hineinpasst, wird beschnitten. Sind im Portfolio Szenenfotos hinterlegt, ist Querformat richtig — Hochformat würde davon die halbe Bildbreite wegschneiden. Liegen dort abfotografierte Plakate, gilt das Umgekehrte.', 'theater-auw' ); ?>
			</p>
			<p class="description">
				<?php esc_html_e( '«Wie das Bild» schneidet nie etwas ab, dafür werden die Kacheln unterschiedlich hoch.', 'theater-auw' ); ?>
			</p>
		</td>
	</tr>
</table>

<h2><?php esc_html_e( 'Ersatzliste', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:74ch">
	<?php esc_html_e( 'Gilt, wenn oben «Von Hand gepflegte Liste» gewählt ist — und als Rückfallebene, wenn es keine passenden Beiträge gibt. Ganz leer lassen entfernt den Abschnitt von der Seite.', 'theater-auw' ); ?>
</p>

<table class="widefat tha-repeat" data-tha-repeat="rueckblick">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Jahr', 'theater-auw' ); ?></th>
			<th><?php esc_html_e( 'Stück', 'theater-auw' ); ?></th>
			<th><?php esc_html_e( 'Bild', 'theater-auw' ); ?></th>
			<th><?php esc_html_e( 'Link', 'theater-auw' ); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody data-tha-repeat-body>
		<?php foreach ( $settings['rueckblick'] as $i => $row ) : ?>
			<tr data-tha-repeat-row>
				<td><input type="text" size="6" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[rueckblick][<?php echo (int) $i; ?>][year]" value="<?php echo esc_attr( $row['year'] ); ?>"></td>
				<td><input type="text" size="16" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[rueckblick][<?php echo (int) $i; ?>][title]" value="<?php echo esc_attr( $row['title'] ); ?>"></td>
				<td class="tha-media-cell" data-tha-media>
					<input type="hidden" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[rueckblick][<?php echo (int) $i; ?>][image_id]" value="<?php echo esc_attr( $row['image_id'] ); ?>">
					<div class="tha-media-field__preview tha-media-field__preview--sm">
						<?php
						$src = $row['image_id'] ? wp_get_attachment_image_url( $row['image_id'], 'thumbnail' ) : '';
						if ( $src ) :
							?>
							<img src="<?php echo esc_url( $src ); ?>" alt="">
						<?php endif; ?>
					</div>
					<button type="button" class="button button-small" data-tha-media-pick><?php esc_html_e( 'Wählen', 'theater-auw' ); ?></button>
					<button type="button" class="button-link" data-tha-media-clear><?php esc_html_e( 'Weg', 'theater-auw' ); ?></button>
				</td>
				<td><input type="url" size="18" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[rueckblick][<?php echo (int) $i; ?>][url]" value="<?php echo esc_attr( $row['url'] ); ?>" placeholder="<?php esc_attr_e( 'optional', 'theater-auw' ); ?>"></td>
				<td><button type="button" class="button-link tha-drop" data-tha-repeat-drop><?php esc_html_e( 'Entfernen', 'theater-auw' ); ?></button></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<p><button type="button" class="button" data-tha-repeat-add><?php esc_html_e( 'Jahr hinzufügen', 'theater-auw' ); ?></button></p>
