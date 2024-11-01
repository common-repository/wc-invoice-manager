<?php

namespace QuadLayers\WCIM\Api\Entities\Admin_Menu\Emails;

use QuadLayers\WCIM\Api\Entities\Admin_Menu\Base;
use QuadLayers\WCIM\Models\Admin_Menu_Emails_Model;

class Post extends Base {
	protected static $route_path = 'emails';

	public function callback( \WP_REST_Request $request ) {
		try {

			$body = json_decode( $request->get_body(), true );

			$admin_menu_emails_model = Admin_Menu_Emails_Model::instance();

			$status = $admin_menu_emails_model->save( $body );

			return $this->handle_response( $status );
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
		return \WP_REST_Server::CREATABLE;
	}

	public static function get_rest_args() {
		return array();
	}
}
