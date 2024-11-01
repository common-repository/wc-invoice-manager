<?php

namespace QuadLayers\WCIM\Api\Entities\Tools;

use Exception;
use QuadLayers\WCIM\Api\Entities\Tools\Base;
use QuadLayers\WCIM\Models\Invoices_Model;
use QuadLayers\WCIM\Services\Invoice_Permissions;

class Delete extends Base {

	public function callback( \WP_REST_Request $request ) {
		try {

			$invoice_permissions = new Invoice_Permissions();
			$user_can_delete     = $invoice_permissions->current_user_can_delete();

			if ( ! $user_can_delete ) {
				throw new Exception( esc_html__( 'You don\'t have permission to delete invoices.', 'wc-invoice-manager' ), 403 );
			}

			$invoices_model = Invoices_Model::instance();

			$invoices = $invoices_model->get_by( 'ID', 'DESC', 0, 0, array( array( 'order_status', 'deleted', '=' ) ) );

			if ( ! $invoices || ! count( $invoices ) ) {
				throw new Exception( esc_html__( 'No orphan invoices were found.', 'wc-invoice-manager' ), 400 );
			}

			$count = 0;
			foreach ( $invoices as $invoice ) {
				$invoices_model->delete( $invoice );
				++$count;
			}
			$response = sprintf( esc_html__( '%s orphan invoices were deleted.', 'wc-invoice-manager' ), esc_attr( $count ) );

			return $this->handle_response(
				array(
					'code'    => esc_html__( 'Success', 'wc-invoice-manager' ),
					'message' => $response,
				)
			);
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
		return \WP_REST_Server::DELETABLE;
	}

	public static function get_rest_args() {
		return array();
	}
}
