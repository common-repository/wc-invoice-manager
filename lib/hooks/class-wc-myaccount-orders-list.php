<?php

namespace QuadLayers\WCIM\Hooks;

use QuadLayers\WCIM\Entities\Invoice;
use QuadLayers\WCIM\Helpers;
use QuadLayers\WCIM\Models\Invoices_Model;

class WC_MyAccount_Orders_List {

	protected static $instance;

	protected function __construct() {
		add_filter( 'woocommerce_account_orders_columns', array( $this, 'add_invoice_number_header_to_order_details' ) );
		add_action( 'woocommerce_my_account_my_orders_column_invoice_number', array( $this, 'display_invoice_number_content_in_order_details' ) );
	}

	public function add_invoice_number_header_to_order_details( $columns ) {
		$columns['invoice_number'] = esc_html__( 'Invoice Number', 'wc-invoice-manager' );
		return $columns;
	}


	public function display_invoice_number_content_in_order_details( $order ) {
		$order_id = $order->get_id();
		$invoice  = Helpers::get_order_invoice( $order_id );

		if ( ! $invoice instanceof Invoice ) {
			return;
		}

		?>
			<a href="<?php echo esc_url( $invoice->get_pdf_link() ); ?>" target="_blank" title="<?php echo esc_html__( 'Download PDF', 'wc-invoice-manager' ); ?>">
				<?php echo esc_html( $invoice->get_filename() ); ?>
			</a>
		<?php
	}

	/**
	 * Get the single instance of this class.
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
