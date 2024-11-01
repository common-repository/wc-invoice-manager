<?php

namespace QuadLayers\WCIM\Api\Entities\Tools;

use Exception;
use QuadLayers\WCIM\Api\Entities\Tools\Base;
use QuadLayers\WCIM\Models\Invoices_Model;
use QuadLayers\WCIM\Entities\Invoice;
use QuadLayers\WCIM\Services\Invoice_Permissions;

class Post extends Base {

	public function callback( \WP_REST_Request $request ) {
		try {

			$user_can_delete = ( new Invoice_Permissions() )->current_user_can_create();

			if ( ! $user_can_delete ) {
				throw new Exception( esc_html__( 'You don\'t have permission to create invoices.', 'wc-invoice-manager' ), 403 );
			}

			$orders = wc_get_orders(
				array(
					'meta_key'     => '_wcim_invoice_number',
					'meta_value'   => 0,
					'meta_compare' => 'NOT EXISTS',
					'orderby'      => 'date', // Order by date.
					'order'        => 'ASC',  // Ascending order for oldest to newest.
				)
			);

			if ( ! count( $orders ) ) {
				throw new Exception( esc_html__( 'No orders without invoices were found.', 'wc-invoice-manager' ), 400 );
			}

			$count = 0;
			foreach ( $orders as $order ) {
				$invoice = Invoices_Model::instance()->create( $order );
				if ( ! $invoice instanceof Invoice ) {
					continue;
				}
				++$count;
			}

			$response = sprintf( esc_html__( '%s invoices for past orders were created.', 'wc-invoice-manager' ), esc_attr( $count ) );

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
		return \WP_REST_Server::CREATABLE;
	}

	public static function get_rest_args() {
		return array();
	}
}
