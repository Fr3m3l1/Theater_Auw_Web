<?php
/**
 * Phase 1 — Vorbereitung. Wo wir gerade stehen: die Proben laufen.
 * Neutraler Badge, Papier-Ruhe, Countdown auf die Premiere.
 *
 * @var array $ctx
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;

$premiere = Tha_Phase::premiere( $settings );
?>
<section class="tha-hero tha-hero--stage">
	<div class="tha-hero__inner tha-wrap">
		<?php
		// Nur wenn die Navigation sie nicht schon fuehrt.
		if ( ! Tha_Renderer::has_nav( $settings ) ) {
			Tha_Renderer::part( 'partial-wordmark', $ctx );
		}
		?>

		<span class="tha-badge tha-badge--stage"><?php esc_html_e( 'Vorbereitung läuft', 'theater-auw' ); ?></span>

		<h1 class="tha-hero__title">
			<?php
			printf(
				/* translators: %d: Saisonjahr */
				esc_html__( 'Theater %d', 'theater-auw' ),
				(int) $settings['season_year']
			);
			?>
			<br><span class="tha-hero__title-accent"><?php echo esc_html( tha_guillemets( $settings['play_title'] ) ); ?></span>
		</h1>

		<p class="tha-hero__sub">
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: Genre, 2: Autor */
					__( '%1$s von %2$s.', 'theater-auw' ),
					$settings['play_genre'],
					$settings['play_author']
				)
			);
			?>
			<?php if ( $premiere ) : ?>
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: Datum ausgeschrieben, 2: Ort */
						__( 'Die Proben laufen — Premiere am %1$s in der %2$s.', 'theater-auw' ),
						tha_date_long( $premiere['date'] ),
						$settings['venue_name']
					)
				);
				?>
			<?php endif; ?>
		</p>

		<div class="tha-hero__actions">
			<a class="tha-btn tha-btn--stage tha-btn--lg" href="#daten<?php echo esc_attr( $ctx['uid'] ); ?>"><?php esc_html_e( 'Spieldaten ansehen', 'theater-auw' ); ?></a>
			<a class="tha-btn tha-btn--ghost-stage tha-btn--lg" href="#stueck<?php echo esc_attr( $ctx['uid'] ); ?>"><?php esc_html_e( 'Zum Stück', 'theater-auw' ); ?></a>
			<?php Tha_Renderer::part( 'partial-anmeldung-cta', $ctx, array( 'cta_class' => 'tha-btn--ghost-stage' ) ); ?>
		</div>

		<?php Tha_Renderer::part( 'partial-countdown', $ctx ); ?>
	</div>
</section>
