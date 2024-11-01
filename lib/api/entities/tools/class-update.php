<?php

namespace QuadLayers\WCIM\Api\Entities\Tools;

use Exception;
use WC_Order;
use QuadLayers\WCIM\Api\Entities\Tools\Base;
use QuadLayers\WCIM\Models\Invoices_Model;
use QuadLayers\WCIM\Services\Invoice_Permissions;

class Update extends Base {

	public function callback( \WP_REST_Request $request ) {

		try {

			$user_can_delete = ( new Invoice_Permissions() )->current_user_can_update();

			if ( ! $user_can_delete ) {
				throw new Exception( esc_html__( 'You don\'t have permission to update invoices.', 'wc-invoice-manager' ), 403 );
			}

			$invoices_model = Invoices_Model::instance();

			$invoices = $invoices_model->get_all();

			if ( ! $invoices || ! count( $invoices ) ) {
				throw new Exception( esc_html__( 'No invoices to update were found.', 'wc-invoice-manager' ), 400 );
			}

			$count = 0;
			foreach ( $invoices as $invoice ) {

				$order = $invoice->get_order();

				/**
				 * If the order is deleted, update invoice order status.
				 */
				if ( ! $order instanceof WC_Order ) {
					$invoice->set( 'order_status', 'deleted' );
					$invoices_model->update( $invoice );
					continue;
				}

				/**
				 * If the order status is the same, continue.
				 */
				if ( $order->get_date_modified()->format( 'Y-m-d H:i:s' ) === $invoice->get( 'order_datetime' ) ) {
					continue;
				}
				/**
				 * If the order is not deleted, update invoice with order data.
				 */
				$invoice->set_order( $order );
				$invoices_model->update( $invoice );
				++$count;
			}
			$response = sprintf( esc_html__( '%s invoices were updated.', 'wc-invoice-manager' ), esc_attr( $count ) );

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
		return \WP_REST_Server::EDITABLE;
	}

	public static function get_rest_args() {
		return array();
	}
}
