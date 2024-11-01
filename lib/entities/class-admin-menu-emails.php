<?php
namespace QuadLayers\WCIM\Entities;

use QuadLayers\WP_Orm\Entity\SingleEntity;

class Admin_Menu_Emails extends SingleEntity {
	public $attach_on_email         = 'new_order';
	public $attach_invoice_pdf      = true;
	public $attach_invoice_pdf_link = true;
}
