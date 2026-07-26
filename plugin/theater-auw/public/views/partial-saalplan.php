<?php
/**
 * Der Saalplan — gezeichnet statt fotografiert.
 *
 * Im Programmheft ist das ein Bild. Ein Bild wird beim Vergrössern matschig,
 * auf dem Handy liest niemand die dreistelligen Nummern, und wer «wo ist 312?»
 * sucht, findet nichts. Der Plan steht deshalb als echte Zeilen im HTML.
 *
 * Der Saal: fünf Blöcke zu je 50 Plätzen — vorn zwölf Zweierreihen, hinten
 * zwölf, und zuunterst die beiden letzten nebeneinander in einer breiten
 * Zelle. Rechts daneben die Seitenreihe 601–621 in drei Gruppen, jede um
 * eine bestimmte Zeilenzahl versetzt; so steht sie auf dem Papier, und so
 * steht man auch im Saal.
 *
 * Die Halle ändert sich nicht, die Zahlen also auch nicht — sie stehen hier
 * fest. Wer doch einmal umbaut, hinterlegt in den Einstellungen ein Bild;
 * das ersetzt diesen Plan.
 *
 * @var array $ctx
 * @var array $settings
 */

defined( 'ABSPATH' ) || exit;

// Die fünf Blöcke tragen ihre Hunderterstelle: 100 heisst 101–150.
$bloecke = array( 100, 200, 300, 400, 500 );

// Die Seitenreihe. «versatz» ist die Zeilenzahl, um die eine Gruppe gegenüber
// dem Blockraster nach unten rutscht — siehe CSS.
$seitenreihe = array(
	array( 'von' => 601, 'bis' => 611, 'versatz' => 1 ),
	array( 'von' => 612, 'bis' => 614, 'versatz' => 2 ),
	array( 'von' => 615, 'bis' => 621, 'versatz' => 3 ),
);

$farbe = isset( $settings['seatmap_color'] ) ? trim( (string) $settings['seatmap_color'] ) : '';
$halle = trim( (string) $settings['venue_name'] );

$beschriftung = sprintf(
	/* translators: %s: Name der Halle. */
	__( 'Saalplan %s: vorn die Bühne, davor fünf Blöcke mit den Plätzen 101 bis 550, rechts die Seitenplätze 601 bis 621.', 'theater-auw' ),
	'' !== $halle ? $halle : __( 'der Halle', 'theater-auw' )
);
?>
<div class="tha-seatplan-scroll">
	<div class="tha-seatplan"
		role="img"
		aria-label="<?php echo esc_attr( $beschriftung ); ?>"
		<?php if ( '' !== $farbe ) : ?>style="--seatplan-accent: <?php echo esc_attr( $farbe ); ?>"<?php endif; ?>>

		<div class="tha-seatplan__stage"><?php esc_html_e( 'Bühne', 'theater-auw' ); ?></div>

		<div class="tha-seatplan__blocks">

			<?php foreach ( $bloecke as $basis ) : ?>
				<div class="tha-seatplan__block">

					<?php
					// Vorn und hinten sind zwei Gruppen, weil dazwischen der
					// Gang liegt — im Plan als grösserer Abstand.
					$abschnitte = array(
						array( 'von' => $basis + 1,  'bis' => $basis + 24, 'klasse' => '' ),
						array( 'von' => $basis + 25, 'bis' => $basis + 48, 'klasse' => ' tha-seatplan__group--hinten' ),
					);
					?>
					<?php foreach ( $abschnitte as $abschnitt ) : ?>
						<div class="tha-seatplan__group<?php echo esc_attr( $abschnitt['klasse'] ); ?>">
							<?php for ( $nr = $abschnitt['von']; $nr < $abschnitt['bis']; $nr += 2 ) : ?>
								<div class="tha-seatplan__row">
									<span class="tha-seatplan__seat"><?php echo esc_html( (string) $nr ); ?></span>
									<span class="tha-seatplan__seat"><?php echo esc_html( (string) ( $nr + 1 ) ); ?></span>
								</div>
							<?php endfor; ?>

							<?php if ( $basis + 25 === $abschnitt['von'] ) : ?>
								<?php
								// Die letzten beiden sitzen nebeneinander in
								// einer Zelle — genau wie im Programmheft.
								?>
								<div class="tha-seatplan__row">
									<span class="tha-seatplan__seat tha-seatplan__seat--doppel">
										<?php echo esc_html( ( $basis + 49 ) . ' ' . ( $basis + 50 ) ); ?>
									</span>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>

			<div class="tha-seatplan__block tha-seatplan__block--seite">
				<?php foreach ( $seitenreihe as $gruppe ) : ?>
					<div class="tha-seatplan__group tha-seatplan__group--seite-<?php echo (int) $gruppe['versatz']; ?>">
						<?php for ( $nr = $gruppe['von']; $nr <= $gruppe['bis']; $nr++ ) : ?>
							<div class="tha-seatplan__row">
								<span class="tha-seatplan__seat"><?php echo esc_html( (string) $nr ); ?></span>
							</div>
						<?php endfor; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
