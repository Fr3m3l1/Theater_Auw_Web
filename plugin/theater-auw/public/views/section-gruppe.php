<?php
/**
 * Eine Bildergruppe — Schauspieler, Team, Sponsoren.
 *
 * @var array $ctx
 * @var array $settings
 * @var array $group
 */
defined( 'ABSPATH' ) || exit;

$items = Tha_Groups::items( $group );
$lead  = ! empty( $group['lead_image_id'] );

// Ein Gruppenbild allein genügt — eine Gruppe darf auch nur aus dem
// Ensemble-Foto bestehen, ohne Einzelporträts.
if ( ! $items && ! $lead ) {
	// Leere Gruppe: für Besucherinnen und Besucher verschwindet der Abschnitt
	// spurlos — eine halbleere Seite ist besser als leere Rahmen. Wer die
	// Seite verwaltet, soll aber nicht rätseln müssen, warum nichts kommt.
	if ( current_user_can( 'manage_options' ) ) {
		?>
		<section class="tha-section tha-section--page">
			<div class="tha-wrap">
				<p class="tha-hint">
					<?php
					printf(
						/* translators: %s: Schlüssel der Gruppe */
						esc_html__( 'Für die Gruppe «%s» sind noch keine Bilder ausgewählt. Unter Theater Auw → Unterseiten wählst du sie aus der Mediathek. Diesen Hinweis sehen nur angemeldete Administratoren.', 'theater-auw' ),
						esc_html( $group['key'] )
					);
					?>
				</p>
			</div>
		</section>
		<?php
	}
	return;
}

$type  = $group['type'];
$size  = Tha_Groups::image_size( $type );
$ratio = Tha_Groups::ratio( $type );
$dark  = ! empty( $group['dark'] );

$section_classes = 'tha-section ' . ( $dark ? 'tha-section--stage' : 'tha-section--page' );
?>
<section class="<?php echo esc_attr( $section_classes ); ?>" id="gruppe-<?php echo esc_attr( $group['key'] . $ctx['uid'] ); ?>">
	<div class="tha-wrap">

		<?php if ( '' !== $group['title'] || '' !== $group['kicker'] ) : ?>
			<div class="tha-head">
				<?php if ( '' !== $group['kicker'] ) : ?>
					<p class="tha-kicker <?php echo $dark ? 'tha-kicker--onstage' : ''; ?>"><?php echo esc_html( $group['kicker'] ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $group['title'] ) : ?>
					<h2 class="tha-h2 <?php echo $dark ? 'tha-h2--onstage' : ''; ?>"><?php echo esc_html( $group['title'] ); ?></h2>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php
		// Einleitungstext und Gruppenbild.
		//
		// «neben» stellt beides nebeneinander — ein Ensemble-Foto neben dem
		// Stücktext liest sich als Einheit, und das Bild bleibt in vernünftiger
		// Grösse. «oben» stellt das Bild breit darüber, aber gedeckelt: über
		// etwa 860px wird ein Gruppenfoto nicht besser, nur grösser.
		$layout = $lead ? $group['lead_layout'] : 'oben';
		$split  = ( 'neben' === $layout && $lead );

		$render_intro = static function () use ( $group, $dark ) {
			if ( '' === $group['intro'] ) {
				return;
			}
			echo '<div class="tha-prose ' . ( $dark ? 'tha-prose--onstage' : '' ) . '">';
			foreach ( preg_split( '/\n\s*\n/', $group['intro'] ) as $para ) {
				$para = trim( $para );
				if ( '' !== $para ) {
					echo '<p>' . wp_kses_post( $para ) . '</p>';
				}
			}
			echo '</div>';
		};

		$render_lead = static function () use ( $group, $dark ) {
			?>
			<figure class="tha-group-lead">
				<?php
				echo Tha_Renderer::image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$group['lead_image_id'],
					'large',
					$group['title'] ? $group['title'] : __( 'Gruppenbild', 'theater-auw' ),
					$dark,
					''
				);
				?>
				<?php if ( '' !== $group['lead_caption'] ) : ?>
					<figcaption class="tha-group-lead__caption"><?php echo esc_html( $group['lead_caption'] ); ?></figcaption>
				<?php endif; ?>
			</figure>
			<?php
		};
		?>

		<?php if ( $split ) : ?>
			<div class="tha-lead-split<?php echo '' === $group['intro'] ? ' tha-lead-split--solo' : ''; ?>">
				<?php
				$render_intro();
				$render_lead();
				?>
			</div>
		<?php else : ?>
			<?php
			$render_intro();
			if ( $lead ) {
				echo '<div class="tha-group-lead-wide">';
				$render_lead();
				echo '</div>';
			}
			?>
		<?php endif; ?>

		<?php if ( $items ) : ?>
		<ul class="tha-people tha-people--<?php echo esc_attr( $type ); ?>"
			style="--tha-cols:<?php echo (int) $group['columns']; ?>">
			<?php foreach ( $items as $item ) : ?>
				<li class="tha-person<?php echo ! empty( $item['wide'] ) ? ' tha-person--wide' : ''; ?>">
					<?php
					// Breite Bilder sind Gruppenaufnahmen — die behalten ihr
					// eigenes Format, sonst würden die Aussenstehenden
					// weggeschnitten.
					echo Tha_Renderer::image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$item['id'],
						empty( $item['wide'] ) ? $size : 'large',
						$item['name'] ? $item['name'] : __( 'Bild', 'theater-auw' ),
						$dark,
						empty( $item['wide'] ) ? $ratio : ''
					);
					?>
					<?php if ( '' !== $item['name'] ) : ?>
						<p class="tha-person__meta">
							<b class="tha-person__name"><?php echo esc_html( $item['name'] ); ?></b>
							<?php if ( '' !== $item['role'] ) : ?>
								<span class="tha-person__role"><?php echo esc_html( $item['role'] ); ?></span>
							<?php endif; ?>
						</p>
						<?php if ( ! empty( $item['text'] ) ) : ?>
							<p class="tha-person__text"><?php echo esc_html( $item['text'] ); ?></p>
						<?php endif; ?>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>
	</div>
</section>
