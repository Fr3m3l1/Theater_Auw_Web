<?php
/**
 * Updates — was seit dem letzten Mal passiert ist.
 *
 * Einträge als Datensätze statt als Absätze in einem Baukasten-Modul. Das
 * kostet einmal Tippen und bringt drei Dinge, die sich von Hand nicht halten
 * lassen: die Reihenfolge stimmt immer (neuestes zuerst, das Plugin sortiert),
 * das Datum steht maschinenlesbar im `<time>`, und ein Eintrag kann nicht
 * versehentlich mitten in der Liste landen.
 *
 * Bewusst KEINE Beiträge (`post`): die Update-Seite gehört zum Theaterteil,
 * nicht in den Blog der Website. Ein eigener Beitragstyp wäre die dritte
 * Stelle, an der Inhalte liegen.
 *
 * @var array $ctx
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;

$rows  = Tha_Settings::updates( isset( $ctx['sub_limit'] ) ? $ctx['sub_limit'] : 0, $settings );
$intro = trim( (string) $settings['updates_intro'] );

if ( ! $rows ) {
	if ( '' !== $intro ) {
		?>
		<section class="tha-section tha-section--card" id="updates<?php echo esc_attr( $ctx['uid'] ); ?>">
			<div class="tha-wrap">
				<div class="tha-prose"><p><?php echo wp_kses_post( $intro ); ?></p></div>
			</div>
		</section>
		<?php
	}
	if ( current_user_can( 'manage_options' ) ) {
		?>
		<section class="tha-section tha-section--page">
			<div class="tha-wrap">
				<p class="tha-hint">
					<?php esc_html_e( 'Es ist noch kein Update erfasst. Unter Theater Auw → Updates legst du Einträge an — Datum, Überschrift, Text, wahlweise ein Bild. Diesen Hinweis sehen nur angemeldete Administratoren.', 'theater-auw' ); ?>
				</p>
			</div>
		</section>
		<?php
	}
	return;
}
?>
<section class="tha-section tha-section--card" id="updates<?php echo esc_attr( $ctx['uid'] ); ?>">
	<div class="tha-wrap">

		<?php if ( '' !== $intro ) : ?>
			<div class="tha-prose"><p><?php echo wp_kses_post( $intro ); ?></p></div>
		<?php endif; ?>

		<ol class="tha-updates">
			<?php foreach ( $rows as $row ) : ?>
				<li class="tha-update">

					<?php if ( '' !== $row['date'] ) : ?>
						<p class="tha-update__date">
							<time datetime="<?php echo esc_attr( $row['date'] ); ?>"><?php echo esc_html( tha_date_written( $row['date'] ) ); ?></time>
						</p>
					<?php endif; ?>

					<div class="tha-update__body">
						<?php if ( '' !== $row['title'] ) : ?>
							<h3 class="tha-h3 tha-update__title"><?php echo esc_html( $row['title'] ); ?></h3>
						<?php endif; ?>

						<?php if ( $row['image_id'] ) : ?>
							<figure class="tha-update__media">
								<?php
								// Kein Zuschnitt: ein Probenfoto im Hochformat
								// soll hochkant bleiben, ein Plakat quer.
								echo Tha_Renderer::image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									$row['image_id'],
									'large',
									$row['title'] ? $row['title'] : __( 'Bild zum Update', 'theater-auw' ),
									false,
									''
								);
								?>
							</figure>
						<?php endif; ?>

						<?php if ( '' !== $row['text'] ) : ?>
							<div class="tha-prose">
								<?php
								foreach ( preg_split( '/\n\s*\n/', $row['text'] ) as $para ) {
									$para = trim( $para );
									if ( '' !== $para ) {
										echo '<p>' . wp_kses_post( $para ) . '</p>';
									}
								}
								?>
							</div>
						<?php endif; ?>

						<?php if ( '' !== $row['url'] ) : ?>
							<p class="tha-update__more">
								<a href="<?php echo esc_url( $row['url'] ); ?>" target="_blank" rel="noopener">
									<?php Tha_Renderer::part( 'partial-icon-external', $ctx ); ?>
									<?php echo esc_html( '' !== $row['link'] ? $row['link'] : __( 'Mehr dazu', 'theater-auw' ) ); ?>
								</a>
							</p>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
