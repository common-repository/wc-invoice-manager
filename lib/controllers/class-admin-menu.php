<?php

namespace QuadLayers\WCIM\Controllers;

use QuadLayers\WCIM\Api\Entities\Admin_Menu\Routes_Library as Admin_Menu_Routes_Library;
use QuadLayers\WCIM\Api\Entities\Tools\Routes_Library as Tools_Routes_Library;

class Admin_Menu {

	protected static $instance;
	protected static $menu_slug = 'wcim';

	private function __construct() {
		/**
		 * Admin Menu
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_footer', array( __CLASS__, 'add_menu_css' ) );
	}

	public function register_scripts() {

		$backend = include QUADLAYERS_WCIM_PLUGIN_DIR . 'build/backend/js/index.asset.php';

		wp_register_style(
			'wcim-backend',
			plugins_url( '/build/backend/css/style.css', QUADLAYERS_WCIM_PLUGIN_FILE ),
			array(
				'wcim-components',
				'media-views',
			),
			QUADLAYERS_WCIM_PLUGIN_VERSION
		);

		wp_register_script(
			'wcim-backend',
			plugins_url( '/build/backend/js/index.js', QUADLAYERS_WCIM_PLUGIN_FILE ),
			$backend['dependencies'],
			$backend['version'],
			true
		);

		wp_localize_script(
			'wcim-backend',
			'wcimAdminMenu',
			array(
				'QUADLAYERS_WCIM_API_ADMIN_MENU_REST_ROUTES' => $this->get_endpoints_admin_menu(),
				'QUADLAYERS_WCIM_API_TOOLS_REST_ROUTES' => $this->get_endpoints_tools(),
			)
		);
	}

	public function enqueue_scripts() {

		if ( ! isset( $_GET['page'] ) || self::get_menu_slug() !== $_GET['page'] ) {
			return;
		}

		wp_enqueue_script( 'wcim-backend' );
		wp_enqueue_style( 'wcim-backend' );
	}

	public function add_menu() {
		$menu_slug = self::get_menu_slug();
		add_menu_page(
			QUADLAYERS_WCIM_PLUGIN_NAME,
			QUADLAYERS_WCIM_PLUGIN_NAME,
			'edit_posts',
			$menu_slug,
			'__return_null',
			plugins_url( '/assets/backend/img/logo.svg', QUADLAYERS_WCIM_PLUGIN_FILE )
		);
		add_submenu_page(
			$menu_slug,
			esc_html__( 'Settings', 'wc-invoice-manager' ),
			esc_html__( 'Settings', 'wc-invoice-manager' ),
			'edit_posts',
			$menu_slug,
			'__return_null'
		);
	}

	public static function add_menu_css() {
		$menu_slug = self::get_menu_slug();
		?>
			<style>
				#toplevel_page_<?php echo esc_attr( $menu_slug ); ?> .wp-menu-image {
					display: flex;
					align-items: center;
					justify-content: center;
				}
				#toplevel_page_<?php echo esc_attr( $menu_slug ); ?> .wp-menu-image img {
					padding: 0;
					width: 26px;
				}

			</style>
		<?php
	}

	public static function get_menu_slug() {
		return self::$menu_slug;
	}

	private function get_endpoints_admin_menu() {
		$route_library   = Admin_Menu_Routes_Library::instance();
		$endpoints       = $route_library->get_routes();
		$endpoints_array = array();

		foreach ( $endpoints as $endpoint ) {

			$endpoint_key = str_replace( '/', '_', $endpoint::get_rest_route() );

			if ( ! isset( $endpoints_array[ $endpoint_key ] ) ) {

				$endpoints_array[ $endpoint_key ] = $endpoint::get_rest_path();

			}
		}

		return $endpoints_array;
	}

	private function get_endpoints_tools() {
		$route_library   = Tools_Routes_Library::instance();
		$endpoints       = $route_library->get_routes();
		$endpoints_array = array();

		foreach ( $endpoints as $endpoint ) {

			$endpoint_key = str_replace( '/', '_', $endpoint::get_rest_route() );

			if ( ! isset( $endpoints_array[ $endpoint_key ] ) ) {

				$endpoints_array[ $endpoint_key ] = $endpoint::get_rest_path();

			}
		}

		return $endpoints_array;
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
