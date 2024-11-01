<?php

namespace QuadLayers\WCIM\Hooks;

use QuadLayers\WCIM\Entities\Invoice;
use QuadLayers\WCIM\Helpers;
use QuadLayers\WCIM\Models\Admin_Menu_Orders_Model;
use QuadLayers\WCIM\Models\Invoices_Model;
use QuadLayers\WCIM\Models\Admin_Menu_Settings_Model;
use QuadLayers\WCIM\Services\Template;
use QuadLayers\WCIM\Services\Mail;
use QuadLayers\WCIM\Services\Invoice_Permissions;

class WC_Admin_Orders_List {

	protected static $instance;

	protected function __construct() {

		// Add invoice number column to orders list.
		// Legacy orders.
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_invoice_number_column' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'display_invoice_number_column_data' ), 10, 2 );

		// HPOS orders.
		add_filter( 'woocommerce_shop_order_list_table_columns', array( $this, 'add_invoice_number_column' ), 15 );
		add_action( 'woocommerce_shop_order_list_table_custom_column', array( $this, 'display_invoice_number_column_data' ), 5, 2 );

		// Add bulk actions to orders list.
		// Legacy orders.
		add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_custom_bulk_action' ) );
		add_action( 'handle_bulk_actions-edit-shop_order', array( $this, 'handle_custom_bulk_action' ), 10, 3 );
		// HPOS orders.
		add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( $this, 'add_custom_bulk_action' ) );
		add_action( 'handle_bulk_actions-woocommerce_page_wc-orders', array( $this, 'handle_custom_bulk_action' ), 10, 3 );

		// Delete invoice when delete permanently order.
		$settings_model                  = Admin_Menu_Settings_Model::instance();
		$settings_delete_invoice_setting = $settings_model->get()['delete_invoice'];

		if ( 'trash' === $settings_delete_invoice_setting ) {
			add_action( 'wp_trash_post', array( $this, 'wcim_remove_invoice_from_delete_order' ) );
		} elseif ( 'permanently' === $settings_delete_invoice_setting ) {
			add_action( 'before_delete_post', array( $this, 'wcim_remove_invoice_from_delete_order' ) );
		} elseif ( 'no' === $settings_delete_invoice_setting ) {
			add_action( 'before_delete_post', array( $this, 'wcim_update_invoice_state_before_delete_order' ) );
		}
	}

	/**
	 * Add invoice number column to orders list.
	 */
	public function add_invoice_number_column( $columns ) {

		$orders_model = Admin_Menu_Orders_Model::instance();

		if ( ! $orders_model->get()['display_order_list_column'] ) {
			return $columns;
		}

		$columns['invoice'] = esc_html__( 'Invoice', 'wc-invoice-manager' );
		return $columns;
	}

	/**
	 * Display invoice number column data.
	 */
	public function display_invoice_number_column_data( $column, $order ) {

		if ( 'invoice' !== $column ) {
			return;
		}

		if ( ! is_object( $order ) ) {
			$order = wc_get_order( $order );
		}

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
	 * Add custom bulk actions to orders list.
	 */
	public function add_custom_bulk_action( $actions ) {
		$actions['wcim_generate_invoices'] = esc_html__( 'Generate invoices', 'wc-invoice-manager' );
		$actions['wcim_send_email']        = esc_html__( 'Email invoices', 'wc-invoice-manager' );
		return $actions;
	}

	/**
	 * Handle custom bulk actions.
	 */
	public function handle_custom_bulk_action( $redirect_to, $action, $order_ids ) {
		if ( 'wcim_generate_invoices' === $action ) {

			foreach ( $order_ids as $order_id ) {

				$invoice = Helpers::get_order_invoice( $order_id );

				if ( ! $invoice instanceof Invoice ) {
					continue;
				}

				$template = new Template();

				$template->get_pdf( $invoice, true );
			}
			$redirect_to = add_query_arg( 'bulk_action_result', 'custom_action_success', $redirect_to );
		}
		if ( 'wcim_send_email' === $action ) {

			$mail = new Mail();
			foreach ( $order_ids as $order_id ) {
				$invoice = Helpers::get_order_invoice( $order_id );
				if ( ! $invoice instanceof Invoice ) {
					continue;
				}
				$mail->send_invoice( $invoice );
			}
			$redirect_to = add_query_arg( 'bulk_action_result', 'custom_action_success', $redirect_to );
		}

		return $redirect_to;
	}

	/**
	 * Delete invoice when delete permanently order.
	 */
	public function wcim_remove_invoice_from_delete_order( $order_id ) {
		// We check if the global post type isn't ours.
		if ( 'shop_order' !== get_post_type( $order_id ) ) {
			return;
		}

		$invoice_permissions = new Invoice_Permissions();
		$user_can_delete     = $invoice_permissions->current_user_can_delete();
		if ( ! $user_can_delete ) {
			return;
		}

		$invoice = Helpers::get_order_invoice( $order_id );

		if ( ! $invoice instanceof Invoice ) {
			return;
		}

		if ( $invoice ) {
			$invoices_model = Invoices_Model::instance();
			$invoices_model->delete( $invoice->get( 'ID' ) );
		}
	}

	/**
	 * Update invoice state when delete order.
	 */
	public function wcim_update_invoice_state_before_delete_order( $order_id ) {
		// Get the post type of the deleted item.
		$post_type = get_post_type( $order_id );

		// Check if the deleted item is an order.
		if ( 'shop_order' !== $post_type ) {
			return;
		}

		$invoice = Helpers::get_order_invoice( $order_id );

		if ( ! $invoice instanceof Invoice ) {
			return;
		}

		$invoice->set( 'order_status', 'deleted' );

		$invoices_model = Invoices_Model::instance();
		$invoices_model->update( $invoice );
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
