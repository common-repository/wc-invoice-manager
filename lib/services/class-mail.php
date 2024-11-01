<?php
namespace QuadLayers\WCIM\Services;

use WC_Order;
use QuadLayers\WCIM\Entities\Invoice;
use QuadLayers\WCIM\Helpers;
use QuadLayers\WCIM\Services\File;

class Mail {

	/**
	 * Send invoice email.
	 */
	public function send_invoice( Invoice $invoice ) {

		$invoice_order = $invoice->get_order();

		if ( ! $invoice_order instanceof WC_Order ) {
			return;
		}

		$invoice_order_id = $invoice_order->get_id();

		if ( ! $invoice_order_id ) {
			return;
		}
		$mailer = WC()->mailer();
		$mails  = $mailer->get_emails();

		foreach ( $mails as $mail ) {
			if ( 'customer_invoice' === $mail->id ) {
				add_filter( 'woocommerce_email_attachments', array( $this, 'wcim_attach_pdf' ), 10, 3 );
				$mail->trigger( $invoice_order_id, $invoice_order );
			}
		}
	}

	/**
	 * Attach PDF to WC email.
	 */
	public function wcim_attach_pdf( $attachments, $email_id, $order ) {

		if ( ! $order instanceof WC_Order ) {
			return $attachments;
		}
		$order_id = $order->get_id();
		$invoice  = Helpers::get_order_invoice( $order_id );

		if ( ! $invoice instanceof Invoice ) {
			return $attachments;
		}

		$file_path = File::get_uploads_path() . '/' . $invoice->get_filename();

		if ( ! file_exists( $file_path ) ) {
			return $attachments;
		}
		$attachments[] = $file_path;
		return $attachments;
	}
}
