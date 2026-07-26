<?php
/**
 * Minimal-Attrappen der WordPress-Funktionen, die die Templates benutzen.
 *
 * Zweck: die ECHTEN Templates aus plugin/theater-auw/public/views/ ausserhalb
 * von WordPress rendern zu können. Dadurch gibt es keine zweite, nachgebaute
 * Vorschau, die mit der Zeit auseinanderdriftet — Vorschau und Live-Seite
 * erzeugen dasselbe Markup.
 *
 * Das ist ausdrücklich KEIN WordPress-Ersatz. Nur so viel, wie die Views
 * brauchen.
 */

define( 'ABSPATH', __DIR__ . '/' );

// ---------------------------------------------------------------------
// Escaping / Ausgabe
// ---------------------------------------------------------------------

function esc_html( $t ) {
	return htmlspecialchars( (string) $t, ENT_QUOTES, 'UTF-8' );
}
function esc_attr( $t ) {
	return htmlspecialchars( (string) $t, ENT_QUOTES, 'UTF-8' );
}
function esc_textarea( $t ) {
	return htmlspecialchars( (string) $t, ENT_QUOTES, 'UTF-8' );
}
function esc_url( $u ) {
	return htmlspecialchars( (string) $u, ENT_QUOTES, 'UTF-8' );
}
function esc_url_raw( $u ) {
	return trim( (string) $u );
}
function wp_kses_post( $t ) {
	return (string) $t;
}

// ---------------------------------------------------------------------
// i18n — die Vorschau ist einsprachig, also identity
// ---------------------------------------------------------------------

function __( $t, $d = null ) {
	return $t;
}
function _x( $t, $c = '', $d = null ) {
	return $t;
}
function _n( $s, $p, $n, $d = null ) {
	return 1 === (int) $n ? $s : $p;
}
function esc_html__( $t, $d = null ) {
	return esc_html( $t );
}
function esc_attr__( $t, $d = null ) {
	return esc_attr( $t );
}
function esc_html_e( $t, $d = null ) {
	echo esc_html( $t );
}
function esc_attr_e( $t, $d = null ) {
	echo esc_attr( $t );
}
function _e( $t, $d = null ) {
	echo esc_html( $t );
}

// ---------------------------------------------------------------------
// Sanitizing
// ---------------------------------------------------------------------

function absint( $v ) {
	return abs( (int) $v );
}
function sanitize_text_field( $v ) {
	return trim( strip_tags( (string) $v ) );
}
function sanitize_key( $v ) {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $v ) );
}
// Wie in WordPress: entweder ein sauberes #rgb/#rrggbb oder null.
function sanitize_hex_color( $v ) {
	$v = (string) $v;
	return preg_match( '/^#([A-Fa-f0-9]{3}){1,2}$/', $v ) ? $v : null;
}
function sanitize_email( $v ) {
	return filter_var( (string) $v, FILTER_SANITIZE_EMAIL );
}
function wp_unslash( $v ) {
	return $v;
}

// ---------------------------------------------------------------------
// Optionen — in der Vorschau aus fixtures.json
// ---------------------------------------------------------------------

$GLOBALS['tha_preview_options'] = array();

function get_option( $key, $default = false ) {
	return isset( $GLOBALS['tha_preview_options'][ $key ] )
		? $GLOBALS['tha_preview_options'][ $key ]
		: $default;
}
function update_option( $key, $value ) {
	$GLOBALS['tha_preview_options'][ $key ] = $value;
	return true;
}
function add_option( $key, $value ) {
	return update_option( $key, $value );
}

function wp_parse_args( $args, $defaults = array() ) {
	if ( ! is_array( $args ) ) {
		$args = array();
	}
	return array_merge( $defaults, $args );
}

function wp_list_pluck( $list, $field ) {
	$out = array();
	foreach ( (array) $list as $row ) {
		if ( is_array( $row ) && isset( $row[ $field ] ) ) {
			$out[] = $row[ $field ];
		}
	}
	return $out;
}

// ---------------------------------------------------------------------
// Zeit
// ---------------------------------------------------------------------

function wp_timezone() {
	return new DateTimeZone( 'Europe/Zurich' );
}

// ---------------------------------------------------------------------
// Hooks — in der Vorschau No-Ops
// ---------------------------------------------------------------------

function apply_filters( $tag, $value ) {
	return $value;
}
function add_action() {}
function add_filter() {}
function do_action() {}
function has_action( $tag ) {
	return false;
}
function add_shortcode() {}

// ---------------------------------------------------------------------
// Assets — No-Ops, die Vorschau bindet das CSS direkt ein
// ---------------------------------------------------------------------

function wp_enqueue_style() {}
function wp_enqueue_script() {}
function wp_register_script() {}
function wp_add_inline_script() {}
function wp_localize_script() {}
function wp_style_is() {
	return false;
}
function wp_cache_flush() {}
function wp_cache_delete() {}
function get_bloginfo( $what = '' ) {
	return 'version' === $what ? '6.5' : 'Preview';
}
function plugin_dir_path( $f ) {
	return rtrim( dirname( $f ), '/' ) . '/';
}
function plugin_dir_url( $f ) {
	return '../plugin/theater-auw/';
}
function register_activation_hook() {}
function register_deactivation_hook() {}
function wp_next_scheduled() {
	return true;
}
function wp_schedule_event() {}
function wp_clear_scheduled_hook() {}
function load_plugin_textdomain() {}
function is_admin() {
	return false;
}
function current_user_can( $cap ) {
	return false;
}
function get_queried_object_id() {
	return 0;
}
function get_post_field() {
	return '';
}
function has_shortcode() {
	return false;
}
function has_block() {
	return false;
}
function register_block_type() {}
function shortcode_atts( $pairs, $atts ) {
	return wp_parse_args( (array) $atts, $pairs );
}

// ---------------------------------------------------------------------
// Medien — in der Vorschau gibt es keine Mediathek, also greifen überall
// die gestalteten Platzhalter. Genau der Zustand, in dem das Plugin beim
// Verein zuerst laufen wird.
// ---------------------------------------------------------------------

function wp_attachment_is_image( $id ) {
	return false;
}
function wp_get_attachment_image( $id, $size = 'thumbnail', $icon = false, $attr = array() ) {
	return '';
}
function wp_get_attachment_image_url( $id, $size = 'thumbnail' ) {
	return '';
}
function get_permalink( $id = 0 ) {
	return '#';
}

// ---------------------------------------------------------------------
// Navigation und Rückblick
//
// Die Vorschau bildet hier den ECHTEN Bestand von stvauw.ch nach — die
// Menüstruktur und die Portfolio-Jahrgänge, wie sie am 25.07.2026 auf der
// Produktivseite ausgelesen wurden. So zeigt die Vorschau, was das Plugin
// dort tatsächlich rendern wird, und nicht eine Fantasie.
// ---------------------------------------------------------------------

function wp_get_nav_menus() {
	return array( (object) array( 'term_id' => 1, 'name' => 'Theater', 'count' => 15 ) );
}

function wp_get_nav_menu_object( $id ) {
	return 1 === (int) $id ? (object) array( 'term_id' => 1, 'name' => 'Theater' ) : false;
}

function home_url( $path = '/' ) {
	return '#';
}

function wp_nav_menu( $args = array() ) {
	$menu = array(
		array( 'Home', '#', array( array( 'Spieldaten', '#daten' ) ), true ),
		array( 'TheaterAuw', '#', array(
			array( 'Theater 2026', '#stueck' ),
			array( 'Die Schauspieler', '#' ),
			array( 'Hinter den Kulissen', '#' ),
			array( 'Organisation Komitee', '#' ),
		), false ),
		array( 'Sponsoren', '#', array( array( 'Sponsoren 2026', '#' ) ), false ),
		array( 'Rückblick', '#', array(
			array( '2026 – 2008', '#rueckblick' ),
			array( '2007 – 1987', '#' ),
			array( '1975 – 1937', '#' ),
		), false ),
		array( 'Berichte', '#', array(), false ),
	);

	$html = '<ul class="' . esc_attr( $args['menu_class'] ) . '">';
	foreach ( $menu as $item ) {
		list( $label, $href, $kids, $current ) = $item;
		$cls = 'menu-item';
		if ( $kids ) {
			$cls .= ' menu-item-has-children';
		}
		if ( $current ) {
			$cls .= ' current-menu-item';
		}
		$html .= '<li class="' . $cls . '"><a href="' . esc_attr( $href ) . '">' . esc_html( $label ) . '</a>';
		if ( $kids ) {
			$html .= '<ul class="sub-menu">';
			foreach ( $kids as $kid ) {
				$html .= '<li class="menu-item"><a href="' . esc_attr( $kid[1] ) . '">' . esc_html( $kid[0] ) . '</a></li>';
			}
			$html .= '</ul>';
		}
		$html .= '</li>';
	}
	$html .= '</ul>';

	if ( empty( $args['echo'] ) ) {
		return $html;
	}
	echo $html;
}

function post_type_exists( $type ) {
	return 'project' === $type;
}

function get_post_types( $args = array(), $output = 'names' ) {
	return array();
}

/**
 * Die Jahrgänge, wie sie im Divi-Portfolio von stvauw.ch stehen.
 */
function get_posts( $args = array() ) {
	$titles = array(
		'2025 Karriere',
		'2024 Zoff im Paradies',
		'2023 D´ Putzfrau als Detektivin',
		'2022 En ganz normaali Huusfrau',
		'2019 Architekte und anderi Vögel',
		'2018 Alles Klara',
		'2017 De Früehräntner',
		'2016 De wahri Jakob',
	);
	$limit = isset( $args['numberposts'] ) ? (int) $args['numberposts'] : 8;

	// -1 heisst «alle» — array_slice würde mit negativer Länge sonst das
	// letzte Element abschneiden.
	$slice = ( $limit < 0 ) ? $titles : array_slice( $titles, 0, $limit );

	$out = array();
	foreach ( $slice as $i => $t ) {
		$out[] = (object) array( 'ID' => 1000 + $i, 'post_title' => $t );
	}
	return $out;
}

function get_post_thumbnail_id( $post = null ) {
	return 0; // In der Vorschau gibt es keine Mediathek → Platzhalter.
}

// ---------------------------------------------------------------------
// Bildergruppen
//
// Die Namen sind die echten von stvauw.ch — so zeigt die Vorschau, welche
// Beschriftung das Plugin aus der Mediathek ziehen wird.
// ---------------------------------------------------------------------

$GLOBALS['tha_preview_captions'] = array(
	// Die Schauspieler
	101 => 'Hanna Gehrig', 102 => 'Brigitte Favre', 103 => 'Ivan Huwiler',
	104 => 'Pascal von Flüe', 105 => 'Franz Troxler | Grilleur', 106 => 'Ludwig Stecher',
	107 => 'Gabi Leu', 108 => 'Noée Burkard', 109 => 'Claudia Villiger',
	110 => 'Melanie Zürcher', 111 => 'Markus Ineichen', 112 => 'Christine Jauner | Regie',
	// Hinter den Kulissen
	201 => 'Gabi + Gertrud Leu | Maske und Kostüme | Ein Dream-Team sind Gabi und Gertrud. Ihr grosses Wissen zu Garderobe, Maske und Frisuren ist Gold wert.',
	202 => 'Maya Baumann | Souffleuse | «Als Souffleuse kann ich während der Probezeit alles etwas beobachten.»',
	203 => 'Michèle Rippmann | Souffleuse | „Als Souffleuse muss ich jederzeit bereit sein einzuspringen."',
	204 => 'Christian Arnet | Bühnenchef | „S\'Büächli muass halt gläse werde."',
	205 => 'José Favre | Bühnen-Crew | Unterstützt den Bühnenchef tatkräftig und handwerklich.',
	206 => 'Sandro Heer + Martin Casagrande | Technik und Licht | Der Techniker muss flexibel und einfallsreich sein.',
	207 => 'Tombolateam',
	// OK
	301 => 'Theater OK-Team',
	// Sponsoren
	401 => 'Aargauische Kantonalbank', 402 => 'Airex AG', 403 => 'Bike & Dive GmbH',
	404 => 'Blumen + Pflanzencenter Waldispühl', 405 => 'Blumerei Auw', 406 => 'Budo-Team GmbH',
	407 => 'Bütler Elektro Telecom AG', 408 => 'Curaden AG', 409 => 'Engel Tore',
	410 => 'Flow Gym', 411 => 'Garage Kandy Weber', 412 => 'Glas-Inspirations.ch',
	413 => 'Grolimund AG', 414 => 'Hartmann Logistik', 415 => 'Metzgerei Villiger',
);

function wp_get_attachment_caption( $id ) {
	return isset( $GLOBALS['tha_preview_captions'][ $id ] ) ? $GLOBALS['tha_preview_captions'][ $id ] : '';
}
function get_post_meta( $id, $key = '', $single = false ) {
	return '';
}
function get_the_title( $id = 0 ) {
	return '';
}
function wpautop( $t ) {
	return $t;
}
function do_shortcode( $t ) {
	return $t;
}

function get_pages( $args = array() ) {
	// Der echte Seitenbestand von stvauw.ch/theater — damit der Test der
	// Shortcode-Vorschlaege an denselben Titeln scheitert oder besteht wie
	// die Admin-Person vor ihrem Bildschirm.
	global $tha_test_pages;
	if ( ! is_array( $tha_test_pages ) ) {
		return array();
	}
	$out = array();
	foreach ( $tha_test_pages as $i => $eintrag ) {
		// Entweder nur ein Titel oder «Titel» => «Seiteninhalt».
		$titel  = is_int( $i ) ? $eintrag : $i;
		$inhalt = is_int( $i ) ? '' : $eintrag;
		$out[] = (object) array(
			'ID'           => 100 + count( $out ),
			'post_title'   => $titel,
			'post_content' => $inhalt,
		);
	}
	return $out;
}
function get_edit_post_link( $id = 0 ) {
	return '#';
}

function wp_get_attachment_metadata( $id ) {
	// In der Vorschau: Porträts hochkant, Gruppenaufnahmen quer.
	$id = (int) $id;
	if ( 207 === $id || 301 === $id || $id >= 500 ) {
		return array( 'width' => 694, 'height' => 516 );
	}
	return array( 'width' => 768, 'height' => 1154 );
}

function wp_get_attachment_url( $id ) {
	return '#';
}

// ---------------------------------------------------------------------
// Transients und eine Attrappe von $wpdb
//
// Nur so viel, wie Tha_Chronik::images_by_year() braucht: eine einzige
// SELECT-Abfrage, deren WHERE hier in PHP nachgebildet wird. Die Zeilen
// stehen in $tha_test_attachments und entsprechen dem, was auf stvauw.ch
// in der Mediathek liegt.
// ---------------------------------------------------------------------

define( 'DAY_IN_SECONDS', 86400 );

$GLOBALS['tha_transients'] = array();

function get_transient( $key ) {
	return isset( $GLOBALS['tha_transients'][ $key ] ) ? $GLOBALS['tha_transients'][ $key ] : false;
}
function set_transient( $key, $value, $ttl = 0 ) {
	$GLOBALS['tha_transients'][ $key ] = $value;
	return true;
}
function delete_transient( $key ) {
	unset( $GLOBALS['tha_transients'][ $key ] );
	return true;
}

class Tha_Fake_Wpdb {
	public $posts    = 'wp_posts';
	public $postmeta = 'wp_postmeta';

	public function get_results( $sql ) {
		$out = array();
		foreach ( (array) $GLOBALS['tha_test_attachments'] as $row ) {
			$titel = isset( $row['title'] ) ? $row['title'] : '';
			$datei = isset( $row['file'] ) ? $row['file'] : '';
			$basis = basename( $datei );
			if ( preg_match( '/^(19|20)[0-9]{2}/', $titel ) || preg_match( '/^(19|20)[0-9]{2}/', $basis ) ) {
				$out[] = (object) array(
					'ID'         => (int) $row['id'],
					'post_title' => $titel,
					'datei'      => $datei,
				);
			}
		}
		return $out;
	}
}

$GLOBALS['tha_test_attachments'] = array();
$GLOBALS['wpdb'] = new Tha_Fake_Wpdb();

/**
 * Formular-Helfer aus WordPress.
 *
 * Die Uebersicht rendert inzwischen selbst Checkboxen; ohne diese drei
 * bricht das Einbinden der Vorlage ab. Die Ausgabe interessiert im Test
 * niemanden, nur dass sie ueberhaupt kommt.
 */
function checked( $checked, $current = true, $echo = true ) {
	$out = ( (string) $checked === (string) $current ) ? " checked='checked'" : '';
	if ( $echo ) {
		echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	return $out;
}

function selected( $selected, $current = true, $echo = true ) {
	$out = ( (string) $selected === (string) $current ) ? " selected='selected'" : '';
	if ( $echo ) {
		echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	return $out;
}

function disabled( $disabled, $current = true, $echo = true ) {
	$out = ( (string) $disabled === (string) $current ) ? " disabled='disabled'" : '';
	if ( $echo ) {
		echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	return $out;
}

function get_the_date( $format = '', $post = null ) {
	return '01.01.2026';
}

function wp_make_link_relative( $url ) {
	$pfad = wp_parse_url( $url, PHP_URL_PATH );
	return $pfad ? $pfad : $url;
}

function wp_parse_url( $url, $component = -1 ) {
	return parse_url( $url, $component );
}
