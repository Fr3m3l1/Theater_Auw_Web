<?php
/**
 * Seitenrahmen. Rendert NUR den Inhalt — Kopfzeile, Navigation und Footer
 * bleiben die des Themes. Die Theater-Seite soll Teil von stvauw.ch bleiben,
 * nicht daneben stehen.
 *
 * @var array $ctx
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;

$settings = $ctx['settings'];
$phase    = $ctx['phase'];

$classes = array(
	'tha',
	'tha--' . $phase,
	'tha--width-' . $ctx['width'],
);
if ( 'stark' === $settings['isolation'] ) {
	$classes[] = 'tha--stark';
}
if ( $ctx['is_preview'] ) {
	$classes[] = 'tha--preview';
}
?>
<?php echo Tha_Assets::font_link_fallback(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-phase="<?php echo esc_attr( $phase ); ?>" id="theater<?php echo esc_attr( $ctx['uid'] ); ?>">

	<?php if ( $ctx['is_preview'] ) : ?>
		<p class="tha-preview-note">
			<?php
			printf(
				/* translators: %s: Phasenname */
				esc_html__( 'Vorschau: Phase «%s». Nur für angemeldete Administratoren sichtbar.', 'theater-auw' ),
				esc_html( ucfirst( $phase ) )
			);
			?>
		</p>
	<?php endif; ?>

	<?php
	Tha_Renderer::nav_once( $ctx );

	if ( in_array( 'hero', $ctx['sections'], true ) ) {
		Tha_Renderer::part( 'hero-' . $phase, $ctx );
	}
	if ( in_array( 'spieldaten', $ctx['sections'], true ) ) {
		Tha_Renderer::part( 'section-spieldaten', $ctx );
	}
	if ( in_array( 'stueck', $ctx['sections'], true ) ) {
		Tha_Renderer::part( 'section-stueck', $ctx );
	}
	if ( in_array( 'weiteres', $ctx['sections'], true ) ) {
		Tha_Renderer::part( 'section-weiteres', $ctx );
	}

	// Der Sponsoring-Hinweis — eine eigene Sektion, kein Einschub in
	// «Weiteres»: er ist kein Rund-um-die-Vorstellung-Thema, sondern ein
	// Aufruf — und er muss ohne Eingriff wieder verschwinden können, wenn
	// die Anmeldefrist abgelaufen ist (siehe Tha_Anmeldung).
	if ( ! empty( $settings['anmeldung_enabled'] ) && class_exists( 'Tha_Anmeldung' ) ) {
		Tha_Renderer::part( 'section-anmeldung', $ctx );
	}
	if ( in_array( 'details', $ctx['sections'], true ) ) {
		Tha_Renderer::part( 'section-details', $ctx );
	}
	if ( in_array( 'rueckblick', $ctx['sections'], true ) ) {
		Tha_Renderer::part( 'section-rueckblick', $ctx );
	}
	if ( in_array( 'kontakt', $ctx['sections'], true ) ) {
		Tha_Renderer::part( 'section-kontakt', $ctx );
	}

	Tha_Renderer::part( 'partial-ticket-dialog', $ctx );

	if ( ! empty( $settings['sticky_cta'] ) && in_array( $phase, array( 'vorverkauf', 'spielzeit' ), true ) && $ctx['sale_live'] ) {
		Tha_Renderer::part( 'partial-sticky-cta', $ctx );
	}
	?>
</div>
