<?php
/**
 * Einstellungsseite mit Tabs.
 *
 * @var array  $settings
 * @var string $tab
 * @var array  $tabs
 */

defined( 'ABSPATH' ) || exit;

$status  = Tha_Admin::status_explanation( $settings );
$base    = admin_url( 'admin.php?page=' . Tha_Admin::SLUG );
$page_ok = ! empty( $settings['page_id'] ) ? get_permalink( $settings['page_id'] ) : '';
?>
<div class="wrap tha-admin">

	<h1><?php esc_html_e( 'Theater Auw', 'theater-auw' ); ?></h1>

	<!-- Status: welche Phase gilt gerade, und warum -->
	<div class="tha-status tha-status--<?php echo esc_attr( $status['phase'] ); ?>">
		<div class="tha-status__main">
			<span class="tha-status__kicker"><?php esc_html_e( 'Aktuell auf der Website', 'theater-auw' ); ?></span>
			<strong class="tha-status__phase"><?php echo esc_html( Tha_Admin::phase_label( $status['phase'] ) ); ?></strong>
			<span class="tha-status__hint"><?php echo esc_html( Tha_Admin::phase_hint( $status['phase'] ) ); ?></span>
		</div>
		<div class="tha-status__why">
			<p><?php echo esc_html( $status['reason'] ); ?></p>
			<?php if ( $page_ok ) : ?>
				<p class="tha-status__links">
					<?php esc_html_e( 'Vorschau:', 'theater-auw' ); ?>
					<?php foreach ( Tha_Settings::PHASES as $p ) : ?>
						<a href="<?php echo esc_url( add_query_arg( 'tha_phase', $p, $page_ok ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( Tha_Admin::phase_label( $p ) ); ?></a>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( empty( $settings['ticket_url'] ) ) : ?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'Ticketleo-Link fehlt noch.', 'theater-auw' ); ?></strong>
				<?php esc_html_e( 'Solange kein Link hinterlegt ist, zeigen die Ticket-Buttons «Ticketlink folgt» und sind nicht klickbar. Trage die Event-URL unter «Phasen & Tickets» ein, sobald Ticketleo für die neue Saison eingerichtet ist.', 'theater-auw' ); ?>
			</p>
		</div>
	<?php endif; ?>

	<h2 class="nav-tab-wrapper">
		<?php foreach ( $tabs as $slug => $label ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'tab', $slug, $base ) ); ?>"
				class="nav-tab <?php echo $tab === $slug ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
	</h2>

	<form method="post" action="options.php">
		<?php settings_fields( Tha_Settings::GROUP ); ?>

		<?php
		$view = THA_DIR . 'admin/views/tab-' . $tab . '.php';
		if ( file_exists( $view ) ) {
			include $view;
		}
		?>

		<?php submit_button( __( 'Änderungen speichern', 'theater-auw' ) ); ?>
	</form>

	<hr>

	<h2><?php esc_html_e( 'Einbau in die Seite', 'theater-auw' ); ?></h2>
	<p>
		<?php esc_html_e( 'Setze auf der Theater-Seite einen Shortcode-Block mit:', 'theater-auw' ); ?>
		<code>[theater_auw]</code>
	</p>
	<p class="description">
		<?php esc_html_e( 'Alternativ gibt es im Block-Editor den Block «Theater Auw». Kopfzeile, Navigation und Footer bleiben die deiner Website — der Block rendert nur den Theater-Inhalt.', 'theater-auw' ); ?>
	</p>
</div>
