<?php
/**
 * Phase 2 — Vorverkauf. Zwei Unterzustände:
 *   sale_pending → Countdown auf den Verkaufsstart, CTA noch nicht dominant
 *   sale_live    → roter CTA dominiert alles andere
 *
 * @var array $ctx
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;

$premiere  = Tha_Phase::premiere( $settings );
$last      = end( $settings['dates'] );
$sale_live = $ctx['sale_live'];
$url       = $settings['ticket_url'];
?>
<section class="tha-hero tha-hero--stage">
	<div class="tha-hero__inner tha-wrap">
		<?php
		// Nur wenn die Navigation sie nicht schon fuehrt.
		if ( ! Tha_Renderer::has_nav( $settings ) ) {
			Tha_Renderer::part( 'partial-wordmark', $ctx );
		}
		?>

		<span class="tha-badge <?php echo $sale_live ? 'tha-badge--red' : 'tha-badge--stage'; ?>">
			<?php
			echo $sale_live
				? esc_html__( 'Vorverkauf läuft', 'theater-auw' )
				: esc_html__( 'Vorverkauf startet bald', 'theater-auw' );
			?>
		</span>

		<h1 class="tha-hero__title">
			<?php if ( $sale_live ) : ?>
				<?php esc_html_e( 'Jetzt Plätze', 'theater-auw' ); ?><br><?php esc_html_e( 'sichern', 'theater-auw' ); ?>
			<?php else : ?>
				<?php echo esc_html( tha_guillemets( $settings['play_title'] ) ); ?>
			<?php endif; ?>
		</h1>

		<p class="tha-hero__sub">
			<?php
			echo esc_html( tha_guillemets( $settings['play_title'] ) . ' — ' . $settings['play_genre'] . '.' );

			if ( $premiere && $last ) {
				echo ' ' . esc_html(
					sprintf(
						/* translators: 1: Anzahl, 2: erstes Datum, 3: letztes Datum mit Jahr */
						__( '%1$d Vorstellungen vom %2$s bis %3$s.', 'theater-auw' ),
						count( $settings['dates'] ),
						tha_date_short( $premiere['date'] ),
						tha_date_short_year( $last['date'] )
					)
				);
			}
			if ( $sale_live ) {
				echo ' ' . esc_html__( 'Premiere und Sonntag sind erfahrungsgemäss schnell weg.', 'theater-auw' );
			}
			?>
		</p>

		<div class="tha-hero__actions">
			<?php if ( $sale_live && $url ) : ?>
				<a class="tha-btn tha-btn--primary tha-btn--lg" href="<?php echo esc_url( $url ); ?>"
					data-tha-tickets target="_blank" rel="noopener">
					<?php echo esc_html( $ctx['cta_label'] ); ?>
					<?php Tha_Renderer::part( 'partial-icon-external', $ctx ); ?>
				</a>
			<?php elseif ( $sale_live ) : ?>
				<span class="tha-btn tha-btn--primary tha-btn--lg is-disabled" aria-disabled="true"><?php esc_html_e( 'Ticketlink folgt', 'theater-auw' ); ?></span>
			<?php endif; ?>
			<a class="tha-btn tha-btn--stage tha-btn--lg" href="#daten<?php echo esc_attr( $ctx['uid'] ); ?>"><?php esc_html_e( 'Alle Spieldaten', 'theater-auw' ); ?></a>
			<?php Tha_Renderer::part( 'partial-anmeldung-cta', $ctx, array( 'cta_class' => 'tha-btn--ghost-stage' ) ); ?>
		</div>

		<?php Tha_Renderer::part( 'partial-countdown', $ctx ); ?>
	</div>
</section>
