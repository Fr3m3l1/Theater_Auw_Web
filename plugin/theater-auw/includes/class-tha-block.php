<?php
/**
 * Block «Theater Auw» — serverseitig gerendert, damit im Editor genau das
 * steht, was später auch live steht.
 *
 * Bewusst ohne @wordpress/scripts: der Editor-Script ist reines JS gegen die
 * globalen wp.* Objekte. So bleibt das ZIP mit blossem bash baubar — was für
 * eine Vereinsseite, die in fünf Jahren jemand anders erbt, mehr wert ist als
 * eine moderne Toolchain.
 */

defined( 'ABSPATH' ) || exit;

class Tha_Block {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	public static function register() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		wp_register_script(
			'theater-auw-block',
			THA_URL . 'assets/js/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
			THA_VERSION,
			true
		);

		register_block_type(
			'theater-auw/seite',
			array(
				'api_version'     => 2,
				'title'           => __( 'Theater Auw', 'theater-auw' ),
				'description'     => __( 'Die Theater-Seite mit der aktuellen Saison-Phase.', 'theater-auw' ),
				'category'        => 'widgets',
				'icon'            => 'tickets-alt',
				'editor_script'   => 'theater-auw-block',
				'supports'        => array(
					'align' => array( 'wide', 'full' ),
					'html'  => false,
				),
				'attributes'      => array(
					'phase' => array(
						'type'    => 'string',
						'default' => '',
					),
					'align' => array(
						'type' => 'string',
					),
				),
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);
	}

	public static function render( $attributes ) {
		return Tha_Renderer::render(
			array(
				'phase' => isset( $attributes['phase'] ) ? $attributes['phase'] : '',
			)
		);
	}
}
