<?php
/**
 * Hinweis auf der Hauptseite: «Sponsoren gesucht — zur Sponsoren-Anmeldung».
 *
 * Erscheint als eigene Sektion nach «Weiteres», solange die Anmeldung offen
 * ist. Nach Ablauf der Frist verschwindet sie von selbst — niemand muss am
 * Tag nach dem Stichtag daran denken, den Hinweis abzuschalten.
 *
 * @var array $ctx
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;

if ( empty( $settings['anmeldung_enabled'] ) || ! class_exists( 'Tha_Anmeldung' ) ) {
	return;
}
$url = Tha_Anmeldung::signup_url( $settings );
if ( ! $url || ! Tha_Anmeldung::is_open( $settings ) ) {
	return;
}
?>
<section class="tha-section tha-section--stage tha-section--anmeldung" id="anmeldung<?php echo esc_attr( $ctx['uid'] ); ?>">
	<div class="tha-wrap tha-anmeldung-cta">
		<div class="tha-anmeldung-cta__copy">
			<p class="tha-kicker tha-kicker--onstage"><?php esc_html_e( 'Sponsoren gesucht', 'theater-auw' ); ?></p>
			<h2 class="tha-h2 tha-h2--onstage"><?php echo esc_html( $settings['anmeldung_kicker'] ? $settings['anmeldung_kicker'] : __( 'Premierenabend- & Spenderpakete', 'theater-auw' ) ); ?></h2>
			<p class="tha-anmeldung-cta__meta">
				<?php
				$frist = Tha_Anmeldung::deadline_label( $settings );
				if ( '' !== $frist ) {
					printf(
						/* translators: %s: Datum, z.B. «8. Oktober 2026» */
						esc_html__( 'Gold, Silber und Spenderpakete — mit einem Platz am Premierenabend. Anmeldung bis %s.', 'theater-auw' ),
						esc_html( $frist )
					);
				} else {
					esc_html_e( 'Gold, Silber und Spenderpakete — mit einem Platz am Premierenabend.', 'theater-auw' );
				}
				?>
			</p>
		</div>
		<a class="tha-btn tha-btn--primary tha-btn--lg" href="<?php echo esc_url( $url ); ?>">
			<?php esc_html_e( 'Zur Sponsoren-Anmeldung', 'theater-auw' ); ?>
		</a>
	</div>
</section>