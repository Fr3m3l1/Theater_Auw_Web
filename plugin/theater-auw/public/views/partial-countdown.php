<?php
/**
 * Countdown. Der Server rechnet das Ziel als UTC-Epoch in Millisekunden aus,
 * das JavaScript macht nur noch die Subtraktion. Ohne JavaScript bleibt die
 * serverseitig gerenderte Tagesangabe stehen — nichts wirkt kaputt.
 *
 * @var array $ctx
 */

defined( 'ABSPATH' ) || exit;

$cd = $ctx['countdown'];
if ( empty( $cd ) ) {
	return;
}
?>
<div class="tha-countdown" data-tha-countdown data-target="<?php echo esc_attr( $cd['ts'] ); ?>">
	<div class="tha-countdown__units" aria-hidden="true">
		<span class="tha-countdown__unit"><b data-cd="d"><?php echo esc_html( $cd['days'] ); ?></b><i><?php esc_html_e( 'Tage', 'theater-auw' ); ?></i></span>
		<span class="tha-countdown__unit"><b data-cd="h">–</b><i><?php esc_html_e( 'Std', 'theater-auw' ); ?></i></span>
		<span class="tha-countdown__unit"><b data-cd="m">–</b><i><?php esc_html_e( 'Min', 'theater-auw' ); ?></i></span>
	</div>
	<p class="tha-countdown__label">
		<span class="tha-countdown__fallback"><?php echo esc_html( $cd['fallback'] ); ?></span>
		<?php echo esc_html( $cd['label'] ); ?>
	</p>
</div>
