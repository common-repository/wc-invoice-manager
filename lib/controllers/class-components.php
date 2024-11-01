<?php

namespace QuadLayers\WCIM\Controllers;

class Components {

	protected static $instance;

	private function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
	}

	public function register_scripts() {
		$components = include QUADLAYERS_WCIM_PLUGIN_DIR . 'build/components/js/index.asset.php';

		wp_register_style(
			'wcim-components',
			plugins_url( '/build/components/css/style.css', QUADLAYERS_WCIM_PLUGIN_FILE ),
			array(
				'wp-components',
				'media-views',
			),
			QUADLAYERS_WCIM_PLUGIN_VERSION
		);

		wp_register_script(
			'wcim-components',
			plugins_url( '/build/components/js/index.js', QUADLAYERS_WCIM_PLUGIN_FILE ),
			$components['dependencies'],
			$components['version'],
			true
		);
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
