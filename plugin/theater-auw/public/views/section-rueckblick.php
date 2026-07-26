<?php
/**
 * Rückblick — seit 1937 auf der Bühne.
 *
 * Die Einträge kommen aus dem vorhandenen Bestand (auf stvauw.ch das
 * Divi-Portfolio), nicht aus einer zweiten, abgetippten Liste.
 *
 * @var array $ctx
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;

// $rb_args wird vom Shortcode gesetzt (von/bis/anzahl); auf der Hauptseite
// ist es leer und es gilt die Einstellung.
$rb_args = isset( $rb_args ) ? $rb_args : array();
$rows    = Tha_Rueckblick::entries( $settings, $rb_args );

if ( ! $rows ) {
	// Für Besucherinnen und Besucher verschwindet der Abschnitt spurlos.
	//
	// Wer die Seite verwaltet, hat aber gerade einen Shortcode eingesetzt und
	// sieht nichts passieren — das ist die schlechteste aller Rückmeldungen.
	// Auf den Seiten «2007 – 1987» und «1975 – 1937» lag genau das vor: der
	// Rahmen war null Pixel hoch, weil das Portfolio erst 2008 beginnt.
	if ( current_user_can( 'manage_options' ) ) {
		$rb_von = isset( $rb_args['von'] ) ? (int) $rb_args['von'] : 0;
		$rb_bis = isset( $rb_args['bis'] ) ? (int) $rb_args['bis'] : 0;
		$rb_alle = Tha_Rueckblick::entries( $settings, array() );
		$rb_jahre = array();
		foreach ( $rb_alle as $rb_row ) {
			if ( $rb_row['year'] ) {
				$rb_jahre[] = (int) $rb_row['year'];
			}
		}
		?>
		<section class="tha-section tha-section--page">
			<div class="tha-wrap">
				<p class="tha-hint">
					<?php
					if ( $rb_von && $rb_bis ) {
						printf(
							/* translators: 1: Von-Jahr, 2: Bis-Jahr */
							esc_html__( 'Für die Jahre %1$d bis %2$d liegt im Portfolio kein Eintrag vor — deshalb bleibt dieser Abschnitt leer.', 'theater-auw' ),
							(int) $rb_von,
							(int) $rb_bis
						);
					} else {
						esc_html_e( 'Im Portfolio liegt kein Eintrag vor — deshalb bleibt dieser Abschnitt leer.', 'theater-auw' );
					}

					if ( $rb_jahre ) {
						echo ' ';
						printf(
							/* translators: 1: Aeltestes Jahr, 2: Neuestes Jahr, 3: Anzahl */
							esc_html__( 'Vorhanden sind %3$d Jahrgänge von %1$d bis %2$d.', 'theater-auw' ),
							min( $rb_jahre ),
							max( $rb_jahre ),
							count( $rb_jahre )
						);
					} else {
						echo ' ';
						esc_html_e( 'Es gibt überhaupt keine Einträge — prüfe unter Theater Auw → Rückblick, ob der richtige Beitragstyp gewählt ist.', 'theater-auw' );
					}

					echo ' ';
					esc_html_e( 'Diesen Hinweis sehen nur angemeldete Administratoren.', 'theater-auw' );
					?>
				</p>
			</div>
		</section>
		<?php
	}
	return;
}

$rb_title  = isset( $rb_title ) && '' !== $rb_title
	? $rb_title
	: sprintf(
		/* translators: %d: Gründungsjahr */
		__( 'Seit %d auf der Bühne', 'theater-auw' ),
		(int) $settings['founded_year']
	);
$rb_kicker = isset( $rb_kicker ) ? $rb_kicker : __( 'Unsere Geschichte', 'theater-auw' );
$rb_cols   = isset( $rb_cols ) && $rb_cols ? (int) $rb_cols : 4;
?>
<section class="tha-section tha-section--stage" id="rueckblick<?php echo esc_attr( $ctx['uid'] ); ?>">
	<div class="tha-wrap">
		<div class="tha-head tha-head--row">
			<div>
				<?php if ( '' !== $rb_kicker ) : ?>
					<p class="tha-kicker tha-kicker--onstage"><?php echo esc_html( $rb_kicker ); ?></p>
				<?php endif; ?>
				<h2 class="tha-h2 tha-h2--onstage"><?php echo esc_html( $rb_title ); ?></h2>
			</div>
			<?php if ( ! empty( $settings['rueckblick_archive_url'] ) ) : ?>
				<a class="tha-link-onstage" href="<?php echo esc_url( $settings['rueckblick_archive_url'] ); ?>">
					<?php esc_html_e( 'Ganzer Rückblick', 'theater-auw' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<ul class="tha-years" style="--tha-cols:<?php echo (int) $rb_cols; ?>">
			<?php foreach ( $rows as $row ) : ?>
				<li class="tha-year">
					<?php if ( $row['url'] ) : ?>
						<a class="tha-year__link" href="<?php echo esc_url( $row['url'] ); ?>">
					<?php endif; ?>

					<?php
					// Das Bildformat der Kacheln.
					//
					// Hier stand einmal fest 3/4 — in der Annahme, im Portfolio
					// lägen Plakate, und Plakate sind hochkant. Auf stvauw.ch
					// liegen dort aber Szenenfotos: alle 16 Jahrgänge im
					// Querformat, im Mittel 3:2. Das Hochformat schnitt davon
					// die halbe Bildbreite weg. Deshalb ist es jetzt wählbar,
					// mit Querformat als Standard.
					$rb_ratio = array(
						'quer' => '3 / 2',
						'hoch' => '3 / 4',
						'auto' => '',
					);
					$rb_key = isset( $settings['rueckblick_ratio'] ) ? $settings['rueckblick_ratio'] : 'quer';

					echo Tha_Renderer::image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$row['image_id'],
						'medium_large',
						$row['year'] ? sprintf( __( 'Bild %s', 'theater-auw' ), $row['year'] ) : __( 'Rückblick', 'theater-auw' ),
						true,
						isset( $rb_ratio[ $rb_key ] ) ? $rb_ratio[ $rb_key ] : '3 / 2'
					);
					?>

					<p class="tha-year__meta">
						<?php if ( $row['year'] ) : ?>
							<b class="tha-year__year"><?php echo esc_html( $row['year'] ); ?></b>
						<?php endif; ?>
						<?php if ( $row['title'] ) : ?>
							<span class="tha-year__title"><?php echo esc_html( tha_guillemets( $row['title'] ) ); ?></span>
						<?php endif; ?>
					</p>

					<?php if ( $row['url'] ) : ?>
						</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
