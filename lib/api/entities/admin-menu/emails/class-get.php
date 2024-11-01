<?php

namespace QuadLayers\WCIM\Api\Entities\Admin_Menu\Emails;

use QuadLayers\WCIM\Api\Entities\Admin_Menu\Base;
use QuadLayers\WCIM\Models\Admin_Menu_Emails_Model;

class Get extends Base {
	protected static $route_path = 'emails';

	public function callback( \WP_REST_Request $request ) {
		try {
			$admin_menu_emails_model = Admin_Menu_Emails_Model::instance();

			$admin_menu_email = $admin_menu_emails_model->get();

			return $this->handle_response( $admin_menu_email );
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
