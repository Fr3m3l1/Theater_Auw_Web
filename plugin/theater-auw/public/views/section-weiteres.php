<?php
/**
 * Rund um die Vorstellung: Essen, Tombola, Preise.
 *
 * @var array $ctx
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;
?>
<section class="tha-section tha-section--page" id="weiteres<?php echo esc_attr( $ctx['uid'] ); ?>">
	<div class="tha-wrap">
		<div class="tha-head">
			<p class="tha-kicker"><?php esc_html_e( 'Rund um die Vorstellung', 'theater-auw' ); ?></p>
			<h2 class="tha-h2"><?php esc_html_e( 'Weiteres', 'theater-auw' ); ?></h2>
		</div>

		<?php
		// Fällt die Preis-Karte weg, wären drei Spalten für zwei Karten zu
		// weit — das Raster richtet sich nach dem, was tatsächlich da ist.
		$karten = 1;
		if ( $settings['tombola_note'] ) {
			$karten++;
		}
		if ( ! Tha_Settings::has_prices( $settings ) ) {
			$karten++;
		}
		?>
		<div class="tha-grid tha-grid--cards" style="--tha-cols:<?php echo (int) $karten; ?>">
			<div class="tha-card">
				<h3 class="tha-h3"><?php esc_html_e( 'Essen', 'theater-auw' ); ?></h3>
				<p><?php echo esc_html( $settings['food_note'] ); ?></p>
				<p class="tha-note">
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: von, 2: bis */
							__( 'Abendaufführungen: %1$s bis %2$s Uhr.', 'theater-auw' ),
							$settings['food_from'],
							$settings['food_to']
						)
					);
					?>
				</p>
			</div>

			<?php if ( $settings['tombola_note'] ) : ?>
				<div class="tha-card">
					<h3 class="tha-h3"><?php esc_html_e( 'Tombola', 'theater-auw' ); ?></h3>
					<p><?php echo esc_html( $settings['tombola_note'] ); ?></p>
					<?php
					/**
					 * Das einzige verbliebene Bild aus dem Programmheft.
					 *
					 * Bei Eintritten und Speisekarte stand hier einmal
					 * dasselbe — die sind heute als Zeilen erfasst und ein
					 * Scan daneben wäre der zweite, schlechtere Ort. Die
					 * Tombolapreise haben keine solche Entsprechung: dort ist
					 * das Foto selbst der Inhalt.
					 */
					$tombola_id = absint( $settings['tombola_image_id'] );
					if ( $tombola_id && function_exists( 'wp_get_attachment_image' ) ) :
						$tombola_img = wp_get_attachment_image(
							$tombola_id,
							'medium',
							false,
							array( 'loading' => 'lazy', 'alt' => __( 'Tombola', 'theater-auw' ) )
						);
						if ( $tombola_img ) :
							?>
							<a class="tha-infoimg" href="<?php echo esc_url( wp_get_attachment_image_url( $tombola_id, 'full' ) ); ?>" target="_blank" rel="noopener">
								<?php echo $tombola_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span class="tha-infoimg__hint"><?php esc_html_e( 'Ansehen', 'theater-auw' ); ?></span>
							</a>
							<?php
						endif;
					endif;
					?>
				</div>
			<?php endif; ?>

			<?php
			// Diese Karte nur, solange es KEINE erfassten Preise gibt. Sonst
			// stünde neben den echten Beträgen im Abschnitt «Gut zu wissen»
			// noch eine zweite, vagere Preisangabe.
			if ( ! Tha_Settings::has_prices( $settings ) ) :
				?>
				<div class="tha-card">
					<h3 class="tha-h3"><?php esc_html_e( 'Preise & Zahlung', 'theater-auw' ); ?></h3>
					<p class="tha-tags">
						<span class="tha-tag"><?php esc_html_e( 'Bargeld', 'theater-auw' ); ?></span>
						<span class="tha-tag"><?php esc_html_e( 'Karte', 'theater-auw' ); ?></span>
						<span class="tha-tag"><?php esc_html_e( 'Twint', 'theater-auw' ); ?></span>
					</p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
