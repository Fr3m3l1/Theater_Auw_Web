<?php
/**
 * Die Speisekarte — Speisen und Getränke als Zeilen.
 *
 * Warum nicht als Scan aus dem Programmheft: ein Bild kann niemand vorlesen
 * lassen, niemand durchsuchen, und auf dem Handy muss man es aufziehen.
 * Dieselbe Entscheidung wie bei den Eintrittspreisen — und bewusst dieselbe
 * Darstellung, damit «Gut zu wissen» und die Speisekarte gleich sprechen.
 *
 * Zwei Spalten: was aus der Küche kommt, und was aus dem Keller. Steht nur
 * eine der beiden Karten da, nimmt sie die ganze Breite — dafür sorgt
 * `auto-fit` im CSS, hier braucht es keine Sonderbehandlung.
 *
 * @var array $ctx
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;

$spalten = array();
foreach ( array( 'speisen' => __( 'Speisen', 'theater-auw' ), 'getraenke' => __( 'Getränke', 'theater-auw' ) ) as $kind => $titel ) {
	$rows = Tha_Settings::menu_rows( $kind, $settings );
	if ( $rows ) {
		$spalten[] = array(
			'title' => $titel,
			'rows'  => $rows,
		);
	}
}

$intro = trim( (string) $settings['menu_intro'] );

// Ohne eigenen Einleitungstext steht die Küchen-Notiz aus den Einstellungen
// da. Auf einer Speisekarte ist «ab wann gibt es etwas» die zweite Frage
// direkt nach «was gibt es» — und die Antwort ist schon erfasst.
if ( '' === $intro ) {
	$intro = trim( (string) $settings['food_note'] );
}

if ( ! Tha_Settings::has_menu( $settings ) ) {
	// Noch keine Zeile erfasst. Für Besucherinnen und Besucher verschwindet der
	// Abschnitt spurlos — eine leere Karte wäre schlimmer als keine. Wer die
	// Seite verwaltet, soll aber nicht rätseln müssen, warum nichts kommt.
	if ( '' !== $intro ) {
		?>
		<section class="tha-section tha-section--card" id="karte<?php echo esc_attr( $ctx['uid'] ); ?>">
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
					<?php esc_html_e( 'Auf der Speisekarte steht noch nichts. Unter Theater Auw → Speisekarte trägst du Gerichte und Getränke als Zeilen ein. Diesen Hinweis sehen nur angemeldete Administratoren.', 'theater-auw' ); ?>
				</p>
			</div>
		</section>
		<?php
	}
	return;
}
?>
<section class="tha-section tha-section--card" id="karte<?php echo esc_attr( $ctx['uid'] ); ?>">
	<div class="tha-wrap">

		<?php if ( '' !== $intro ) : ?>
			<div class="tha-prose"><p><?php echo wp_kses_post( $intro ); ?></p></div>
		<?php endif; ?>

		<div class="tha-karte">
			<?php foreach ( $spalten as $spalte ) : ?>
				<div class="tha-karte__col">
					<h3 class="tha-h3"><?php echo esc_html( $spalte['title'] ); ?></h3>

					<?php
					// Nach Gang bündeln — «Vorspeise», «Hauptgang», «Dessert».
					// Anders als bei den Eintrittspreisen bekommt eine Gruppe
					// ihre Zwischenzeile auch dann, wenn nur ein Gericht
					// darunter steht: ein Gang mit einem Gericht ist trotzdem
					// ein Gang.
					$gaenge = array();
					foreach ( $spalte['rows'] as $row ) {
						$gaenge[ isset( $row['group'] ) ? $row['group'] : '' ][] = $row;
					}
					?>
					<div class="tha-prices tha-prices--karte">
						<?php foreach ( $gaenge as $name => $zeilen ) : ?>

							<?php if ( '' !== $name ) : ?>
								<?php
								// Bewusst ein <div> statt <p>: Themes schreiben
								// gern `p { margin: 0 !important }`, und die
								// Zwischenzeile klebte dann an der Zeile
								// darüber. Wie bei den Preisen.
								?>
								<div class="tha-prices__group"><?php echo esc_html( $name ); ?></div>
							<?php endif; ?>

							<dl class="tha-prices__list">
								<?php foreach ( $zeilen as $row ) : ?>
									<div class="tha-prices__row">
										<dt>
											<?php echo esc_html( $row['label'] ); ?>
											<?php if ( '' !== $row['note'] ) : ?>
												<span class="tha-prices__note"><?php echo esc_html( $row['note'] ); ?></span>
											<?php endif; ?>
										</dt>
										<?php if ( '' !== trim( (string) $row['amount'] ) ) : ?>
											<dd><?php echo esc_html( $row['amount'] ); ?></dd>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</dl>

						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( '' !== trim( (string) $settings['menu_note'] ) ) : ?>
			<p class="tha-note"><?php echo esc_html( $settings['menu_note'] ); ?></p>
		<?php endif; ?>

		<?php
		// Wann gibt es das Essen? Steht schon unter «Ort & Ablauf» — hier noch
		// einmal, weil genau diese Frage auf der Speisekarte gestellt wird.
		$von = trim( (string) $settings['food_from'] );
		$bis = trim( (string) $settings['food_to'] );
		?>
		<?php if ( '' !== $von && '' !== $bis ) : ?>
			<p class="tha-tags">
				<span class="tha-tag">
					<?php
					printf(
						/* translators: 1: Uhrzeit von, 2: Uhrzeit bis */
						esc_html__( 'Essen von %1$s bis %2$s Uhr', 'theater-auw' ),
						esc_html( $von ),
						esc_html( $bis )
					);
					?>
				</span>
				<span class="tha-tag"><?php esc_html_e( 'Bargeld', 'theater-auw' ); ?></span>
				<span class="tha-tag"><?php esc_html_e( 'Karte', 'theater-auw' ); ?></span>
				<span class="tha-tag"><?php esc_html_e( 'Twint', 'theater-auw' ); ?></span>
			</p>
		<?php endif; ?>
	</div>
</section>
