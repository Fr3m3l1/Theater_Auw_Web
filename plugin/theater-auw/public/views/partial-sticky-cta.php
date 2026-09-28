<?php
/**
 * Der «Tickets»-Knopf aus dem UI-Kit-Header — hier als Leiste, die erst
 * erscheint, wenn der Hero hochgescrollt ist. So bleibt der CTA erreichbar,
 * ohne mit der Navigation des Themes zu konkurrieren.
 *
 * @var array $ctx
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;

if ( ! $settings['ticket_url'] ) {
	return;
}
?>
<div class="tha-sticky" data-tha-sticky hidden>
	<span class="tha-sticky__text"><?php echo esc_html( tha_guillemets( $settings['play_title'] ) ); ?></span>
	<a class="tha-btn tha-btn--primary tha-btn--sm" href="<?php echo esc_url( $settings['ticket_url'] ); ?>"
		target="_blank" rel="noopener"><?php echo esc_html( $ctx['cta_label'] ); ?></a>
</div>
