<?php
/**
 * Das Stück. Die Leute werden mit Namen genannt — darauf ist der Verein stolz,
 * und das bleibt so.
 *
 * @var array $ctx
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;
?>
<section class="tha-section tha-section--card" id="stueck<?php echo esc_attr( $ctx['uid'] ); ?>">
	<div class="tha-wrap tha-split">
		<div>
			<div class="tha-head">
				<p class="tha-kicker">
					<?php
					printf(
						/* translators: %d: Saisonjahr */
						esc_html__( 'Theater %d', 'theater-auw' ),
						(int) $settings['season_year']
					);
					?>
				</p>
				<h2 class="tha-h2"><?php echo esc_html( tha_guillemets( $settings['play_title'] ) ); ?></h2>
			</div>

			<p class="tha-tags">
				<?php if ( $settings['play_genre'] ) : ?>
					<span class="tha-tag"><?php echo esc_html( $settings['play_genre'] ); ?></span>
				<?php endif; ?>
				<?php if ( $settings['play_author'] ) : ?>
					<span class="tha-tag"><?php echo esc_html( $settings['play_author'] ); ?></span>
				<?php endif; ?>
				<?php if ( $settings['play_director'] ) : ?>
					<span class="tha-tag"><?php echo esc_html( sprintf( __( 'Regie: %s', 'theater-auw' ), $settings['play_director'] ) ); ?></span>
				<?php endif; ?>
			</p>

			<div class="tha-prose">
				<?php
				foreach ( preg_split( '/\n\s*\n/', (string) $settings['play_synopsis'] ) as $para ) {
					$para = trim( $para );
					if ( '' !== $para ) {
						echo '<p>' . wp_kses_post( $para ) . '</p>';
					}
				}
				?>
			</div>
		</div>

		<div>
			<?php
			echo Tha_Renderer::image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$settings['stueck_image_id'],
				'large',
				__( 'Foto: Ensemble / Probenbild', 'theater-auw' ),
				false,
				'4 / 3'
			);
			?>
		</div>
	</div>
</section>
