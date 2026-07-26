<?php
/**
 * Ticketleo-Handoff.
 *
 * Auf dieser Seite wird nie verkauft. Jeder Ticket-Button ist ein echtes
 * <a href> auf Ticketleo — der Dialog ist reiner Komfort und erklärt nur, wohin
 * es geht, damit eine fremde Domain niemanden überrascht. Ohne JavaScript wird
 * er nie geöffnet und der Link funktioniert trotzdem.
 *
 * @var array $ctx
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;

if ( ! $settings['ticket_url'] && ! array_filter( wp_list_pluck( $settings['dates'], 'ticket_url' ) ) ) {
	return;
}
?>
<div class="tha-dialog" data-tha-dialog hidden>
	<div class="tha-dialog__scrim" data-tha-dialog-close></div>
	<div class="tha-dialog__panel" role="dialog" aria-modal="true" aria-labelledby="tha-dialog-title<?php echo esc_attr( $ctx['uid'] ); ?>">
		<div class="tha-dialog__head">
			<h2 class="tha-h3" id="tha-dialog-title<?php echo esc_attr( $ctx['uid'] ); ?>"><?php esc_html_e( 'Weiter zu Ticketleo', 'theater-auw' ); ?></h2>
			<button type="button" class="tha-dialog__close" data-tha-dialog-close aria-label="<?php esc_attr_e( 'Schliessen', 'theater-auw' ); ?>">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
			</button>
		</div>

		<div class="tha-dialog__body">
			<p>
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: Reservieren oder Kaufen */
						__( 'Der Ticketverkauf läuft über unseren Partner Ticketleo. Dort wählst du Vorstellung, Plätze und Kategorie — die Bestätigung kommt direkt per E-Mail.', 'theater-auw' ),
						$ctx['cta_label']
					)
				);
				?>
			</p>
			<p class="tha-tags">
				<span class="tha-tag"><?php esc_html_e( 'Platzwahl im Saalplan', 'theater-auw' ); ?></span>
				<span class="tha-tag"><?php esc_html_e( 'Kat. A / Kat. B', 'theater-auw' ); ?></span>
			</p>
			<?php if ( $settings['ticket_note'] ) : ?>
				<p class="tha-note"><?php echo esc_html( $settings['ticket_note'] ); ?></p>
			<?php endif; ?>
		</div>

		<div class="tha-dialog__actions">
			<button type="button" class="tha-btn tha-btn--secondary" data-tha-dialog-close><?php esc_html_e( 'Abbrechen', 'theater-auw' ); ?></button>
			<a class="tha-btn tha-btn--primary" data-tha-dialog-go href="<?php echo esc_url( $settings['ticket_url'] ); ?>" target="_blank" rel="noopener">
				<?php esc_html_e( 'Zu Ticketleo', 'theater-auw' ); ?>
				<?php Tha_Renderer::part( 'partial-icon-external', $ctx ); ?>
			</a>
		</div>
	</div>
</div>
