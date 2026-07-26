<?php
/**
 * Deinstallation. Die Einstellungen bleiben absichtlich stehen: wer das Plugin
 * kurz deaktiviert und neu hochlädt, soll nicht alle Spieldaten neu erfassen.
 * Nur die geplante Aufgabe wird abgeräumt.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

wp_clear_scheduled_hook( 'theater_auw_daily_refresh' );
