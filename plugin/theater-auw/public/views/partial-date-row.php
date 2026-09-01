<?php
/**
 * Eine Zeile der Spieldaten.
 *
 * @var array $ctx
 * @var array $settings
 * @var array $show
 */

defined( 'ABSPATH' ) || exit;

$status = $show['effective_status'];
$dim    = in_array( $status, array( 'vorbei', 'ausverkauft' ), true );
$url    = tha_ticket_url( $show );

$status_labels = array(
	'wenige'      => __( 'Wenige Plätze', 'theater-auw' ),
	'ausverkauft' => __( 'Ausverkauft', 'theater-auw' ),
	'vorbei'      => __( 'Vorbei', 'theater-auw' ),
);

$row_classes = array( 'tha-date' );
if ( $dim ) {
	$row_classes[] = 'is-dim';
}
if ( ! empty( $show['is_today'] ) && 'spielzeit' === $ctx['phase'] ) {
	$row_classes[] = 'is-today';
}
?>
<li class="<?php echo esc_attr( implode( ' ', $row_classes ) ); ?>">
	<span class="tha-date__wd"><?php echo esc_html( tha_weekday_short( $show['date'] ) ); ?></span>
	<span class="tha-date__d"><?php echo esc_html( tha_date_short( $show['date'] ) ); ?></span>

	<span class="tha-date__meta">
		<?php echo esc_html( tha_time_label( $show['time'] ) ); ?>
		<?php if ( ! empty( $show['doors_time'] ) ) : ?>
			<span class="tha-date__doors"><?php echo esc_html( sprintf( __( 'Türöffnung %s Uhr', 'theater-auw' ), $show['doors_time'] ) ); ?></span>
		<?php endif; ?>
		<?php if ( '' !== $show['label'] ) : ?>
			<b class="tha-date__label"><?php echo esc_html( $show['label'] ); ?></b>
		<?php endif; ?>
		<?php if ( ! empty( $show['is_today'] ) && 'spielzeit' === $ctx['phase'] ) : ?>
			<b class="tha-date__today"><?php esc_html_e( 'Heute', 'theater-auw' ); ?></b>
		<?php endif; ?>
	</span>

	<?php if ( isset( $status_labels[ $status ] ) ) : ?>
		<span class="tha-date__status tha-date__status--<?php echo esc_attr( $status ); ?>">
			<?php echo esc_html( $status_labels[ $status ] ); ?>
		</span>
	<?php endif; ?>

	<?php if ( ! empty( $show['bookable'] ) && $url ) : ?>
		<a class="tha-btn tha-btn--primary tha-btn--sm tha-date__cta"
			href="<?php echo esc_url( $url ); ?>" data-tha-tickets target="_blank" rel="noopener">
			<?php echo esc_html( 'kaufen' === $settings['ticket_cta_mode'] ? __( 'Tickets', 'theater-auw' ) : __( 'Reservieren', 'theater-auw' ) ); ?>
		</a>
	<?php endif; ?>
</li>
