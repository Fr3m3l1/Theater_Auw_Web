<?php
/**
 * Dunkler Seitenkopf der Anmeldeseite.
 *
 * Teil des `the_content`-Filters in `Tha_Anmeldung`. Steht VOR dem
 * Seiteninhalt (der im Divi-Builder gebaut wurde und ein echtes Formular
 * trägt) und ist in eine `.tha`-Wurzel gehüllt: dadurch gelten dieselben
 * Stile wie auf den übrigen Theater-Seiten, und die Theater-Typografie
 * lässt den Inhalt daneben in Ruhe.
 *
 * @var array  $settings  Plugin-Einstellungen (aus class-tha-anmeldung.php).
 * @var bool   $open      Nimmt die Anmeldung noch etwas entgegen?
 * @var string $lead      Vorspann unter dem Titel.
 * @var string $kicker    Kleine Zeile über dem Titel.
 * @var string $titel     Titel des Seitenkopfs.
 */
defined( 'ABSPATH' ) || exit;
?>
<header class="tha-pagehead">
	<div class="tha-wrap">
		<?php if ( '' !== $kicker ) : ?>
			<p class="tha-kicker tha-kicker--onstage"><?php echo esc_html( $kicker ); ?></p>
		<?php endif; ?>

		<h1 class="tha-pagehead__title"><?php echo esc_html( $titel ); ?></h1>

		<?php if ( '' !== $lead ) : ?>
			<p class="tha-pagehead__lead"><?php echo esc_html( $lead ); ?></p>
		<?php endif; ?>

		<?php
		$frist = trim( (string) $settings['anmeldung_deadline'] );
		if ( $open && '' !== $frist ) :
			?>
			<p class="tha-pagehead__badge">
				<?php
				printf(
					/* translators: %s: Datum, z.B. «8. Oktober 2026» */
					esc_html__( 'Anmeldung bis %s', 'theater-auw' ),
					esc_html( Tha_Anmeldung::deadline_label( $settings ) )
				);
				?>
			</p>
		<?php elseif ( ! $open ) : ?>
			<p class="tha-pagehead__badge tha-pagehead__badge--zu">
				<?php esc_html_e( 'Die Anmeldung ist geschlossen', 'theater-auw' ); ?>
			</p>
		<?php endif; ?>
	</div>
</header>