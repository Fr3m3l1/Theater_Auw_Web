<?php
/**
 * Tab — Unterseiten (Bildergruppen).
 *
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;

$types = array(
	'personen' => __( 'Personen (Porträts, hochkant)', 'theater-auw' ),
	'logos'    => __( 'Logos (Sponsoren, auf weisser Karte)', 'theater-auw' ),
	'bilder'   => __( 'Bilder (Querformat)', 'theater-auw' ),
);
?>
<h2><?php esc_html_e( 'Unterseiten', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:74ch">
	<?php esc_html_e( 'Für Seiten wie «Die Schauspieler», «Hinter den Kulissen», «Organisation Komitee» und «Sponsoren». Du wählst hier nur die Bilder aus der Mediathek — die Namen stehen bereits in den Bildern und werden übernommen (Bildunterschrift, sonst Alternativtext, sonst Titel).', 'theater-auw' ); ?>
</p>
<p class="description" style="max-width:74ch">
	<?php
	printf(
		/* translators: %s: Beispiel-Schreibweise */
		esc_html__( 'Soll neben dem Namen eine Rolle stehen, schreibe die Bildunterschrift in der Mediathek als %s.', 'theater-auw' ),
		'<code>Franz Troxler | Grilleur</code>'
	);
	?>
</p>

<div class="tha-groups" data-tha-repeat="groups">
	<div data-tha-repeat-body>
		<?php foreach ( $settings['groups'] as $i => $group ) : ?>
			<?php $base = Tha_Settings::OPTION . '[groups][' . (int) $i . ']'; ?>
			<div class="tha-group" data-tha-repeat-row>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Schlüssel', 'theater-auw' ); ?></th>
						<td>
							<input type="text" size="16" name="<?php echo esc_attr( $base ); ?>[key]" value="<?php echo esc_attr( $group['key'] ); ?>">
							<code>[theater_auw_gruppe key="<?php echo esc_html( $group['key'] ); ?>"]</code>
							<p class="description"><?php esc_html_e( 'Damit wird die Gruppe auf einer Seite eingebunden. Nur Kleinbuchstaben.', 'theater-auw' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Titel', 'theater-auw' ); ?></th>
						<td>
							<input type="text" class="regular-text" name="<?php echo esc_attr( $base ); ?>[title]" value="<?php echo esc_attr( $group['title'] ); ?>">
							<input type="text" class="regular-text" name="<?php echo esc_attr( $base ); ?>[kicker]" value="<?php echo esc_attr( $group['kicker'] ); ?>" placeholder="<?php esc_attr_e( 'Überzeile', 'theater-auw' ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Einleitung', 'theater-auw' ); ?></th>
						<td><textarea rows="3" class="large-text" name="<?php echo esc_attr( $base ); ?>[intro]"><?php echo esc_textarea( $group['intro'] ); ?></textarea></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Darstellung', 'theater-auw' ); ?></th>
						<td>
							<select name="<?php echo esc_attr( $base ); ?>[type]" data-tha-type>
								<?php foreach ( $types as $val => $label ) : ?>
									<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $val, $group['type'] ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
							<label style="margin-left:12px">
								<?php esc_html_e( 'Spalten', 'theater-auw' ); ?>
								<input type="number" min="1" max="8" style="width:4em" name="<?php echo esc_attr( $base ); ?>[columns]" value="<?php echo esc_attr( $group['columns'] ); ?>">
							</label>
							<label style="margin-left:12px">
								<input type="checkbox" name="<?php echo esc_attr( $base ); ?>[dark]" value="1" <?php checked( ! empty( $group['dark'] ) ); ?>>
								<?php esc_html_e( 'dunkler Hintergrund', 'theater-auw' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Reihenfolge', 'theater-auw' ); ?></th>
						<td>
							<?php $sortierung = isset( $group['sort'] ) ? $group['sort'] : 'manuell'; ?>
							<select name="<?php echo esc_attr( $base ); ?>[sort]">
								<option value="manuell" <?php selected( 'manuell', $sortierung ); ?>><?php esc_html_e( 'Wie ausgewählt', 'theater-auw' ); ?></option>
								<option value="alpha" <?php selected( 'alpha', $sortierung ); ?>><?php esc_html_e( 'Alphabetisch nach Name', 'theater-auw' ); ?></option>
							</select>
							<p class="description">
								<?php esc_html_e( 'Bei Sponsoren ist alphabetisch das Fairste — sonst steht eine Firma vorn, nur weil sie zufällig zuerst angeklickt wurde. Umlaute werden dabei richtig einsortiert: «Ägerter» steht vor «Amrein», nicht hinter «Zurkirchen».', 'theater-auw' ); ?>
							</p>
							<p class="description">
								<?php esc_html_e( 'Bei den Schauspielern lohnt sich «Wie ausgewählt»: dort ist die Reihenfolge redaktionell — Hauptrollen zuerst.', 'theater-auw' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Gruppenbild', 'theater-auw' ); ?></th>
						<td>
							<div class="tha-media-field" data-tha-media>
								<div class="tha-media-field__preview">
									<?php $lead = wp_get_attachment_image_url( (int) $group['lead_image_id'], 'medium' ); ?>
									<?php if ( $lead ) : ?>
										<img src="<?php echo esc_url( $lead ); ?>" alt="">
									<?php endif; ?>
								</div>
								<input type="hidden" name="<?php echo esc_attr( $base ); ?>[lead_image_id]" value="<?php echo esc_attr( (int) $group['lead_image_id'] ); ?>">
								<p>
									<button type="button" class="button" data-tha-media-pick><?php esc_html_e( 'Bild wählen', 'theater-auw' ); ?></button>
									<button type="button" class="button-link" data-tha-media-clear><?php esc_html_e( 'Entfernen', 'theater-auw' ); ?></button>
								</p>
								<p>
									<select name="<?php echo esc_attr( $base ); ?>[lead_layout]">
										<option value="neben" <?php selected( 'neben', $group['lead_layout'] ); ?>><?php esc_html_e( 'Neben dem Einleitungstext (empfohlen)', 'theater-auw' ); ?></option>
										<option value="oben" <?php selected( 'oben', $group['lead_layout'] ); ?>><?php esc_html_e( 'Breit über dem Raster', 'theater-auw' ); ?></option>
									</select>
								</p>
								<input type="text" class="regular-text" name="<?php echo esc_attr( $base ); ?>[lead_caption]"
									value="<?php echo esc_attr( $group['lead_caption'] ); ?>"
									placeholder="<?php esc_attr_e( 'Bildunterschrift (optional)', 'theater-auw' ); ?>">
								<p class="description"><?php esc_html_e( 'Das Ensemble- oder Teamfoto. Wird nie beschnitten. Neben dem Einleitungstext bleibt es angenehm gross statt seitenfüllend. Eine Gruppe darf auch nur aus diesem einen Bild bestehen.', 'theater-auw' ); ?></p>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Einzelbilder', 'theater-auw' ); ?></th>
						<td>
							<div class="tha-gallery-field" data-tha-gallery
								data-name-base="<?php echo esc_attr( $base ); ?>"
								data-type="<?php echo esc_attr( $group['type'] ); ?>">

								<input type="hidden" name="<?php echo esc_attr( $base ); ?>[ids]" value="<?php echo esc_attr( implode( ',', (array) $group['ids'] ) ); ?>" data-tha-gallery-ids>

								<div class="tha-cards" data-tha-gallery-cards
									data-empty="<?php esc_attr_e( 'Noch keine Bilder gewählt.', 'theater-auw' ); ?>">
									<?php foreach ( (array) $group['ids'] as $id ) : ?>
										<?php
										$id      = (int) $id;
										$src     = wp_get_attachment_image_url( $id, 'thumbnail' );
										$auto    = Tha_Groups::label_for( $id );
										$eigen   = Tha_Groups::normalize_label( isset( $group['labels'][ $id ] ) ? $group['labels'][ $id ] : '' );
										$is_wide = in_array( $id, array_map( 'absint', (array) $group['wide'] ), true );
										$feld    = $base . '[labels][' . $id . ']';
										// Das Textfeld steht nur im Weg, solange nichts drinsteht.
										$hat_text = ( '' !== $eigen['text'] || '' !== $auto['text'] );
										// Bei den Sponsoren steht in derselben Zeile der Ort statt
										// einer Rolle — «Amrein Metzgerei» und darunter «Auw».
										$rolle_leer = 'logos' === $group['type'] ? __( 'Ort', 'theater-auw' ) : __( 'Rolle', 'theater-auw' );
										?>
										<div class="tha-card<?php echo $hat_text ? ' tha-card--text' : ''; ?>" data-tha-card data-id="<?php echo esc_attr( $id ); ?>">
											<span class="tha-card__grip" data-tha-card-grip title="<?php esc_attr_e( 'Zum Verschieben ziehen', 'theater-auw' ); ?>" aria-hidden="true"></span>

											<button type="button" class="tha-card__thumb" data-tha-card-swap
												title="<?php esc_attr_e( 'Anderes Bild wählen', 'theater-auw' ); ?>">
												<?php if ( $src ) : ?>
													<img src="<?php echo esc_url( $src ); ?>" alt="">
												<?php else : ?>
													<span class="tha-card__missing"><?php esc_html_e( 'Bild fehlt', 'theater-auw' ); ?></span>
												<?php endif; ?>
											</button>

											<div class="tha-card__fields">
												<input type="text" class="tha-card__f tha-card__f--name<?php echo '' !== $auto['name'] ? ' tha-card__f--auto' : ''; ?>"
													name="<?php echo esc_attr( $feld ); ?>[name]"
													value="<?php echo esc_attr( $eigen['name'] ); ?>"
													placeholder="<?php echo esc_attr( '' !== $auto['name'] ? $auto['name'] : __( 'Name', 'theater-auw' ) ); ?>">

												<input type="text" class="tha-card__f tha-card__f--role<?php echo '' !== $auto['role'] ? ' tha-card__f--auto' : ''; ?>"
													name="<?php echo esc_attr( $feld ); ?>[role]"
													value="<?php echo esc_attr( $eigen['role'] ); ?>"
													data-auto="<?php echo esc_attr( $auto['role'] ); ?>"
													placeholder="<?php echo esc_attr( '' !== $auto['role'] ? $auto['role'] : $rolle_leer ); ?>">

												<input type="text" class="tha-card__f tha-card__f--text<?php echo '' !== $auto['text'] ? ' tha-card__f--auto' : ''; ?>"
													name="<?php echo esc_attr( $feld ); ?>[text]"
													value="<?php echo esc_attr( $eigen['text'] ); ?>"
													placeholder="<?php echo esc_attr( '' !== $auto['text'] ? $auto['text'] : __( 'Text oder Zitat', 'theater-auw' ) ); ?>">

												<div class="tha-card__tools">
													<label class="tha-card__wide">
														<input type="checkbox" value="1"
															name="<?php echo esc_attr( $base ); ?>[wide][<?php echo esc_attr( $id ); ?>]"
															<?php checked( $is_wide ); ?>>
														<?php esc_html_e( 'gross', 'theater-auw' ); ?>
													</label>
													<button type="button" class="button-link tha-card__more" data-tha-card-more><?php esc_html_e( 'Text', 'theater-auw' ); ?></button>
													<button type="button" class="button-link tha-card__drop" data-tha-card-drop><?php esc_html_e( 'Entfernen', 'theater-auw' ); ?></button>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>

								<p>
									<button type="button" class="button" data-tha-gallery-pick><?php esc_html_e( 'Bilder hinzufügen', 'theater-auw' ); ?></button>
									<button type="button" class="button-link tha-drop" data-tha-gallery-clear><?php esc_html_e( 'Alle entfernen', 'theater-auw' ); ?></button>
								</p>

								<p class="description">
									<?php esc_html_e( 'Auf das Bild klicken tauscht es aus — die Beschriftung bleibt stehen. Am Griff links ziehst du eine Karte an ihren Platz.', 'theater-auw' ); ?>
								</p>
								<p class="description">
									<?php esc_html_e( 'Der graue Text in den Feldern ist die Beschriftung aus dem Bild; sie wird automatisch genommen. Schreib in ein Feld, um nur dieses eine für diese Gruppe zu ersetzen — den Firmennamen etwa, ohne alles andere neu zu tippen. Das Bild selbst bleibt unverändert; Feld wieder leeren stellt die automatische Beschriftung her.', 'theater-auw' ); ?>
								</p>
								<p class="description">
									<?php esc_html_e( 'Das mittlere Feld ist die Zeile unter dem Namen: bei den Schauspielern die Rolle, bei den Sponsoren der Ort. Sie erscheint auf der Seite, sobald etwas drinsteht — hier oder in der Mediathek.', 'theater-auw' ); ?>
								</p>
								<p class="description">
									<?php esc_html_e( '«gross» stellt ein Bild doppelt so breit und behält sein Format — für Gruppenaufnahmen zwischen Einzelporträts, etwa das Tombolateam.', 'theater-auw' ); ?>
								</p>
							</div>
						</td>
					</tr>
				</table>

				<p><button type="button" class="button-link tha-drop" data-tha-repeat-drop><?php esc_html_e( 'Diese Gruppe entfernen', 'theater-auw' ); ?></button></p>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<p><button type="button" class="button" data-tha-repeat-add><?php esc_html_e( 'Gruppe hinzufügen', 'theater-auw' ); ?></button></p>

<h2><?php esc_html_e( 'So baust du eine Unterseite', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:74ch">
	<?php esc_html_e( 'Auf der Seite «Die Schauspieler» einen Shortcode-Block einfügen mit:', 'theater-auw' ); ?>
</p>
<pre style="background:#fff;border:1px solid #dcdcde;padding:12px;max-width:74ch;overflow:auto">[theater_auw_seite titel="Die Schauspieler" gruppe="schauspieler"]
Hier kann ganz normaler Text stehen — er erscheint unter der Galerie.
[/theater_auw_seite]</pre>
<p class="description" style="max-width:74ch">
	<?php esc_html_e( 'Das gibt der Unterseite dieselbe Navigation und dieselbe Bildsprache wie der Hauptseite, aber einen kompakteren Kopf. Brauchst du nur die Galerie ohne Rahmen, nimm stattdessen den kurzen Shortcode oben bei der jeweiligen Gruppe.', 'theater-auw' ); ?>
</p>
