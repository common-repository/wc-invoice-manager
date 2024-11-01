<?php

namespace QuadLayers\WCIM\Api\Entities\Tools;

use QuadLayers\WCIM\Api\Entities\Tools\Post as Tools_Post;
use QuadLayers\WCIM\Api\Entities\Tools\Update as Tools_Update;
use QuadLayers\WCIM\Api\Entities\Tools\Delete as Tools_Delete;
use QuadLayers\WCIM\Api\Route as Route_Interface;

class Routes_Library {
	protected $routes                = array();
	protected static $rest_namespace = 'quadlayers/wcim';
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
		new Tools_Post();
		new Tools_Update();
		new Tools_Delete();
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
