<?php
/**
 * Tab 3 — Spieldaten. Wiederholbare Zeilen.
 *
 * Vergangene Daten werden auf der Website automatisch als «Vorbei» angezeigt —
 * der Status muss dafür nicht nachgeführt werden.
 *
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;

$statuses = array(
	'offen'       => __( 'Plätze frei', 'theater-auw' ),
	'wenige'      => __( 'Wenige Plätze', 'theater-auw' ),
	'ausverkauft' => __( 'Ausverkauft', 'theater-auw' ),
	'vorbei'      => __( 'Vorbei', 'theater-auw' ),
);
$rows = $settings['dates'];
?>
<h2><?php esc_html_e( 'Spieldaten', 'theater-auw' ); ?></h2>
<p class="description">
	<?php esc_html_e( 'Die Reihenfolge wird beim Speichern automatisch nach Datum sortiert. Vergangene Vorstellungen erscheinen von selbst als «Vorbei».', 'theater-auw' ); ?>
</p>

<table class="widefat tha-repeat" data-tha-repeat="dates">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Datum', 'theater-auw' ); ?></th>
			<th><?php esc_html_e( 'Zeit', 'theater-auw' ); ?></th>
			<th><?php esc_html_e( 'Bezeichnung', 'theater-auw' ); ?></th>
			<th><?php esc_html_e( 'Notiz', 'theater-auw' ); ?></th>
			<th><?php esc_html_e( 'Status', 'theater-auw' ); ?></th>
			<th><?php esc_html_e( 'Eigener Ticketlink', 'theater-auw' ); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody data-tha-repeat-body>
		<?php foreach ( $rows as $i => $row ) : ?>
			<tr data-tha-repeat-row>
				<td><input type="date" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[dates][<?php echo (int) $i; ?>][date]" value="<?php echo esc_attr( $row['date'] ); ?>"></td>
				<td><input type="text" size="6" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[dates][<?php echo (int) $i; ?>][time]" value="<?php echo esc_attr( $row['time'] ); ?>" placeholder="20.00"></td>
				<td><input type="text" size="12" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[dates][<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr( $row['label'] ); ?>" placeholder="<?php esc_attr_e( 'Premiere', 'theater-auw' ); ?>"></td>
				<td><input type="text" size="14" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[dates][<?php echo (int) $i; ?>][note]" value="<?php echo esc_attr( $row['note'] ); ?>" placeholder="<?php esc_attr_e( 'Sponsoren-Abend', 'theater-auw' ); ?>"></td>
				<td>
					<select name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[dates][<?php echo (int) $i; ?>][status]">
						<?php foreach ( $statuses as $val => $label ) : ?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $val, $row['status'] ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
				<td><input type="url" size="18" name="<?php echo esc_attr( Tha_Settings::OPTION ); ?>[dates][<?php echo (int) $i; ?>][ticket_url]" value="<?php echo esc_attr( $row['ticket_url'] ); ?>" placeholder="<?php esc_attr_e( 'optional', 'theater-auw' ); ?>"></td>
				<td><button type="button" class="button-link tha-drop" data-tha-repeat-drop><?php esc_html_e( 'Entfernen', 'theater-auw' ); ?></button></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<p><button type="button" class="button" data-tha-repeat-add><?php esc_html_e( 'Vorstellung hinzufügen', 'theater-auw' ); ?></button></p>
