<?php
/**
 * Schlanker Abschluss statt zweitem Footer — der Footer des Themes bleibt
 * darunter stehen.
 *
 * @var array $ctx
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;

if ( ! $settings['contact_email'] ) {
	return;
}
?>
<section class="tha-section tha-section--contact" id="kontakt<?php echo esc_attr( $ctx['uid'] ); ?>">
	<div class="tha-wrap tha-contact">
		<p class="tha-contact__text">
			<?php
			printf(
				/* translators: %d: Gründungsjahr */
				esc_html__( 'Die Theatergruppe des STV Auw. Jeden Herbst auf der Bühne — seit %d.', 'theater-auw' ),
				(int) $settings['founded_year']
			);
			?>
		</p>
		<p class="tha-contact__mail">
			<a href="mailto:<?php echo esc_attr( $settings['contact_email'] ); ?>"><?php echo esc_html( $settings['contact_email'] ); ?></a>
		</p>
		<p class="tha-contact__signoff"><?php esc_html_e( 'Herzliche Grüsse, Theater Auw', 'theater-auw' ); ?></p>
	</div>
</section>
