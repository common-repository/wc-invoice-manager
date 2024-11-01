<?php

namespace QuadLayers\WCIM\Controllers;

use QuadLayers\WCIM\Models\Invoices_Model;
use QuadLayers\WCIM\Helpers as QUADLAYERS_WCIM_Helpers;
use QuadLayers\WCIM\Services\Template;

class Helpers {

	protected static $instance;

	private function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
	}

	public function register_scripts() {
		$helpers = include QUADLAYERS_WCIM_PLUGIN_DIR . 'build/helpers/js/index.asset.php';

		$template       = new Template();
		$invoices_model = Invoices_Model::instance();

		/**
		 * Register helpers assets
		 */
		wp_register_script(
			'wcim-helpers',
			plugins_url( '/build/helpers/js/index.js', QUADLAYERS_WCIM_PLUGIN_FILE ),
			$helpers['dependencies'],
			$helpers['version'],
			true
		);

		wp_localize_script(
			'wcim-helpers',
			'wcimHelpers',
			array(
				'QUADLAYERS_WCIM_INVOICE_TEMPLATES'       => QUADLAYERS_WCIM_Helpers::get_invoices_templates(),
				'QUADLAYERS_WCIM_ORDER_STATUS_OPTIONS'    => QUADLAYERS_WCIM_Helpers::get_order_status_options(),
				'QUADLAYERS_WCIM_INVOICES_COUNT'          => $invoices_model->count(),
				'QUADLAYERS_WCIM_ADMIN_URL_TEMPLATE_EDIT' => $template->get_edit_url(),
				'QUADLAYERS_WCIM_ADMIN_URL_TEMPLATE_LIST' => $template->get_list_url(),
				'QUADLAYERS_WCIM_PLUGIN_URL'              => plugins_url( '/', QUADLAYERS_WCIM_PLUGIN_FILE ),
				'QUADLAYERS_WCIM_PLUGIN_NAME'             => QUADLAYERS_WCIM_PLUGIN_NAME,
				'QUADLAYERS_WCIM_PLUGIN_VERSION'          => QUADLAYERS_WCIM_PLUGIN_VERSION,
				'QUADLAYERS_WCIM_SUPPORT_URL'             => QUADLAYERS_WCIM_SUPPORT_URL,
				'QUADLAYERS_WCIM_GROUP_URL'               => QUADLAYERS_WCIM_GROUP_URL,
				'QUADLAYERS_WCIM_PREMIUM_SELL_URL'        => QUADLAYERS_WCIM_PREMIUM_SELL_URL,
				'QUADLAYERS_WCIM_DOCUMENTATION_URL'       => QUADLAYERS_WCIM_DOCUMENTATION_URL,
			)
		);
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
