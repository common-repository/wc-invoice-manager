<?php

namespace QuadLayers\WCIM\Api\Entities\Admin_Menu\Invoice;

use QuadLayers\WCIM\Api\Entities\Admin_Menu\Base;
use QuadLayers\WCIM\Models\Admin_Menu_Settings_Model;

class Get extends Base {
	protected static $route_path = 'invoice';

	public function callback( \WP_REST_Request $request ) {
		try {
			$admin_menu_settings_model = Admin_Menu_Settings_Model::instance();

			$admin_menu_invoice = $admin_menu_settings_model->get();

			return $this->handle_response( $admin_menu_invoice );
		} catch ( \Throwable $error ) {
			return $this->handle_response(
				array(
					'code'    => $error->getCode(),
					'message' => $error->getMessage(),
				)
			);
		}
	}

	public static function get_rest_method() {
		return \WP_REST_Server::READABLE;
	}

	public static function get_rest_args() {
		return array();
	}
}
