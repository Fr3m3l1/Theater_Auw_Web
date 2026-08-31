<?php
/**
 * «Zur Sponsoren-Anmeldung»-Knopf für den Hero der Hauptseite.
 *
 * Erscheint, solange die Anmeldung aktiv ist (eingeschaltet, Seite gesetzt,
 * Frist nicht abgelaufen) — also genau so lange, wie die Sektion weiter
 * unten. `$cta_class` bestimmt die Lesart im jeweiligen Phasen-Hero.
 *
 * @var array  $ctx
 * @var array  $settings
 * @var string $cta_class  Z.B. `tha-btn--ghost-stage` oder `tha-btn--stage`.
 */
defined( 'ABSPATH' ) || exit;

if ( empty( $settings['anmeldung_enabled'] ) || ! class_exists( 'Tha_Anmeldung' ) ) {
	return;
}
$url = Tha_Anmeldung::signup_url( $settings );
if ( ! $url || ! Tha_Anmeldung::is_open( $settings ) ) {
	return;
}
$cta_class = isset( $cta_class ) && $cta_class ? $cta_class : 'tha-btn--stage';
?>
<a class="tha-btn <?php echo esc_attr( $cta_class ); ?> tha-btn--lg" href="<?php echo esc_url( $url ); ?>">
	<?php esc_html_e( 'Zur Sponsoren-Anmeldung', 'theater-auw' ); ?>
</a>