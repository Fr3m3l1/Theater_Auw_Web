<?php
/**
 * Phase 3 — Spielzeit. Strukturell dasselbe wie Vorverkauf, aber mit dem
 * Gold-Spotlight und «Heute Vorstellung», wenn heute gespielt wird.
 *
 * @var array $ctx
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;

$today = $ctx['today'];
$next  = $ctx['next'];
$show  = $today ? $today : $next;
$url   = $show ? tha_ticket_url( $show ) : $settings['ticket_url'];

// «Heute Abend» stimmt bei der Sonntags-Nachmittagsvorstellung nicht.
$today_lead = __( 'Heute Abend', 'theater-auw' );
if ( $today ) {
	$hour = (int) strtok( $today['time'], '.' );
	if ( $hour < 17 ) {
		$today_lead = __( 'Heute', 'theater-auw' );
	}
}
?>
<section class="tha-hero tha-hero--stage tha-hero--spotlight">
	<div class="tha-hero__inner tha-wrap">
		<?php
		// Nur wenn die Navigation sie nicht schon fuehrt.
		if ( ! Tha_Renderer::has_nav( $settings ) ) {
			Tha_Renderer::part( 'partial-wordmark', $ctx );
		}
		?>

		<span class="tha-badge tha-badge--gold">
			<?php
			echo $today
				? esc_html__( 'Spielzeit — heute Vorstellung', 'theater-auw' )
				: esc_html__( 'Spielzeit läuft', 'theater-auw' );
			?>
		</span>

		<h1 class="tha-hero__title">
			<?php if ( $today ) : ?>
				<?php echo esc_html( $today_lead ); ?><br><span class="tha-hero__title-gold"><?php echo esc_html( tha_time_label( $today['time'] ) ); ?></span>
			<?php elseif ( $next ) : ?>
				<?php esc_html_e( 'Nächste', 'theater-auw' ); ?><br><span class="tha-hero__title-gold"><?php echo esc_html( tha_weekday_short( $next['date'] ) . ' ' . tha_date_short( $next['date'] ) ); ?></span>
			<?php else : ?>
				<?php echo esc_html( tha_guillemets( $settings['play_title'] ) ); ?>
			<?php endif; ?>
		</h1>

		<p class="tha-hero__sub">
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: Türöffnung, 2: Essen ab, 3: Essen bis */
					__( 'Türöffnung %1$s Uhr, Essen von %2$s bis %3$s Uhr.', 'theater-auw' ),
					$settings['doors_time'],
					$settings['food_from'],
					$settings['food_to']
				)
			);
			?>
			<?php esc_html_e( 'Es hat noch Restplätze — online oder an der Abendkasse.', 'theater-auw' ); ?>
		</p>

		<div class="tha-hero__actions">
			<?php if ( $url ) : ?>
				<a class="tha-btn tha-btn--primary tha-btn--lg" href="<?php echo esc_url( $url ); ?>"
					data-tha-tickets target="_blank" rel="noopener">
					<?php esc_html_e( 'Restplätze sichern', 'theater-auw' ); ?>
					<?php Tha_Renderer::part( 'partial-icon-external', $ctx ); ?>
				</a>
			<?php endif; ?>
			<a class="tha-btn tha-btn--stage tha-btn--lg" href="#daten<?php echo esc_attr( $ctx['uid'] ); ?>"><?php esc_html_e( 'Alle Spieldaten', 'theater-auw' ); ?></a>
			<?php Tha_Renderer::part( 'partial-anmeldung-cta', $ctx, array( 'cta_class' => 'tha-btn--ghost-stage' ) ); ?>
		</div>

		<?php Tha_Renderer::part( 'partial-countdown', $ctx ); ?>
	</div>
</section>
