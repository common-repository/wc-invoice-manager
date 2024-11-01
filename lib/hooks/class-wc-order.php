<?php
namespace QuadLayers\WCIM\Hooks;

use QuadLayers\WCIM\Helpers;
use QuadLayers\WCIM\Entities\Invoice;
use QuadLayers\WCIM\Models\Invoices_Model;
use QuadLayers\WCIM\Models\Admin_Menu_Settings_Model;
use QuadLayers\WCIM\Services\Template;

class WC_Order {

	protected static $instance;

	private function __construct() {
		$settings_model                = Admin_Menu_Settings_Model::instance();
		$settings_order_status_setting = $settings_model->get()['order_status'];

		foreach ( $settings_order_status_setting as $invoice_generate_on ) {
			add_action( "woocommerce_order_status_{$invoice_generate_on}", array( $this, 'create_invoice_on_status_change' ), 10, 2 );
		}
		add_action( 'woocommerce_order_status_changed', array( $this, 'update_invoice_on_order_status_change' ), 10, 3 );
	}

	public function update_invoice_on_order_status_change( $order_id, $order_status, $new_order_status ) {

		$invoice = Helpers::get_order_invoice( $order_id );

		if ( ! $invoice instanceof Invoice ) {
			return;
		}

		$invoice->set( 'order_status', $new_order_status );

		$invoices_model = Invoices_Model::instance();
		$invoices_model->update( $invoice );
	}

	public function create_invoice_on_status_change( $order_id, $order ) {

		// If the "Disable for free orders" feature is enabled.
		$settings_model         = Admin_Menu_Settings_Model::instance();
		$settings_free_disabled = $settings_model->get()['free_disabled'];

		$total = intval( floatval( str_replace( ',', '.', $order->get_total() ) ) );

		if ( $settings_free_disabled && 0 === $total ) {
			return; // Exit early if it's a free order.
		}

		$invoice = Helpers::get_order_invoice( $order_id );

		/**
		 * If the order already has an invoice, do nothing.
		 */
		if ( $invoice instanceof Invoice ) {
			return;
		}

		$invoices_model = Invoices_Model::instance();
		$invoice        = $invoices_model->create( $order );

		/**
		 * If the invoice was not created, do nothing.
		 */
		if ( ! $invoice instanceof Invoice ) {
			return;
		}

		$template = new Template();
		$template->get_pdf( $invoice, true );
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
