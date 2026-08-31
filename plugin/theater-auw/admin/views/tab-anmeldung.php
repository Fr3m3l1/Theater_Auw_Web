<?php
/**
 * Tab — Sponsoring. Die Anmeldeseite, der Hinweis auf der Hauptseite und
 * die Frist, die beides automatisch schliesst.
 *
 * Die Anmeldeseite («Anmeldung Sponsoring») blieb als letzte Seite im alten
 * Design, weil sie ein echtes Formular trägt: sie nimmt Anmeldungen zu den
 * Premierenabend- und Spenderpaketen entgegen. Sie lässt sich nicht in den
 * Shortcode-Rahmen einschliessen, ohne dem Formular die Theme-Stile zu
 * nehmen — deshalb gestaltet das Plugin sie von aussen.
 *
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;

$offen = Tha_Anmeldung::is_open( $settings );
$frist = Tha_Anmeldung::deadline_label( $settings );
?>
<h2><?php esc_html_e( 'Sponsoring-Anmeldung', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:74ch">
	<?php esc_html_e( 'Die Seite «Anmeldung Sponsoring» bleibt wie sie ist — mit ihrem Formular, ihren Paket-Preisen und dem Menü. Das Plugin setzt ihr nur den dunklen Seitenkopf vor, übernimmt die Theater-Schrift und blendet das Formular nach Ablauf der Frist automatisch aus. Du musst am Stichtag nichts abschalten.', 'theater-auw' ); ?>
</p>

<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><?php esc_html_e( 'Anmeldung', 'theater-auw' ); ?></th>
		<td>
			<input type="hidden" name="<?php echo esc_attr( Tha_Admin::name( 'anmeldung_present' ) ); ?>" value="1">
			<label>
				<input type="checkbox" name="<?php echo esc_attr( Tha_Admin::name( 'anmeldung_enabled' ) ); ?>" value="1" <?php checked( ! empty( $settings['anmeldung_enabled'] ) ); ?>>
				<?php esc_html_e( 'Anmeldung läuft — Hinweis auf der Hauptseite zeigen', 'theater-auw' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Der Hinweis «Sponsoren gesucht» erscheint auf der Hauptseite und verschwindet automatisch, sobald die Frist unten abgelaufen ist.', 'theater-auw' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="tha-anmeldung_page_id"><?php esc_html_e( 'Anmeldeseite', 'theater-auw' ); ?></label></th>
		<td>
			<?php
			wp_dropdown_pages(
				array(
					'name'              => Tha_Admin::name( 'anmeldung_page_id' ),
					'id'                => 'tha-anmeldung_page_id',
					'selected'          => (int) $settings['anmeldung_page_id'],
					'show_option_none'  => __( '— keine —', 'theater-auw' ),
					'option_none_value' => 0,
				)
			);
			?>
			<p class="description">
				<?php esc_html_e( 'Die Seite, die den Seitenkopf und das Formular tragen soll — üblicherweise «Anmeldung Sponsoring».', 'theater-auw' ); ?>
			</p>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="tha-anmeldung_title"><?php esc_html_e( 'Titel des Seitenkopfs', 'theater-auw' ); ?></label></th>
		<td>
			<input type="text" class="regular-text" id="tha-anmeldung_title"
				name="<?php echo esc_attr( Tha_Admin::name( 'anmeldung_title' ) ); ?>"
				value="<?php echo esc_attr( $settings['anmeldung_title'] ); ?>">
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="tha-anmeldung_kicker"><?php esc_html_e( 'Kicker', 'theater-auw' ); ?></label></th>
		<td>
			<input type="text" class="regular-text" id="tha-anmeldung_kicker"
				name="<?php echo esc_attr( Tha_Admin::name( 'anmeldung_kicker' ) ); ?>"
				value="<?php echo esc_attr( $settings['anmeldung_kicker'] ); ?>">
			<p class="description"><?php esc_html_e( 'Die kleine rote Zeile über dem Titel — erscheint auch als Überschrift des Hinweises auf der Hauptseite.', 'theater-auw' ); ?></p>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="tha-anmeldung_lead"><?php esc_html_e( 'Vorspann', 'theater-auw' ); ?></label></th>
		<td>
			<textarea rows="3" class="large-text" id="tha-anmeldung_lead"
				name="<?php echo esc_attr( Tha_Admin::name( 'anmeldung_lead' ) ); ?>"><?php echo esc_textarea( $settings['anmeldung_lead'] ); ?></textarea>
			<p class="description"><?php esc_html_e( 'Der Satz unter dem Titel des Seitenkopfs.', 'theater-auw' ); ?></p>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="tha-anmeldung_deadline"><?php esc_html_e( 'Anmeldefrist', 'theater-auw' ); ?></label></th>
		<td>
			<input type="date" id="tha-anmeldung_deadline"
				name="<?php echo esc_attr( Tha_Admin::name( 'anmeldung_deadline' ) ); ?>"
				value="<?php echo esc_attr( $settings['anmeldung_deadline'] ); ?>">
			<p class="description">
				<?php
				if ( $offen && '' !== $frist ) {
					printf(
						/* translators: %s: Datum, z.B. «8. Oktober 2026» */
						esc_html__( 'Die Anmeldung ist offen — bis %s.', 'theater-auw' ),
						esc_html( $frist )
					);
				} elseif ( ! $offen ) {
					esc_html_e( 'Die Frist ist abgelaufen: Formular und Hinweis sind ausgeblendet. Ein neues Datum hier öffnet die Anmeldung wieder.', 'theater-auw' );
				}
				?>
			</p>
			<p class="description"><?php esc_html_e( 'Wer bis zu diesem Tag anmeldet, ist dabei. Ohne Datum bleibt die Anmeldung offen.', 'theater-auw' ); ?></p>
		</td>
	</tr>
</table>