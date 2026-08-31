<?php
/**
 * Tab — Preise & Infos.
 *
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;
?>
<h2><?php esc_html_e( 'Der Abschnitt auf der Seite', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:76ch">
	<?php esc_html_e( 'Ein eigener Abschnitt auf der Seite mit Eintrittspreisen, Platz-Layout und Links zu Speise- und Getränkekarte. Preise stehen hier als echte Zeilen und nicht als Scan — ein Bild kann niemand vorlesen lassen, niemand durchsuchen und auf dem Handy schlecht entziffern.', 'theater-auw' ); ?>
</p>

<table class="form-table" role="presentation">
	<?php
	Tha_Admin::text_field( 'details_title', $settings['details_title'], __( 'Überschrift', 'theater-auw' ) );
	Tha_Admin::textarea_field( 'details_intro', $settings['details_intro'], __( 'Einleitung', 'theater-auw' ), __( 'Optional, steht über den Angaben.', 'theater-auw' ) );
	?>
</table>

<h2><?php esc_html_e( 'Eintrittspreise', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:76ch">
	<?php esc_html_e( 'Mehrere Zeilen mit derselben Gruppe werden zusammengefasst — «Vorpremiere» steht dann einmal da, darunter Kinder und Erwachsene, so wie im Programmheft. Zeilen ohne Betrag werden nicht angezeigt.', 'theater-auw' ); ?>
</p>

<table class="widefat tha-repeat" data-tha-repeat="prices">
	<thead>
		<tr>
			<th style="width:22%"><?php esc_html_e( 'Gruppe', 'theater-auw' ); ?></th>
			<th style="width:24%"><?php esc_html_e( 'Bezeichnung', 'theater-auw' ); ?></th>
			<th style="width:16%"><?php esc_html_e( 'Betrag', 'theater-auw' ); ?></th>
			<th><?php esc_html_e( 'Zusatz', 'theater-auw' ); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody data-tha-repeat-body>
		<?php foreach ( $settings['prices'] as $i => $row ) : ?>
			<tr data-tha-repeat-row>
				<td><input type="text" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[prices][<?php echo (int) $i; ?>][group]" value="<?php echo esc_attr( isset( $row['group'] ) ? $row['group'] : '' ); ?>" placeholder="<?php esc_attr_e( 'Vorpremiere', 'theater-auw' ); ?>"></td>
				<td><input type="text" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[prices][<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr( $row['label'] ); ?>" placeholder="<?php esc_attr_e( 'Erwachsene', 'theater-auw' ); ?>"></td>
				<td><input type="text" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[prices][<?php echo (int) $i; ?>][amount]" value="<?php echo esc_attr( $row['amount'] ); ?>" placeholder="Fr. 22.—"></td>
				<td><input type="text" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[prices][<?php echo (int) $i; ?>][note]" value="<?php echo esc_attr( $row['note'] ); ?>" placeholder="<?php esc_attr_e( 'z.B. bis 16 Jahre', 'theater-auw' ); ?>"></td>
				<td><button type="button" class="button-link tha-drop" data-tha-repeat-drop><?php esc_html_e( 'Entfernen', 'theater-auw' ); ?></button></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<p><button type="button" class="button" data-tha-repeat-add><?php esc_html_e( 'Preiszeile hinzufügen', 'theater-auw' ); ?></button></p>

<table class="form-table" role="presentation">
	<?php Tha_Admin::text_field( 'price_hint', $settings['price_hint'], __( 'Hinweis unter den Preisen', 'theater-auw' ), __( 'Zum Beispiel «Kassaöffnung eine Stunde vor Vorstellungsbeginn».', 'theater-auw' ) ); ?>
</table>

<h2><?php esc_html_e( 'Kassaöffnung', 'theater-auw' ); ?></h2>

<table class="widefat tha-repeat" data-tha-repeat="openings">
	<thead>
		<tr>
			<th style="width:40%"><?php esc_html_e( 'Bezeichnung', 'theater-auw' ); ?></th>
			<th><?php esc_html_e( 'Zeit', 'theater-auw' ); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody data-tha-repeat-body>
		<?php
		$openings = $settings['openings'] ? $settings['openings'] : array( array( 'label' => '', 'time' => '' ) );
		foreach ( $openings as $i => $row ) :
			?>
			<tr data-tha-repeat-row>
				<td><input type="text" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[openings][<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr( $row['label'] ); ?>" placeholder="<?php esc_attr_e( 'Abendaufführungen', 'theater-auw' ); ?>"></td>
				<td><input type="text" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[openings][<?php echo (int) $i; ?>][time]" value="<?php echo esc_attr( $row['time'] ); ?>" placeholder="<?php esc_attr_e( 'ab 18.30 Uhr', 'theater-auw' ); ?>"></td>
				<td><button type="button" class="button-link tha-drop" data-tha-repeat-drop><?php esc_html_e( 'Entfernen', 'theater-auw' ); ?></button></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<p><button type="button" class="button" data-tha-repeat-add><?php esc_html_e( 'Zeile hinzufügen', 'theater-auw' ); ?></button></p>

<h2><?php esc_html_e( 'Platz-Layout', 'theater-auw' ); ?></h2>
<p class="description">
	<?php esc_html_e( 'Den Saalplan zeichnet das Plugin selbst — Plätze 101–550 in fünf Blöcken. Das ist besser als ein Scan: die Nummern bleiben auf dem Handy lesbar und lassen sich vorlesen. Nur wer umbaut, hinterlegt hier ein Bild; es ersetzt den gezeichneten Plan.', 'theater-auw' ); ?>
</p>
<table class="form-table" role="presentation">
	<?php
	Tha_Admin::text_field( 'seatmap_title', $settings['seatmap_title'], __( 'Überschrift', 'theater-auw' ) );
	Tha_Admin::text_field(
		'seatmap_color',
		$settings['seatmap_color'],
		__( 'Farbe der Bühnenleiste', 'theater-auw' ),
		__( 'Leer lassen für das Theaterrot. Sonst ein Farbwert wie #e0762a — zum Beispiel die Farbe des Programmhefts dieses Jahrgangs.', 'theater-auw' )
	);
	Tha_Admin::textarea_field( 'seatmap_note', $settings['seatmap_note'], __( 'Hinweis', 'theater-auw' ), __( 'Zum Beispiel welche Reihen zu Kategorie A gehören.', 'theater-auw' ) );
	Tha_Admin::image_field( 'seatmap_image_id', $settings['seatmap_image_id'], __( 'Bild statt Plan', 'theater-auw' ) );
	?>
</table>

<h2><?php esc_html_e( 'Zum Nachlesen', 'theater-auw' ); ?></h2>
<p class="description">
	<?php esc_html_e( 'Links auf Speisekarte, Getränkekarte oder Programmheft. Entweder eine Adresse eintragen oder eine Datei aus der Mediathek wählen.', 'theater-auw' ); ?>
</p>

<table class="widefat tha-repeat" data-tha-repeat="documents">
	<thead>
		<tr>
			<th style="width:34%"><?php esc_html_e( 'Beschriftung', 'theater-auw' ); ?></th>
			<th><?php esc_html_e( 'Adresse', 'theater-auw' ); ?></th>
			<th style="width:22%"><?php esc_html_e( 'oder Datei', 'theater-auw' ); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody data-tha-repeat-body>
		<?php
		$docs = $settings['documents'] ? $settings['documents'] : array( array( 'label' => '', 'url' => '', 'file_id' => 0 ) );
		foreach ( $docs as $i => $row ) :
			?>
			<tr data-tha-repeat-row>
				<td><input type="text" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[documents][<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr( $row['label'] ); ?>" placeholder="<?php esc_attr_e( 'Speisekarte', 'theater-auw' ); ?>"></td>
				<td><input type="url" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[documents][<?php echo (int) $i; ?>][url]" value="<?php echo esc_attr( $row['url'] ); ?>" placeholder="https://"></td>
				<td class="tha-media-cell" data-tha-media>
					<input type="hidden" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[documents][<?php echo (int) $i; ?>][file_id]" value="<?php echo esc_attr( (int) $row['file_id'] ); ?>">
					<div class="tha-media-field__preview tha-media-field__preview--sm">
						<?php $src = $row['file_id'] ? wp_get_attachment_image_url( (int) $row['file_id'], 'thumbnail' ) : ''; ?>
						<?php if ( $src ) : ?><img src="<?php echo esc_url( $src ); ?>" alt=""><?php endif; ?>
					</div>
					<button type="button" class="button button-small" data-tha-media-pick><?php esc_html_e( 'Wählen', 'theater-auw' ); ?></button>
					<button type="button" class="button-link" data-tha-media-clear><?php esc_html_e( 'Weg', 'theater-auw' ); ?></button>
				</td>
				<td><button type="button" class="button-link tha-drop" data-tha-repeat-drop><?php esc_html_e( 'Entfernen', 'theater-auw' ); ?></button></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<p><button type="button" class="button" data-tha-repeat-add><?php esc_html_e( 'Link hinzufügen', 'theater-auw' ); ?></button></p>
