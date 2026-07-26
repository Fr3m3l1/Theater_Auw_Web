<?php
/**
 * Tab 1 — Phasen & Tickets. Das Herzstück: geplant oder von Hand geschaltet.
 *
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;

$sched = $settings['phase_schedule'];
?>

<h2><?php esc_html_e( 'Wie soll die Phase wechseln?', 'theater-auw' ); ?></h2>

<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><?php esc_html_e( 'Steuerung', 'theater-auw' ); ?></th>
		<td>
			<fieldset>
				<label class="tha-radio">
					<input type="radio" name="<?php echo esc_attr( Tha_Admin::name( 'phase_mode' ) ); ?>" value="auto"
						<?php checked( 'auto', $settings['phase_mode'] ); ?>>
					<span>
						<strong><?php esc_html_e( 'Nach Kalender (empfohlen)', 'theater-auw' ); ?></strong><br>
						<span class="description"><?php esc_html_e( 'Einmal im Sommer die drei Daten unten eintragen — die Seite schaltet danach von selbst weiter.', 'theater-auw' ); ?></span>
					</span>
				</label>
				<br>
				<label class="tha-radio">
					<input type="radio" name="<?php echo esc_attr( Tha_Admin::name( 'phase_mode' ) ); ?>" value="manual"
						<?php checked( 'manual', $settings['phase_mode'] ); ?>>
					<span>
						<strong><?php esc_html_e( 'Von Hand festlegen', 'theater-auw' ); ?></strong><br>
						<span class="description"><?php esc_html_e( 'Überschreibt den Kalender. Nützlich, wenn etwas kurzfristig anders läuft.', 'theater-auw' ); ?></span>
					</span>
				</label>
			</fieldset>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="tha-phase_manual"><?php esc_html_e( 'Feste Phase', 'theater-auw' ); ?></label></th>
		<td>
			<select id="tha-phase_manual" name="<?php echo esc_attr( Tha_Admin::name( 'phase_manual' ) ); ?>">
				<?php foreach ( Tha_Settings::PHASES as $p ) : ?>
					<option value="<?php echo esc_attr( $p ); ?>" <?php selected( $p, $settings['phase_manual'] ); ?>>
						<?php echo esc_html( Tha_Admin::phase_label( $p ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( 'Gilt nur, wenn oben «Von Hand festlegen» gewählt ist.', 'theater-auw' ); ?></p>
		</td>
	</tr>
</table>

<h2><?php esc_html_e( 'Der Fahrplan', 'theater-auw' ); ?></h2>
<p class="description">
	<?php esc_html_e( 'Ab diesen Zeitpunkten gilt die jeweilige Phase. Davor gilt automatisch «Vorbereitung».', 'theater-auw' ); ?>
</p>

<table class="form-table" role="presentation">
	<?php
	Tha_Admin::datetime_field(
		Tha_Settings::OPTION . '[phase_schedule][vorverkauf_start]',
		$sched['vorverkauf_start'],
		__( 'Vorverkauf ab', 'theater-auw' ),
		__( 'Ab hier zeigt die Seite die Vorverkaufs-Phase.', 'theater-auw' )
	);
	Tha_Admin::datetime_field(
		Tha_Settings::OPTION . '[phase_schedule][spielzeit_start]',
		$sched['spielzeit_start'],
		__( 'Spielzeit ab', 'theater-auw' ),
		__( 'Üblicherweise der Premierentag, 00:00 Uhr.', 'theater-auw' )
	);
	Tha_Admin::datetime_field(
		Tha_Settings::OPTION . '[phase_schedule][danke_start]',
		$sched['danke_start'],
		__( 'Danke ab', 'theater-auw' ),
		__( 'Üblicherweise der Tag nach der Derniere, 00:00 Uhr.', 'theater-auw' )
	);
	?>
</table>

<h2><?php esc_html_e( 'Ticketleo', 'theater-auw' ); ?></h2>
<p class="description">
	<?php esc_html_e( 'Tickets werden nie auf dieser Seite verkauft — jeder Button führt zu Ticketleo. Hier steht nur, wohin.', 'theater-auw' ); ?>
</p>

<table class="form-table" role="presentation">
	<?php
	Tha_Admin::text_field(
		'ticket_url',
		$settings['ticket_url'],
		__( 'Event-URL', 'theater-auw' ),
		__( 'Die Adresse der Veranstaltung bei Ticketleo, zum Beispiel <code>https://reservation.ticketleo.com/event/33310/karriere</code> (das war 2025). Muss mit <code>https://</code> beginnen.', 'theater-auw' ),
		'url'
	);
	Tha_Admin::datetime_field(
		Tha_Settings::OPTION . '[ticket_sale_start]',
		$settings['ticket_sale_start'],
		__( 'Verkaufsstart', 'theater-auw' ),
		__( 'Bis dahin zeigt die Vorverkaufs-Phase einen Countdown statt eines Kauf-Buttons.', 'theater-auw' )
	);
	?>
	<tr>
		<th scope="row"><label for="tha-ticket_cta_mode"><?php esc_html_e( 'Beschriftung', 'theater-auw' ); ?></label></th>
		<td>
			<select id="tha-ticket_cta_mode" name="<?php echo esc_attr( Tha_Admin::name( 'ticket_cta_mode' ) ); ?>">
				<option value="reservieren" <?php selected( 'reservieren', $settings['ticket_cta_mode'] ); ?>><?php esc_html_e( 'Plätze reservieren', 'theater-auw' ); ?></option>
				<option value="kaufen" <?php selected( 'kaufen', $settings['ticket_cta_mode'] ); ?>><?php esc_html_e( 'Tickets kaufen', 'theater-auw' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'Wählt bei Ticketleo nur reserviert und zahlt an der Abendkasse, passt «reservieren» besser.', 'theater-auw' ); ?></p>
		</td>
	</tr>
	<?php
	Tha_Admin::text_field(
		'ticket_note',
		$settings['ticket_note'],
		__( 'Hinweis im Dialog', 'theater-auw' ),
		__( 'Erscheint klein unter dem Erklärtext im Ticketleo-Hinweis.', 'theater-auw' )
	);
	?>
</table>
