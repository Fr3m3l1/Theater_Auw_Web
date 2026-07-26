<?php
/**
 * Spieldaten — das zentrale wiederkehrende Objekt, in drei Phasen sichtbar.
 * Ohne JavaScript ist das eine schlichte, vollständige Liste.
 *
 * @var array $ctx
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;

$shows = $ctx['shows'];
if ( ! $shows ) {
	return;
}
?>
<section class="tha-section tha-section--page" id="daten<?php echo esc_attr( $ctx['uid'] ); ?>">
	<div class="tha-wrap">
		<div class="tha-head">
			<p class="tha-kicker"><?php esc_html_e( 'Aufführungsdaten', 'theater-auw' ); ?></p>
			<h2 class="tha-h2"><?php esc_html_e( 'Spieldaten', 'theater-auw' ); ?></h2>
		</div>

		<div class="tha-narrow">
			<ul class="tha-dates">
				<?php foreach ( $shows as $show ) : ?>
					<?php Tha_Renderer::part( 'partial-date-row', $ctx, array( 'show' => $show ) ); ?>
				<?php endforeach; ?>
			</ul>

			<p class="tha-note">
				<?php
				$bits = array();
				foreach ( $shows as $show ) {
					if ( '' !== $show['note'] ) {
						$bits[] = $show['note'];
						break;
					}
				}
				echo esc_html(
					sprintf(
						/* translators: 1: Türöffnung, 2: Ort */
						__( 'Türöffnung %1$s Uhr · %2$s', 'theater-auw' ),
						$settings['doors_time'],
						$settings['venue_name']
					)
				);
				if ( $bits ) {
					echo ' · ' . esc_html( sprintf( __( 'Premiere mit %s', 'theater-auw' ), $bits[0] ) );
				}
				?>
			</p>

			<?php if ( $settings['venue_maps_url'] ) : ?>
				<p class="tha-note">
					<a href="<?php echo esc_url( $settings['venue_maps_url'] ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'Anfahrt und Parkplätze', 'theater-auw' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
	</div>
</section>
