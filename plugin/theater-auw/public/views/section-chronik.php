<?php
/**
 * Chronik — die abgeschlossenen Jahrgänge als Liste.
 *
 * Bewusst kein Kachelraster: für diese Jahre gibt es keine Plakate, dafür
 * echte Angaben — Gattung, Autor, Regie, Besetzungsstärke. Eine Liste zeigt
 * das alles, ein Bildraster hätte nichts zu zeigen.
 *
 * @var array $ctx
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;

$ch_args = isset( $ch_args ) ? $ch_args : array();
$ch_rows = Tha_Chronik::entries( $ch_args );

if ( ! $ch_rows ) {
	return;
}

$ch_title  = isset( $ch_title ) && '' !== $ch_title ? $ch_title : __( 'Chronik', 'theater-auw' );
$ch_kicker = isset( $ch_kicker ) ? $ch_kicker : __( 'Was wir gespielt haben', 'theater-auw' );
$ch_intro  = isset( $ch_intro ) ? $ch_intro : '';
// Bilder lassen sich abschalten: [theater_auw_chronik bilder="0"].
$ch_images = isset( $ch_images ) ? (bool) $ch_images : true;
?>
<section class="tha-section tha-section--page" id="chronik<?php echo esc_attr( $ctx['uid'] ); ?>">
	<div class="tha-wrap">

		<div class="tha-head">
			<?php if ( '' !== $ch_kicker ) : ?>
				<p class="tha-kicker"><?php echo esc_html( $ch_kicker ); ?></p>
			<?php endif; ?>
			<h2 class="tha-h2"><?php echo esc_html( $ch_title ); ?></h2>
		</div>

		<?php if ( '' !== $ch_intro ) : ?>
			<div class="tha-prose"><p><?php echo wp_kses_post( $ch_intro ); ?></p></div>
		<?php endif; ?>

		<ol class="tha-chronik">
			<?php foreach ( $ch_rows as $ch_row ) : ?>
				<?php $ch_meta = Tha_Chronik::meta( $ch_row ); ?>
				<li class="tha-chronik__row<?php echo '' === $ch_row['title'] ? ' tha-chronik__row--leer' : ''; ?>">
					<span class="tha-chronik__year"><?php echo (int) $ch_row['year']; ?></span>
					<span class="tha-chronik__body">
						<?php if ( '' === $ch_row['title'] ) : ?>
							<span class="tha-chronik__none"><?php esc_html_e( 'keine Aufführung', 'theater-auw' ); ?></span>
						<?php else : ?>
							<b class="tha-chronik__title"><?php echo esc_html( tha_guillemets( $ch_row['title'] ) ); ?></b>
							<?php if ( '' !== $ch_row['genre'] ) : ?>
								<span class="tha-chronik__genre"><?php echo esc_html( $ch_row['genre'] ); ?></span>
							<?php endif; ?>
							<?php if ( '' !== $ch_meta ) : ?>
								<span class="tha-chronik__meta"><?php echo esc_html( $ch_meta ); ?></span>
							<?php endif; ?>

							<?php
							$ch_fotos = $ch_images ? Tha_Chronik::images( $ch_row['year'] ) : array();

							// `<details>` statt eigener Klapp-Logik: das kann der
							// Browser selbst. Es läuft ohne JavaScript, ist mit
							// der Tastatur bedienbar — und solange es zu ist,
							// lädt der Browser die Bilder gar nicht erst. Bei 21
							// Jahrgängen mit bis zu 20 Aufnahmen ist das der
							// Unterschied zwischen einer Seite und einem Download.
							if ( $ch_fotos ) :
								?>
								<details class="tha-fotos">
									<summary class="tha-fotos__toggle">
										<?php
										printf(
											/* translators: %d: Anzahl Fotos */
											esc_html( _n( '%d Foto', '%d Fotos', count( $ch_fotos ), 'theater-auw' ) ),
											count( $ch_fotos )
										);
										?>
									</summary>
									<ul class="tha-fotos__grid">
										<?php foreach ( $ch_fotos as $ch_id ) : ?>
											<li>
												<a href="<?php echo esc_url( wp_get_attachment_image_url( $ch_id, 'full' ) ); ?>"
													target="_blank" rel="noopener">
													<?php
													echo Tha_Renderer::image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
														$ch_id,
														'medium',
														sprintf(
															/* translators: 1: Jahr, 2: Stueck */
															__( '%1$d — %2$s', 'theater-auw' ),
															(int) $ch_row['year'],
															$ch_row['title']
														),
														false,
														'3 / 2'
													);
													?>
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
								</details>
							<?php endif; ?>
						<?php endif; ?>
					</span>
				</li>
			<?php endforeach; ?>
		</ol>

	</div>
</section>
