<?php

namespace QuadLayers\WCIM\Hooks;

use QuadLayers\WCIM\Helpers;
use QuadLayers\WCIM\Entities\Invoice;
use QuadLayers\WCIM\Models\Admin_Menu_Orders_Model;
use QuadLayers\WCIM\Models\Admin_Menu_Settings_Model;
use QuadLayers\WCIM\Services\Template;
use QuadLayers\WCIM\Services\Mail;

class WC_Admin_Order {

	protected static $instance;

	protected function __construct() {

		// Add the metabox to the order page.
		add_action( 'admin_menu', array( $this, 'add_order_invoice_metabox' ) );

		// Add the custom order actions to the order page.
		add_filter( 'woocommerce_order_actions', array( $this, 'add_order_custom_actions' ) );
		add_action( 'woocommerce_order_action_wcim_generate_invoice_send_email', array( $this, 'wcim_generate_invoice_send_email' ), 10, 1 );
		add_action( 'woocommerce_order_action_wcim_send_invoice_email', array( $this, 'wcim_send_invoice_email' ), 10, 1 );
	}

	/**
	 * Add metabox to order page
	 */
	public function add_order_invoice_metabox() {
		$orders_model = Admin_Menu_Orders_Model::instance();

		if ( ! $orders_model->get()['display_order_metabox'] ) {
			return;
		}
		// Add metabox to order page.
		add_meta_box(
			'wcim-order-invoice-metabox',
			esc_html__( 'Order Invoice', 'wc-invoice-manager' ),
			array( $this, 'add_metabox_invoice' ),
			'shop_order',
			'side',
			'default'
		);

		// Add metabox to order page with HPOS.
		add_meta_box(
			'wcim-order-invoice-metabox',
			esc_html__( 'Order Invoice', 'wc-invoice-manager' ),
			array( $this, 'add_metabox_invoice' ),
			'woocommerce_page_wc-orders',
			'side',
			'default'
		);
	}

	/**
	 * Add metabox buttons
	 */
	public function add_metabox_invoice( $order ) {

		$invoice = Helpers::get_order_invoice( $order->ID );

		// If the invoice is not generated yet, add message advising when the invoice will be generated.
		if ( ! $invoice instanceof Invoice ) {

			$settings_order_status_setting = Admin_Menu_Settings_Model::instance()->get()['order_status'];

			$create_invoice_oreder_statuses = implode(
				', ',
				array_filter(
					array_map(
						function ( $status ) use ( $settings_order_status_setting ) {
							if ( ! isset( $status['label'] ) || ! in_array( $status['value'], $settings_order_status_setting, true ) ) {
								return;
							}
							return $status['label'];
						},
						Helpers::get_order_status_options()
					)
				)
			);

			?>
				<p>
					<?php printf( esc_html__( 'The order is yet to be completed. An invoice will be generated automatically once the order status changes to: %s.', 'wc-invoice-manager' ), esc_attr( $create_invoice_oreder_statuses ) ); ?>
				</p>
			<?php
			return;
		}

		?>
		<a class="button" href="<?php echo esc_url( $invoice->get_pdf_link() ); ?>" target="_blank" title="<?php echo esc_html__( 'Download PDF', 'wc-invoice-manager' ); ?>">
			<?php echo esc_html__( 'Download', 'wc-invoice-manager' ); ?>
		</a>
		<a class="button" href="<?php echo esc_url( $invoice->get_action_link( 'save' ) ); ?>">
			<?php echo esc_html__( 'Update', 'wc-invoice-manager' ); ?>
		</a>
		<a class="button" href="<?php echo esc_url( $invoice->get_action_link( 'email' ) ); ?>">
			<?php echo esc_html__( 'Email', 'wc-invoice-manager' ); ?>
		</a>
		<?php
	}

	/**
	 * Add order custom actions
	 */
	public function add_order_custom_actions( $actions ) {
		$actions['wcim_generate_invoice_send_email'] = esc_html__( 'Generate invoice and send email', 'wc-invoice-manager' );
		$actions['wcim_send_invoice_email']          = esc_html__( 'Send invoice email', 'wc-invoice-manager' );
		return $actions;
	}

	/**
	 * Action to generate invoice and send email
	 */
	public function wcim_generate_invoice_send_email() {

		if ( ! isset( $_POST['post_ID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		$order_id = intval( sanitize_text_field( wp_unslash( $_POST['post_ID'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification

		$invoice = Helpers::get_order_invoice( $order_id );

		if ( ! $invoice instanceof Invoice ) {
			return;
		}

		$template = new Template();

		$template->get_pdf( $invoice );

		$mail = new Mail();

		$mail->send_invoice( $invoice );

		$redirect_url = esc_url_raw( admin_url( "post.php?post={$order_id}&action=edit" ) );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Action to send invoice email
	 */
	public function wcim_send_invoice_email() {

		if ( ! isset( $_POST['post_ID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		$order_id = intval( sanitize_text_field( wp_unslash( $_POST['post_ID'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification

		$invoice = Helpers::get_order_invoice( $order_id );

		if ( ! $invoice instanceof Invoice ) {
			return;
		}

		$mail = new Mail();

		$mail->send_invoice( $invoice );

		$redirect_url = esc_url_raw( admin_url( "post.php?post={$order_id}&action=edit" ) );
		wp_safe_redirect( $redirect_url );
		exit;
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
