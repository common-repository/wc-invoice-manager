<?php
namespace QuadLayers\WCIM\Services;

class Invoice_Permissions {

	protected $accepted_caps = array(
		'administrator',
	);
	protected $current_user;
	protected $user_caps;

	public function __construct() {
		$this->current_user = wp_get_current_user();
		$this->user_caps    = isset( $this->current_user->caps ) ? array_keys( $this->current_user->caps ) : array();
	}

	public function current_user_can_create() {

		$user_can = $this->can_user( $this->accepted_caps );

		return apply_filters( 'quadlayers_wcim_current_user_can_create', $user_can );
	}

	public function current_user_can_read( $invoice = null ) {

		$user_can_read = $this->can_user( $this->accepted_caps );

		$is_user_invoice_owner = intval( $this->current_user->ID ) === intval( $invoice->get( 'order_customer_id' ) );

		if ( $user_can_read || $is_user_invoice_owner ) {
			$user_can = true;
		}

		return apply_filters( 'quadlayers_wcim_current_user_can_read', $user_can );
	}

	public function current_user_can_update() {

		$user_can = $this->can_user( $this->accepted_caps );

		return apply_filters( 'quadlayers_wcim_current_user_can_update', $user_can );
	}

	public function current_user_can_delete() {

		$user_can = $this->can_user( $this->accepted_caps );

		return apply_filters( 'quadlayers_wcim_current_user_can_delete', $user_can );
	}

	protected function can_user( $accepted_caps ) {

		$caps_array_intersect = array_intersect( $accepted_caps, $this->user_caps );

		if ( empty( $caps_array_intersect ) ) {
			return false;
		}

		return true;
	}

	public function get_current_user() {
		return $this->current_user;
	}
}
