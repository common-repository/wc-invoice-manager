<?php
namespace QuadLayers\WCIM\Entities;

use QuadLayers\WP_Orm\Entity\SingleEntity;

class Admin_Menu_Orders extends SingleEntity {
	public $display_order_metabox     = true;
	public $display_order_list_column = true;
}
