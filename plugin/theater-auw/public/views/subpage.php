<?php
/**
 * Rahmen für eine Unterseite (Schauspieler, Sponsoren, OK …).
 *
 * Gleiche Navigation und gleiche Bildsprache wie die Hauptseite, aber ein
 * kompakter Kopf statt des grossen Plakat-Heros — eine Unterseite soll nicht
 * so laut auftreten wie die Startseite.
 *
 * @var array $ctx
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;

$classes = array( 'tha', 'tha--' . $ctx['phase'], 'tha--width-' . $ctx['width'], 'tha--sub' );
if ( 'stark' === $settings['isolation'] ) {
	$classes[] = 'tha--stark';
}
?>
<?php echo Tha_Assets::font_link_fallback(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-phase="<?php echo esc_attr( $ctx['phase'] ); ?>">

	<?php Tha_Renderer::nav_once( $ctx ); ?>

	<?php if ( '' !== $ctx['sub_title'] ) : ?>
		<header class="tha-pagehead">
			<div class="tha-wrap">
				<?php if ( '' !== $ctx['sub_kicker'] ) : ?>
					<p class="tha-kicker tha-kicker--onstage"><?php echo esc_html( $ctx['sub_kicker'] ); ?></p>
				<?php endif; ?>
				<h1 class="tha-pagehead__title"><?php echo esc_html( $ctx['sub_title'] ); ?></h1>
				<?php if ( '' !== $ctx['sub_lead'] ) : ?>
					<p class="tha-pagehead__lead"><?php echo esc_html( $ctx['sub_lead'] ); ?></p>
				<?php endif; ?>
			</div>
		</header>
	<?php endif; ?>

	<?php if ( '' !== $ctx['sub_group'] ) : ?>
		<?php
		$group = Tha_Groups::get( $ctx['sub_group'], $settings );
		if ( $group ) {
			Tha_Renderer::part( 'section-gruppe', $ctx, array( 'group' => $group ) );
		}
		?>
	<?php endif; ?>

	<?php if ( '' !== $ctx['sub_section'] ) : ?>
		<?php Tha_Renderer::part( 'section-' . $ctx['sub_section'], $ctx ); ?>
	<?php endif; ?>

	<?php if ( '' !== $ctx['sub_content'] ) : ?>
		<section class="tha-section tha-section--card">
			<div class="tha-wrap">
				<div class="tha-prose tha-prose--wide">
					<?php echo $ctx['sub_content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	// `kontakt="0"` am Shortcode. Stand vorher im Kontext, wurde hier aber
	// nicht gelesen — die Kontaktzeile kam trotzdem. Wer zwei Shortcodes auf
	// eine Seite setzt, hatte sie deshalb zweimal.
	if ( $ctx['sub_kontakt'] ) :
		?>
		<?php Tha_Renderer::part( 'section-kontakt', $ctx ); ?>
	<?php endif; ?>
	<?php Tha_Renderer::part( 'partial-ticket-dialog', $ctx ); ?>
</div>
