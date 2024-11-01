<?php
namespace QuadLayers\WCIM;

use QuadLayers\WCIM\Models\Invoices_Model;

class Setup {

	protected static $instance;

	protected function __construct() {
		add_action( 'quadlayers_wcim_activation', array( __CLASS__, 'create_table' ) );
	}

	public static function create_table() {
		Invoices_Model::create_table();
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
