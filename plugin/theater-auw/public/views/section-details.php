<?php
/**
 * Detaillierte Informationen — Eintrittspreise, Platz-Layout, Dokumente.
 *
 * Die Preise stehen hier als echte Zeilen, nicht als Scan. Ein Bild kann
 * niemand vorlesen lassen, niemand durchsuchen und auf dem Handy niemand
 * entziffern. Der Saalplan dagegen IST ein Bild — und ändert sich nie.
 *
 * @var array $ctx
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;

// Nur Preiszeilen mit Betrag zeigen; die Vorlage bringt leere Zeilen mit.
$prices = array();
foreach ( (array) $settings['prices'] as $row ) {
	if ( '' !== trim( (string) $row['amount'] ) ) {
		$prices[] = $row;
	}
}

$openings = array();
foreach ( (array) $settings['openings'] as $row ) {
	if ( '' !== trim( (string) $row['time'] ) ) {
		$openings[] = $row;
	}
}

$seatmap = absint( $settings['seatmap_image_id'] );
$docs    = (array) $settings['documents'];

if ( ! $prices && ! $seatmap && ! $docs && ! $openings ) {
	return;
}
?>
<section class="tha-section tha-section--card" id="details<?php echo esc_attr( $ctx['uid'] ); ?>">
	<div class="tha-wrap">

		<div class="tha-head">
			<p class="tha-kicker"><?php esc_html_e( 'Bevor du kommst', 'theater-auw' ); ?></p>
			<h2 class="tha-h2"><?php echo esc_html( $settings['details_title'] ); ?></h2>
		</div>

		<?php if ( '' !== $settings['details_intro'] ) : ?>
			<div class="tha-prose"><p><?php echo wp_kses_post( $settings['details_intro'] ); ?></p></div>
		<?php endif; ?>

		<div class="tha-details">

			<?php if ( $prices ) : ?>
				<div class="tha-details__col">
					<h3 class="tha-h3"><?php esc_html_e( 'Eintritt', 'theater-auw' ); ?></h3>

					<?php
					// Nach Aufführungsart bündeln — und damit auf zwei Ebenen
					// bringen. Eine Art mit mehreren Preisen bekommt eine
					// Zwischenzeile, ihre Preise rücken darunter ein. Hat eine
					// Art nur EINEN Preis, wird daraus eine einzige Zeile auf
					// oberster Ebene: «Abendaufführung» mit «alle» darunter
					// wäre nur Geraune.
					//
					// Auf diesen Unterschied kommt es an. Standen beide Ebenen
					// gleich da, las sich «Abendaufführung» wie ein weiterer
					// Preis der Gruppe «Sonntag» darüber.
					$gruppen = array();
					foreach ( $prices as $row ) {
						$gruppen[ isset( $row['group'] ) ? $row['group'] : '' ][] = $row;
					}
					?>
					<div class="tha-prices">
						<?php foreach ( $gruppen as $name => $zeilen ) : ?>

							<?php if ( '' !== $name && count( $zeilen ) > 1 ) : ?>
								<?php
								// Bewusst ein <div>: Themes schreiben gern
								// `p { margin: 0 !important }` — dagegen kommt
								// keine Spezifität an, und die Zwischenzeile
								// klebte an der Zeile darüber. Am feindlichen
								// Theme in preview/hostile.html nachgestellt.
								?>
								<div class="tha-prices__group"><?php echo esc_html( $name ); ?></div>
								<dl class="tha-prices__list tha-prices__list--sub">
									<?php foreach ( $zeilen as $row ) : ?>
										<div class="tha-prices__row">
											<dt>
												<?php echo esc_html( $row['label'] ); ?>
												<?php if ( '' !== $row['note'] ) : ?>
													<span class="tha-prices__note"><?php echo esc_html( $row['note'] ); ?></span>
												<?php endif; ?>
											</dt>
											<dd><?php echo esc_html( $row['amount'] ); ?></dd>
										</div>
									<?php endforeach; ?>
								</dl>

							<?php else : ?>
								<dl class="tha-prices__list">
									<?php foreach ( $zeilen as $row ) : ?>
										<?php
										// Ohne Gruppe trägt die Bezeichnung die
										// Zeile; mit Gruppe steht die
										// Aufführungsart vorn und die
										// Bezeichnung wandert nach unten.
										$titel  = '' !== $name ? $name : $row['label'];
										$zusatz = '' !== $name
											? array_filter( array( $row['label'], $row['note'] ) )
											: array_filter( array( $row['note'] ) );
										?>
										<div class="tha-prices__row">
											<dt>
												<?php echo esc_html( $titel ); ?>
												<?php if ( $zusatz ) : ?>
													<span class="tha-prices__note"><?php echo esc_html( implode( ', ', $zusatz ) ); ?></span>
												<?php endif; ?>
											</dt>
											<dd><?php echo esc_html( $row['amount'] ); ?></dd>
										</div>
									<?php endforeach; ?>
								</dl>
							<?php endif; ?>

						<?php endforeach; ?>
					</div>

					<?php if ( '' !== $settings['price_hint'] ) : ?>
						<p class="tha-note"><?php echo esc_html( $settings['price_hint'] ); ?></p>
					<?php endif; ?>

					<?php if ( $openings ) : ?>
						<h3 class="tha-h3 tha-details__sub"><?php esc_html_e( 'Kassaöffnung', 'theater-auw' ); ?></h3>
						<div class="tha-prices tha-prices--times">
							<dl class="tha-prices__list">
								<?php foreach ( $openings as $row ) : ?>
									<div class="tha-prices__row">
										<dt><?php echo esc_html( $row['label'] ); ?></dt>
										<dd><?php echo esc_html( $row['time'] ); ?></dd>
									</div>
								<?php endforeach; ?>
							</dl>
						</div>
					<?php endif; ?>

					<p class="tha-tags">
						<span class="tha-tag"><?php esc_html_e( 'Bargeld', 'theater-auw' ); ?></span>
						<span class="tha-tag"><?php esc_html_e( 'Karte', 'theater-auw' ); ?></span>
						<span class="tha-tag"><?php esc_html_e( 'Twint', 'theater-auw' ); ?></span>
					</p>

					<?php if ( $docs ) : ?>
						<h3 class="tha-h3 tha-details__sub"><?php esc_html_e( 'Zum Nachlesen', 'theater-auw' ); ?></h3>
						<ul class="tha-docs">
							<?php foreach ( $docs as $doc ) : ?>
								<?php
								$url = $doc['url'];
								if ( '' === $url && ! empty( $doc['file_id'] ) ) {
									$url = wp_get_attachment_url( (int) $doc['file_id'] );
								}
								if ( ! $url ) {
									continue;
								}
								?>
								<li>
									<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">
										<?php Tha_Renderer::part( 'partial-icon-external', $ctx ); ?>
										<?php echo esc_html( $doc['label'] ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="tha-details__col tha-details__col--map">
				<h3 class="tha-h3"><?php echo esc_html( $settings['seatmap_title'] ); ?></h3>

				<?php if ( $seatmap ) : ?>
					<figure class="tha-seatmap">
						<a href="<?php echo esc_url( wp_get_attachment_image_url( $seatmap, 'full' ) ); ?>" target="_blank" rel="noopener">
							<?php
							// Der Saalplan wird nie beschnitten — sonst fehlen
							// genau die Randplätze, die jemand sucht.
							echo Tha_Renderer::image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								$seatmap,
								'large',
								$settings['seatmap_title'],
								false,
								''
							);
							?>
						</a>
						<?php if ( '' !== $settings['seatmap_note'] ) : ?>
							<figcaption class="tha-seatmap__note"><?php echo wp_kses_post( $settings['seatmap_note'] ); ?></figcaption>
						<?php endif; ?>
					</figure>

				<?php else : ?>
					<?php
					// Kein Bild hinterlegt: dann zeichnen wir den Saal selbst.
					// Das ist der Normalfall — ein Scan wäre in jeder Hinsicht
					// schlechter, siehe partial-saalplan.php.
					?>
					<figure class="tha-seatmap">
						<?php Tha_Renderer::part( 'partial-saalplan', $ctx ); ?>
						<?php if ( '' !== $settings['seatmap_note'] ) : ?>
							<figcaption class="tha-seatmap__note"><?php echo wp_kses_post( $settings['seatmap_note'] ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
