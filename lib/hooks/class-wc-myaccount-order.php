<?php

namespace QuadLayers\WCIM\Hooks;

use QuadLayers\WCIM\Entities\Invoice;
use QuadLayers\WCIM\Helpers;
use QuadLayers\WCIM\Models\Invoices_Model;

class WC_MyAccount_Order {

	protected static $instance;

	protected function __construct() {
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'add_invoice_table_to_order_details' ) );
		// add_action( 'woocommerce_order_details_after_customer_details', array( $this, 'add_invoice_table_to_order_details' ) );.
	}

	/**
	 * Add invoice table to order details.
	 */
	public function add_invoice_table_to_order_details( $order ) {
		$order_id = $order->get_id();
		$invoice  = Helpers::get_order_invoice( $order_id );

		if ( ! $invoice instanceof Invoice ) {
			return;
		}

		?>
			<h2 class="woocommerce-column__title" style="margin-top: 0px;"><?php echo esc_html__( 'Invoice', 'wc-invoice-manager' ); ?></h2>
			<table>
				<tr>
					<th><?php echo esc_html__( 'Download', 'wc-invoice-manager' ); ?></th>
					<td>
						<a href="<?php echo esc_url( $invoice->get_pdf_link() ); ?>" target="_blank" title="<?php echo esc_html__( 'Download PDF', 'wc-invoice-manager' ); ?>">
							<?php echo esc_html( $invoice->get_filename() ); ?>
						</a>
					</td>
				</tr>
			</table>
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
