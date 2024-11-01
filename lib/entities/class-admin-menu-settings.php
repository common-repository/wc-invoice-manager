<?php
namespace QuadLayers\WCIM\Entities;

use QuadLayers\WCIM\Helpers;
use QuadLayers\WP_Orm\Entity\SingleEntity;

class Admin_Menu_Settings extends SingleEntity {
	public $test_mode             = 0;
	public $order_status          = array( 'completed', 'processing' );
	public $free_disabled         = false;
	public $delete_invoice        = 'no';
	public $reset_number_annually = true;
	public $number_start          = 1;
	public $number_prefix         = 'PREFIX-';
	public $number_suffix         = '-SUFIX';
	public $template_id           = null;

	public function __construct() {

		/**
		 * Set template id if not set and there is at least one template.
		 * This prevents the error when the user creates the template and changes are not applied if the template id is not set.
		 */
		if ( null === $this->template_id ) {

			$templates = Helpers::get_invoices_templates();

			if ( isset( $templates[0]->ID ) ) {
				$this->template_id = $templates[0]->ID;
			}

			wp_reset_postdata();
		}
	}
}
