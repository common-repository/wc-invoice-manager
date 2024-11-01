<?php
// phpcs:disable Generic.Commenting.DocComment.MissingShort
// phpcs:disable Generic.VariableAnalysis.UnusedVariable
// phpcs:disable Squiz.Commenting.VariableComment.MissingVar
namespace QuadLayers\WCIM\Entities;

use WC_Order;

use Symlink\ORM\Models\BaseModel as Model;
/**
 * @ORM_Type Entity
 * @ORM_Table "wc_wcim_invoices"
 * @ORM_AllowSchemaUpdate True
 */
class Invoice extends Model {

	/**
	 * WC_Order object.
	 *
	 * @var WC_Order $order
	 */
	protected $order;

	/**
	 * @ORM_Column_Type bigint
	 * @ORM_Column_Length 20
	 * @ORM_Column_Null NOT NULL
	 */
	protected $order_id;

	/**
	 * @ORM_Column_Type varchar
	 * @ORM_Column_Length 50
	 * @ORM_Column_Null NOT NULL
	 */
	protected $order_status;

	/**
	 * @ORM_Column_Type datetime
	 * @ORM_Column_Null NOT NULL
	 */
	protected $order_datetime;

	/**
	 * @ORM_Column_Type bigint
	 * @ORM_Column_Length 20
	 * @ORM_Column_Null NOT NULL
	 */
	protected $order_customer_id;

	/**
	 * @ORM_Column_Type bigint
	 * @ORM_Column_Length 255
	 * @ORM_Column_Null NOT NULL
	 */
	protected $number;

	/**
	 * @ORM_Column_Type varchar
	 * @ORM_Column_Length 255
	 * @ORM_Column_Null NOT NULL
	 */
	protected $code;

	/**
	 * @ORM_Column_Type datetime
	 * @ORM_Column_Null NOT NULL
	 */
	protected $datetime;

	/**
	 * @ORM_Column_Type varchar
	 * @ORM_Column_Length 255
	 * @ORM_Column_Null NOT NULL
	 */
	protected $billing_name;

	/**
	 * @ORM_Column_Type float
	 * @ORM_Column_Null NOT NULL
	 */
	protected $subtotal;

	/**
	 * @ORM_Column_Type float
	 * @ORM_Column_Null NOT NULL
	 */
	protected $taxes;

	/**
	 * @ORM_Column_Type float
	 * @ORM_Column_Null NOT NULL
	 */
	protected $discount;

	/**
	 * @ORM_Column_Type float
	 * @ORM_Column_Null NOT NULL
	 */
	protected $total;

	/**
	 *
	 * @return WC_Order
	 */
	public function get_order() {

		if ( ! $this->order && function_exists( 'wc_get_order' ) ) {
			$this->order = wc_get_order( $this->order_id );
			return $this->order;
		}

		return $this->order;
	}

	public function set_order( WC_Order $order ) {
		$this->set( 'billing_name', sprintf( '%s %s', $order->get_billing_first_name(), $order->get_billing_last_name() ) );
		$this->set( 'subtotal', $order->get_subtotal() );
		$this->set( 'discount', $order->get_discount_total() );
		$this->set( 'taxes', $order->get_tax_totals() );
		$this->set( 'total', $order->get_total() );
		$this->set( 'datetime', current_time( 'mysql' ) );
		$this->set( 'order_id', $order->get_id() );
		$this->set( 'order_status', $order->get_status() );
		$this->set( 'order_datetime', $order->get_date_modified()->format( 'Y-m-d H:i:s' ) );
		$this->set( 'order_customer_id', $order->get_customer_id() );
		return $order;
	}

	public function get_view_link() {
		return $this->get_action_link( 'view' );
	}

	public function get_pdf_link() {
		return $this->get_action_link( 'pdf' );
	}

	public function get_filename() {
		return strtolower( 'invoice-' . $this->code . '.pdf' );
	}

	public function get_action_link( $task ) {
		return admin_url( 'admin-post.php?action=wcim_invoice_' . $task . '&invoice_id=' . $this->ID );
	}
}
