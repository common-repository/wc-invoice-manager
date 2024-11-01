<?php
namespace QuadLayers\WCIM\Models;

use Exception;
use WC_Order;
use Symlink\ORM\Manager;
use Symlink\ORM\Mapping;
use QuadLayers\WCIM\Entities\Invoice;

class Invoices_Model {

	protected static $instance;

	/**
	 * ORM Manager instance.
	 *
	 * @var Manager
	 */
	protected $manager;

	/**
	 * Invoice Repository instance.
	 *
	 * @var Repository
	 */
	protected $repository;

	private function __construct() {

		$manager       = Manager::getManager();
		$this->manager = $manager;

		$repository       = $this->manager->getRepository( Invoice::class );
		$this->repository = $repository;
	}

	public static function create_table() {
		$mapper = Mapping::getMapper();
		$mapper->updateSchema( Invoice::class );
	}

	public function get_all( int $page = 0, int $limit = 25 ) {
		$offset = ( $page - 1 ) * $limit;
		$query  = $this->repository->createQueryBuilder()
			->orderBy( 'ID', 'DESC' )
			->limit( $limit, $offset )
			->buildQuery();

		return $query->getResults( true );
	}

	public function get_by( $order_by = 'ID', $order = 'DESC', $limit = 15, $offset = 0, $where = array() ) {

		$query = $this->repository->createQueryBuilder();

		$query->orderBy( $order_by, $order );

		$query->limit( $limit, $offset );

		if ( ! empty( $where ) ) {
			foreach ( $where as $value ) {
				$query->where( $value[0], $value[1], $value[2] );
			}
		}
		$query->buildQuery();

		return $query->getResults( true );
	}

	public function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . $this->repository->getDBTable();
	}

	public function get_months() {
		global $wpdb;

		$table_name = $this->get_table_name();

		$query = "SELECT DISTINCT YEAR( datetime ) AS year, MONTH( datetime ) AS month FROM $table_name ORDER BY datetime DESC";

		$results = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return $results;
	}

	public function get_next_page( $current_page = 1 ) {
		$count        = $this->count();
		$is_next_page = $count > $current_page * 3;

		if ( ! $is_next_page ) {
			return false;
		}

		$next_page = $current_page + 1;
		return add_query_arg( 'paged', $next_page );
	}

	public function get_prev_page( $current_page = 1 ) {
		if ( 1 === $current_page ) {
			return false;
		}
		$prev_page = $current_page - 1;
		return add_query_arg( 'paged', $prev_page );
	}

	public function get_first_page() {
		return add_query_arg( 'paged', 1 );
	}

	public function get_last_page() {
		$count       = $this->count();
		$total_pages = $count / 3;
		return add_query_arg( 'paged', ceil( $total_pages ) );
	}

	public function save_all() {
		try {
			$this->manager->flush();
			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	public function delete_all() {
		try {
			$mapper = Mapping::getMapper();
			$mapper->dropTable( Invoice::class );
			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	public function count() {
		$query   = $this->repository->createQueryBuilder()
			->buildQuery();
		$results = $query->getResults();
		// Check if results is an array or implements Countable.
		if ( is_array( $results ) || $results instanceof \Countable ) {
			return count( $results );
		} else {
			// If results is not countable, it means we have a single result.
			// So, we return 1 if it's not false, otherwise 0.
			return false !== $results ? 1 : 0;
		}
	}

	public function save( Invoice $invoice ) {
		try {
			$this->manager->persist( $invoice );
			$order = $invoice->get_order();
			/**
			 * Prevents the invoice from being saved if the order does not exist.
			 */
			if ( $order ) {
				$order->add_order_note( sprintf( esc_html__( 'The invoice %s was successfully created for this order.', 'wc-invoice-manager' ), $invoice->get( 'code' ) ) );
				$order->update_meta_data( '_wcim_invoice_number', $invoice->get( 'number' ) );
				$order->save_meta_data();
				$this->manager->flush();
				return true;
			}
		} catch ( \Exception $e ) {
			return false;
		}
	}

	public function update( Invoice $invoice ) {
		try {
			$this->manager->flush();
			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Get invoice by invoice number.
	 *
	 * @param string $number
	 * @return Invoice|false
	 */
	public function get_by_invoice_number( $number ) {
		$result = $this->repository->findBy( array( 'number' => $number ) );
		return $result;
	}

	/**
	 * Get invoice by invoice number.
	 *
	 * @param string $order_id
	 * @return Invoice|false
	 */
	public function get_by_invoice_order_id( $order_id ) {
		$result = $this->repository->findBy( array( 'order_id' => $order_id ) );
		return $result;
	}

	public function find( $id ) {
		try {
			$invoice = $this->repository->find( $id );
			return $invoice;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	public function create( WC_Order $order ) {

		$invoice = $this->get_by_invoice_order_id( $order->get_id() );

		if ( $invoice instanceof Invoice ) {
			throw new Exception( esc_html__( 'An invoice for this order already exists.', 'wc-invoice-manager' ), 400 );
		}

		$settings_model = Admin_Menu_Settings_Model::instance();
		$settings       = $settings_model->get();

		$number = $this->get_next_invoice_number();

		$code = sprintf( '%s%s%s', $settings['number_prefix'], $number, $settings['number_suffix'] );

		$invoice = new Invoice();
		$invoice->set_order( $order );
		$invoice->set( 'number', $number );
		$invoice->set( 'code', $code );
		if ( ! $this->save( $invoice ) ) {
			return false;
		}
		return $invoice;
	}

	public function delete( $invoice ) {
		try {
			if ( ! $invoice instanceof Invoice ) {
				$invoice = $this->find( $invoice );
			}
			if ( ! $invoice instanceof Invoice ) {
				return false;
			}
			$order = $invoice->get_order();
			if ( $order instanceof WC_Order ) {
				$order->delete_meta_data( '_wcim_invoice_number' );
				$order->save_meta_data();
			}
			$this->manager->remove( $invoice );
			$this->manager->flush();
			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	public function get_next_invoice_number() {
		$query   = $this->repository->createQueryBuilder()
			->orderBy( 'number', 'DESC' )
			->limit( 1 )
			->buildQuery();
		$invoice = $query->getResults();
		/**
		 * If there is no invoice, then we start from number start.
		 */
		if ( ! $invoice instanceof Invoice ) {
			$settings_model = Admin_Menu_Settings_Model::instance();
			$settings       = $settings_model->get();
			return intval( $settings['number_start'] );
		}
		return $invoice->get( 'number' ) + 1;
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
