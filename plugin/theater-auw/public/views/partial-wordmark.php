<?php
/**
 * Wortmarke im dunklen Hero.
 *
 * Wenn in den Einstellungen ein Logo gewählt ist, kommt das Bild — sonst wird
 * «theater auw» typografisch gesetzt (weiss + rotes «auw»), wie es die Brand-
 * Regel für Flächen ohne Logodatei vorsieht. Das Logo gehört ausschliesslich
 * auf dunkle Flächen; auf Papier verschwindet die weisse Wortmarke.
 *
 * @var array $ctx
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;

$logo_id = absint( $settings['logo_id'] );
?>
<div class="tha-wordmark">
	<?php if ( $logo_id ) : ?>
		<?php
		echo wp_get_attachment_image(
			$logo_id,
			'medium',
			false,
			array(
				'class' => 'tha-wordmark__img',
				'alt'   => esc_attr__( 'Theater Auw', 'theater-auw' ),
			)
		);
		?>
	<?php else : ?>
		<span class="tha-wordmark__text">theater<span class="tha-wordmark__accent">auw</span></span>
	<?php endif; ?>
</div>
