<?php

namespace QuadLayers\WCIM\Api\Entities\Admin_Menu;

use QuadLayers\WCIM\Api\Entities\Admin_Menu\Emails\Get as Emails_Get;
use QuadLayers\WCIM\Api\Entities\Admin_Menu\Emails\Post as Emails_Post;
use QuadLayers\WCIM\Api\Entities\Admin_Menu\Invoice\Get as Invoice_Get;
use QuadLayers\WCIM\Api\Entities\Admin_Menu\Invoice\Post as Invoice_Post;
use QuadLayers\WCIM\Api\Entities\Admin_Menu\Orders\Get as Orders_Get;
use QuadLayers\WCIM\Api\Entities\Admin_Menu\Orders\Post as Orders_Post;
use QuadLayers\WCIM\Api\Route as Route_Interface;

class Routes_Library {
	protected $routes                = array();
	protected static $rest_namespace = 'quadlayers/wcim/admin-menu';
	protected static $instance;

	private function __construct() {
		add_action( 'init', array( $this, '_rest_init' ) );
	}

	public static function get_namespace() {
		return self::$rest_namespace;
	}

	public function get_routes( $route_path = null ) {
		if ( ! $route_path ) {
			return $this->routes;
		}

		if ( isset( $this->routes[ $route_path ] ) ) {
			return $this->routes[ $route_path ];
		}
	}

	public function register( Route_Interface $instance ) {
		$this->routes[ $instance::get_name() ] = $instance;
	}

	public function _rest_init() {
		// Admin Menu.
		new Emails_Get();
		new Emails_Post();
		new Invoice_Get();
		new Invoice_Post();
		new Orders_Get();
		new Orders_Post();
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
