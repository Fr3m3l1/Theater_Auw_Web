<?php
/**
 * Plugin Name:       Theater Auw
 * Plugin URI:        https://stvauw.ch/theater/
 * Description:       Die Theater-Seite des STV Auw mit vier Saison-Phasen (Vorbereitung, Vorverkauf, Spielzeit, Danke). Einbinden mit dem Shortcode [theater_auw] oder dem Block «Theater Auw».
 * Version:           1.9.3
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Theater Auw
 * Text Domain:       theater-auw
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'THA_VERSION', '1.9.3' );
define( 'THA_FILE', __FILE__ );
define( 'THA_DIR', plugin_dir_path( __FILE__ ) );
define( 'THA_URL', plugin_dir_url( __FILE__ ) );

require_once THA_DIR . 'includes/helpers.php';
require_once THA_DIR . 'includes/class-tha-settings.php';
require_once THA_DIR . 'includes/class-tha-phase.php';
require_once THA_DIR . 'includes/class-tha-pagechrome.php';
require_once THA_DIR . 'includes/class-tha-groups.php';
require_once THA_DIR . 'includes/class-tha-rueckblick.php';
require_once THA_DIR . 'includes/class-tha-chronik.php';
require_once THA_DIR . 'includes/class-tha-renderer.php';
require_once THA_DIR . 'includes/class-tha-assets.php';
require_once THA_DIR . 'includes/class-tha-shortcode.php';
require_once THA_DIR . 'includes/class-tha-block.php';

if ( is_admin() ) {
	require_once THA_DIR . 'admin/class-tha-admin.php';
}

/**
 * Erststart: Standardwerte inklusive der sechs Spieldaten schreiben, damit die
 * Seite nie leer rendert.
 */
function tha_activate() {
	if ( false === get_option( Tha_Settings::OPTION ) ) {
		add_option( Tha_Settings::OPTION, Tha_Settings::defaults() );
	}
}
register_activation_hook( __FILE__, 'tha_activate' );

function tha_deactivate() {
	wp_clear_scheduled_hook( 'theater_auw_daily_refresh' );
}
register_deactivation_hook( __FILE__, 'tha_deactivate' );

/**
 * Verdrahtung. Bewusst schlank gehalten — jede Klasse hängt sich selbst ein.
 */
function tha_bootstrap() {
	load_plugin_textdomain( 'theater-auw', false, dirname( plugin_basename( THA_FILE ) ) . '/languages' );

	Tha_Settings::upgrade();
	Tha_Assets::init();
	Tha_Pagechrome::init();
	Tha_Shortcode::init();
	Tha_Block::init();

	if ( is_admin() ) {
		Tha_Admin::init();
	}

	// Ein Caching-Plugin würde die Phase sonst einfrieren: einmal pro Nacht
	// die Theater-Seite aus dem Cache werfen, damit ein geplanter Phasen-
	// wechsel um Mitternacht auch wirklich sichtbar wird.
	if ( ! wp_next_scheduled( 'theater_auw_daily_refresh' ) ) {
		wp_schedule_event( tha_next_local_midnight_utc(), 'daily', 'theater_auw_daily_refresh' );
	}
	add_action( 'theater_auw_daily_refresh', array( 'Tha_Settings', 'purge_caches' ) );

	// Kommt ein altes Foto dazu oder verschwindet eines, muss die Zuordnung
	// der Chronik-Bilder neu gebildet werden — sonst bliebe sie einen Tag
	// lang falsch, und genau dann schaut jemand hin.
	add_action( 'add_attachment', array( 'Tha_Chronik', 'forget_images' ) );
	add_action( 'edit_attachment', array( 'Tha_Chronik', 'forget_images' ) );
	add_action( 'delete_attachment', array( 'Tha_Chronik', 'forget_images' ) );
	add_action( 'theater_auw_daily_refresh', array( 'Tha_Chronik', 'forget_images' ) );
}
add_action( 'plugins_loaded', 'tha_bootstrap' );
