<?php
/**
 * Phase 4 — Danke. Leise. Grüner Success-Akzent, kein Countdown, kein CTA.
 * Der Dank ist explizit und nennt die Leute — so wie es der Verein selbst tut.
 *
 * @var array $ctx
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="tha-hero tha-hero--stage tha-hero--danke">
	<div class="tha-hero__inner tha-wrap">
		<?php
		// Nur wenn die Navigation sie nicht schon fuehrt.
		if ( ! Tha_Renderer::has_nav( $settings ) ) {
			Tha_Renderer::part( 'partial-wordmark', $ctx );
		}
		?>

		<span class="tha-badge tha-badge--success">
			<?php
			printf(
				/* translators: %d: Saisonjahr */
				esc_html__( "Saison %d — das war's", 'theater-auw' ),
				(int) $settings['season_year']
			);
			?>
		</span>

		<h1 class="tha-hero__title"><?php esc_html_e( 'Merci villmal!', 'theater-auw' ); ?></h1>

		<p class="tha-hero__sub">
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: Anzahl Vorstellungen, 2: Stücktitel */
					__( '%1$d Vorstellungen, ein voller Saal und viele Lacher — %2$s ist Geschichte.', 'theater-auw' ),
					count( $settings['dates'] ),
					tha_guillemets( $settings['play_title'] )
				)
			);
			?>
			<?php esc_html_e( 'Ein herzliches Dankeschön an alle Besucherinnen und Besucher, an alle Helferinnen und Helfer — und an unsere Sponsoren.', 'theater-auw' ); ?>
		</p>

		<div class="tha-hero__actions">
			<a class="tha-btn tha-btn--stage tha-btn--lg" href="#rueckblick<?php echo esc_attr( $ctx['uid'] ); ?>"><?php esc_html_e( 'Rückblick ansehen', 'theater-auw' ); ?></a>
		</div>

		<p class="tha-hero__signoff">
			<?php
			printf(
				/* translators: %d: nächstes Jahr */
				esc_html__( 'Herzliche Grüsse — bis %d.', 'theater-auw' ),
				(int) $settings['next_year']
			);
			?>
		</p>
	</div>
</section>
