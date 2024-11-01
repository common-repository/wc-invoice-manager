<?php

namespace QuadLayers\WCIM\Controllers;

use WC_Order;
use QuadLayers\WCIM\Entities\Invoice;
use QuadLayers\WCIM\Models\Invoices_Model;
use QuadLayers\WCIM\Services\PDF;
use QuadLayers\WCIM\Services\Mail;
use QuadLayers\WCIM\Services\Template;
use QuadLayers\WCIM\Services\Invoice_Permissions;

class Invoice_Actions {

	protected static $instance;

	/**
	 * PDF Builder instance.
	 *
	 * @var PDF
	 */
	protected $pdf;

	/**
	 * Mail Builder instance.
	 *
	 * @var Mail
	 */
	protected $mail;

	/**
	 * Template Builder instance.
	 *
	 * @var Template
	 */
	protected $template;

	/**
	 * Invoice Permissions instance.
	 *
	 * @var Invoice_Permissions
	 */
	protected $permissions;

	private function __construct() {

		add_action( 'admin_post_wcim_invoice_view', array( $this, 'invoice_view' ) );
		add_action( 'admin_post_wcim_invoice_save', array( $this, 'invoice_save' ) );
		add_action( 'admin_post_wcim_invoice_pdf', array( $this, 'invoice_pdf' ) );
		add_action( 'admin_post_wcim_invoice_email', array( $this, 'invoice_email' ) );

		$this->pdf         = new PDF();
		$this->mail        = new Mail();
		$this->template    = new Template();
		$this->permissions = new Invoice_Permissions();
	}

	public function invoice_view() {
		if ( ! isset( $_GET['invoice_id'] ) ) {
			wp_die( esc_html__( 'Invoice ID not found', 'wc-invoice-manager' ) );
		}
		$invoice_id = intval( sanitize_text_field( wp_unslash( $_GET['invoice_id'] ) ) );

		$invoice = Invoices_Model::instance()->find( $invoice_id );

		if ( ! $invoice instanceof Invoice ) {
			wp_die( sprintf( esc_html__( 'Invoice with ID %s not found.', 'wc-invoice-manager' ), esc_attr( $invoice_id ) ) );
		}

		$user_can_read = $this->permissions->current_user_can_read( $invoice );

		if ( ! $user_can_read ) {
			wp_die( esc_html__( 'You don\'thave permission to view this invoice.', 'wc-invoice-manager' ) );
		}

		$html = $this->template->render( $invoice );
		echo $html; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	public function invoice_save() {

		if ( ! isset( $_GET['invoice_id'] ) ) {
			wp_die( esc_html__( 'Invoice ID not found', 'wc-invoice-manager' ) );
		}

		$invoice_id = intval( sanitize_text_field( wp_unslash( $_GET['invoice_id'] ) ) );

		$invoice = Invoices_Model::instance()->find( $invoice_id );

		if ( ! $invoice instanceof Invoice ) {
			wp_die( sprintf( esc_html__( 'Invoice with ID %s not found.', 'wc-invoice-manager' ), esc_attr( $invoice_id ) ) );
		}

		$user_can_update = $this->permissions->current_user_can_update( $invoice );

		if ( ! $user_can_update ) {
			wp_die( esc_html__( 'You don\'t have permission to update this invoice.', 'wc-invoice-manager' ) );
		}
		$this->template->get_pdf( $invoice, true );

		// Redirect to the current page.
		wp_safe_redirect( wp_get_referer() );

		exit;
	}

	public function invoice_pdf() {
		if ( ! isset( $_GET['invoice_id'] ) ) {
			wp_die( esc_html__( 'Invoice ID not found', 'wc-invoice-manager' ) );
		}
		$invoice_id = intval( sanitize_text_field( wp_unslash( $_GET['invoice_id'] ) ) );

		$invoice = Invoices_Model::instance()->find( $invoice_id );

		if ( ! $invoice instanceof Invoice ) {
			wp_die( sprintf( esc_html__( 'Invoice with ID %s not found.', 'wc-invoice-manager' ), esc_attr( $invoice_id ) ) );
		}

		$user_can_read = $this->permissions->current_user_can_read( $invoice );

		if ( ! $user_can_read ) {
			wp_die( esc_html__( 'You don\'thave permission to view this invoice.', 'wc-invoice-manager' ) );
		}

		list( $pdf, $filename ) = $this->template->get_pdf( $invoice );

		$download = false; // Set to false for inline display, true for downloading.
		if ( $download ) {
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename=' . basename( $filename ) );
		} else {
			header( 'Content-Type: application/pdf' );
			header( 'Content-Disposition: inline; filename=' . basename( $filename ) );
		}
		echo $pdf; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Send invoice email
	 */
	public function invoice_email() {

		if ( ! isset( $_GET['invoice_id'] ) ) {
			return;
		}

		$invoice_id = intval( sanitize_text_field( wp_unslash( $_GET['invoice_id'] ) ) );

		$invoice = Invoices_Model::instance()->find( $invoice_id );

		if ( ! $invoice instanceof Invoice ) {
			wp_die();
		}

		$invoice_order = $invoice->get_order();

		if ( ! $invoice_order instanceof WC_Order ) {
			wp_die( esc_html__( 'Order not valid.', 'wc-invoice-manager' ) );
		}

		$invoice_order_id = $invoice_order->get_id();

		if ( ! $invoice_order_id ) {
			wp_die( esc_html__( 'Order not found.', 'wc-invoice-manager' ) );
		}

		$this->mail->send_invoice( $invoice );

		// Redirect to the current page.
		wp_safe_redirect( wp_get_referer() );
		exit;
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
