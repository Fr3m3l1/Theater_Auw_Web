<?php
/**
 * Theater-Navigation.
 *
 * Rendert ein BESTEHENDES WordPress-Menü im Theater-Look — inklusive
 * Unterseiten. Das Menü selbst wird weiterhin unter Design → Menüs gepflegt;
 * das Plugin bringt bewusst keinen eigenen Menü-Bestand mit, sonst gäbe es
 * zwei Navigationen, die auseinanderlaufen.
 *
 * Ist kein Menü gewählt, erscheint hier gar nichts und die Navigation des
 * Themes bleibt allein zuständig.
 *
 * @var array $ctx
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;

if ( ! Tha_Renderer::has_nav( $settings ) ) {
	return;
}

$menu_id = absint( $settings['nav_menu_id'] );

$classes = 'tha-nav';
if ( ! empty( $settings['nav_sticky'] ) ) {
	$classes .= ' tha-nav--sticky';
}
$nav_id = 'tha-nav-menu' . $ctx['uid'];
?>
<nav class="<?php echo esc_attr( $classes ); ?>" aria-label="<?php esc_attr_e( 'Theater Auw', 'theater-auw' ); ?>">
	<div class="tha-nav__inner tha-wrap">

		<a class="tha-nav__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php Tha_Renderer::part( 'partial-wordmark', $ctx ); ?>
		</a>

		<button type="button" class="tha-nav__toggle" data-tha-nav-toggle
			aria-expanded="false" aria-controls="<?php echo esc_attr( $nav_id ); ?>">
			<span class="tha-nav__toggle-label"><?php esc_html_e( 'Menü', 'theater-auw' ); ?></span>
			<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
		</button>

		<div class="tha-nav__menu" id="<?php echo esc_attr( $nav_id ); ?>" data-tha-nav-panel>
			<?php
			wp_nav_menu(
				array(
					'menu'        => $menu_id,
					'container'   => false,
					'menu_class'  => 'tha-nav__list',
					'depth'       => 2,
					'fallback_cb' => false,
					'echo'        => true,
				)
			);
			?>
		</div>

		<?php
		// Ticket-Knopf nur, wenn er auch etwas tut.
		if ( in_array( $ctx['phase'], array( 'vorverkauf', 'spielzeit' ), true )
			&& $ctx['sale_live'] && $settings['ticket_url'] ) :
			?>
			<a class="tha-btn tha-btn--primary tha-btn--sm tha-nav__cta"
				href="<?php echo esc_url( $settings['ticket_url'] ); ?>"
				data-tha-tickets target="_blank" rel="noopener"><?php echo esc_html( $ctx['cta_label'] ); ?></a>
		<?php endif; ?>
	</div>
</nav>
