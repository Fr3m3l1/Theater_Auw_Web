<?php
/**
 * Tab — Updates.
 *
 * Die Seite /theater/updates-2 stand wie die Speisekarte noch im alten Design:
 * ihr Inhalt lag in Divi-Modulen, wo das Plugin nichts von seinem Shortcode
 * sieht. Hier werden die Einträge erfasst; sortiert wird nicht von Hand.
 *
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;

// Eine leere Zeile zum Losschreiben, wenn noch nichts erfasst ist.
$rows = $settings['updates'] ? $settings['updates'] : array(
	array( 'date' => '', 'title' => '', 'text' => '', 'image_id' => 0, 'url' => '', 'link' => '' ),
);
?>
<h2><?php esc_html_e( 'Updates', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:76ch">
	<?php esc_html_e( 'Der Inhalt der Seite «Updates»: kurze Mitteilungen mit Datum. Die Reihenfolge musst du nicht pflegen — das Neueste steht immer oben, Einträge ohne Datum am Ende. Ein Eintrag ohne Überschrift und ohne Text wird verworfen.', 'theater-auw' ); ?>
</p>

<table class="form-table" role="presentation">
	<?php
	Tha_Admin::text_field( 'updates_title', $settings['updates_title'], __( 'Überschrift', 'theater-auw' ), __( 'Steht im Kopfbereich der Seite.', 'theater-auw' ) );
	Tha_Admin::text_field( 'updates_kicker', $settings['updates_kicker'], __( 'Kicker', 'theater-auw' ), __( 'Die kleine Zeile über der Überschrift.', 'theater-auw' ) );
	Tha_Admin::textarea_field( 'updates_intro', $settings['updates_intro'], __( 'Einleitung', 'theater-auw' ), __( 'Optional, steht über den Einträgen.', 'theater-auw' ) );
	?>
</table>

<h2><?php esc_html_e( 'Die Einträge', 'theater-auw' ); ?></h2>

<table class="widefat tha-repeat" data-tha-repeat="updates">
	<thead>
		<tr>
			<th style="width:11%"><?php esc_html_e( 'Datum', 'theater-auw' ); ?></th>
			<th style="width:20%"><?php esc_html_e( 'Überschrift', 'theater-auw' ); ?></th>
			<th><?php esc_html_e( 'Text', 'theater-auw' ); ?></th>
			<th style="width:14%"><?php esc_html_e( 'Bild', 'theater-auw' ); ?></th>
			<th style="width:18%"><?php esc_html_e( 'Link', 'theater-auw' ); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody data-tha-repeat-body>
		<?php foreach ( $rows as $i => $row ) : ?>
			<?php $row = wp_parse_args( (array) $row, array( 'date' => '', 'title' => '', 'text' => '', 'image_id' => 0, 'url' => '', 'link' => '' ) ); ?>
			<tr data-tha-repeat-row>
				<td><input type="date" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[updates][<?php echo (int) $i; ?>][date]" value="<?php echo esc_attr( $row['date'] ); ?>"></td>
				<td><input type="text" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[updates][<?php echo (int) $i; ?>][title]" value="<?php echo esc_attr( $row['title'] ); ?>" placeholder="<?php esc_attr_e( 'Die Rollen sind verteilt', 'theater-auw' ); ?>"></td>
				<td><textarea rows="3" class="large-text" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[updates][<?php echo (int) $i; ?>][text]"><?php echo esc_textarea( $row['text'] ); ?></textarea></td>
				<td class="tha-media-cell" data-tha-media>
					<input type="hidden" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[updates][<?php echo (int) $i; ?>][image_id]" value="<?php echo esc_attr( (int) $row['image_id'] ); ?>">
					<div class="tha-media-field__preview tha-media-field__preview--sm">
						<?php $src = $row['image_id'] ? wp_get_attachment_image_url( (int) $row['image_id'], 'thumbnail' ) : ''; ?>
						<?php if ( $src ) : ?><img src="<?php echo esc_url( $src ); ?>" alt=""><?php endif; ?>
					</div>
					<button type="button" class="button button-small" data-tha-media-pick><?php esc_html_e( 'Wählen', 'theater-auw' ); ?></button>
					<button type="button" class="button-link" data-tha-media-clear><?php esc_html_e( 'Weg', 'theater-auw' ); ?></button>
				</td>
				<td>
					<input type="url" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[updates][<?php echo (int) $i; ?>][url]" value="<?php echo esc_attr( $row['url'] ); ?>" placeholder="https://">
					<input type="text" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[updates][<?php echo (int) $i; ?>][link]" value="<?php echo esc_attr( $row['link'] ); ?>" placeholder="<?php esc_attr_e( 'Mehr dazu', 'theater-auw' ); ?>">
				</td>
				<td><button type="button" class="button-link tha-drop" data-tha-repeat-drop><?php esc_html_e( 'Entfernen', 'theater-auw' ); ?></button></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<p><button type="button" class="button" data-tha-repeat-add><?php esc_html_e( 'Eintrag hinzufügen', 'theater-auw' ); ?></button></p>

<h2><?php esc_html_e( 'Auf die Seite setzen', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:76ch">
	<?php esc_html_e( 'Auf der Seite «Updates» einen Shortcode-Block einfügen. Für einen Auszug auf einer anderen Seite — etwa die drei neuesten Meldungen — gibt es das Attribut «anzahl».', 'theater-auw' ); ?>
</p>
<p><textarea readonly rows="2" class="large-text code" onclick="this.select()">[theater_auw_updates]
[theater_auw_updates anzahl="3"]</textarea></p>
