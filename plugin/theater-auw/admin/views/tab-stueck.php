<?php
/**
 * Tab — Stück & Texte.
 *
 * Drei Blöcke in der Reihenfolge, in der sie auf der Seite stehen: das Stück,
 * der Abend, der Verein. Vorher stand unter «Angaben aus dem Programmheft»
 * alles beieinander, was sonst nirgends passte — Scans, Kontaktadresse und
 * zwei Jahreszahlen.
 *
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;
?>
<h2><?php esc_html_e( 'Das Stück', 'theater-auw' ); ?></h2>
<table class="form-table" role="presentation">
	<?php
	Tha_Admin::text_field( 'season_year', $settings['season_year'], __( 'Saison', 'theater-auw' ), __( 'Jahreszahl, z.B. 2026.', 'theater-auw' ), 'number' );
	Tha_Admin::text_field( 'play_title', $settings['play_title'], __( 'Titel', 'theater-auw' ), __( 'Ohne Anführungszeichen — die «Guillemets» setzt die Seite selbst.', 'theater-auw' ) );
	Tha_Admin::text_field( 'play_genre', $settings['play_genre'], __( 'Gattung', 'theater-auw' ), __( 'Zum Beispiel «Lustspiel in 3 Akten».', 'theater-auw' ) );
	Tha_Admin::text_field( 'play_author', $settings['play_author'], __( 'Autor', 'theater-auw' ) );
	Tha_Admin::text_field( 'play_director', $settings['play_director'], __( 'Regie', 'theater-auw' ) );
	Tha_Admin::textarea_field( 'play_synopsis', $settings['play_synopsis'], __( 'Text zum Stück', 'theater-auw' ), __( 'Leerzeile trennt Absätze. Per Du schreiben, wie gewohnt.', 'theater-auw' ) );
	Tha_Admin::image_field( 'stueck_image_id', $settings['stueck_image_id'], __( 'Bild', 'theater-auw' ), __( 'Ensemble- oder Probenbild, steht neben dem Text. Ohne Auswahl zeigt die Seite einen gestalteten Platzhalter — nie einen leeren Rahmen.', 'theater-auw' ) );
	?>
</table>

<h2><?php esc_html_e( 'Ort und Ablauf', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:74ch">
	<?php esc_html_e( 'Was rund um die Vorstellung passiert. Die Eintrittspreise stehen nicht hier, sondern unter «Preise & Infos» — dort als Zeilen mit Beträgen.', 'theater-auw' ); ?>
</p>
<table class="form-table" role="presentation">
	<?php
	Tha_Admin::text_field( 'venue_name', $settings['venue_name'], __( 'Ort', 'theater-auw' ) );
	Tha_Admin::text_field( 'venue_maps_url', $settings['venue_maps_url'], __( 'Karten-Link', 'theater-auw' ), __( 'Optional — Link auf Google Maps o.ä. für «Anfahrt und Parkplätze».', 'theater-auw' ), 'url' );
	Tha_Admin::text_field( 'doors_time', $settings['doors_time'], __( 'Türöffnung', 'theater-auw' ), __( 'Schweizer Schreibweise mit Punkt: <code>18.30</code>.', 'theater-auw' ) );
	Tha_Admin::text_field( 'food_from', $settings['food_from'], __( 'Essen ab', 'theater-auw' ) );
	Tha_Admin::text_field( 'food_to', $settings['food_to'], __( 'Essen bis', 'theater-auw' ) );
	Tha_Admin::textarea_field( 'food_note', $settings['food_note'], __( 'Text «Essen»', 'theater-auw' ), __( 'Die ganze Karte steht unter «Speisekarte».', 'theater-auw' ) );
	Tha_Admin::textarea_field( 'tombola_note', $settings['tombola_note'], __( 'Text «Tombola»', 'theater-auw' ), __( 'Leer lassen, wenn es keine Tombola gibt — die Karte verschwindet dann.', 'theater-auw' ) );
	Tha_Admin::image_field( 'tombola_image_id', $settings['tombola_image_id'], __( 'Bild «Tombola»', 'theater-auw' ), __( 'Optional, zum Beispiel die Preise. Klein in der Karte, per Klick gross.', 'theater-auw' ) );
	?>
</table>

<h2><?php esc_html_e( 'Der Verein', 'theater-auw' ); ?></h2>
<p class="description" style="max-width:74ch">
	<?php esc_html_e( 'Steht einmal da und bleibt dann jahrelang stehen.', 'theater-auw' ); ?>
</p>
<table class="form-table" role="presentation">
	<?php
	Tha_Admin::text_field( 'contact_email', $settings['contact_email'], __( 'Kontakt-E-Mail', 'theater-auw' ), __( 'Steht im Abschluss der Seite.', 'theater-auw' ), 'email' );
	Tha_Admin::text_field( 'founded_year', $settings['founded_year'], __( 'Auf der Bühne seit', 'theater-auw' ), '', 'number' );
	Tha_Admin::text_field( 'next_year', $settings['next_year'], __( 'Nächste Saison', 'theater-auw' ), __( 'Wird in der Danke-Phase als «bis …» genannt.', 'theater-auw' ), 'number' );
	?>
</table>
