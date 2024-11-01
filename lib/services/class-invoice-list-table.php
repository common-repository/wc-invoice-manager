<?php

namespace QuadLayers\WCIM\Services;

use WC_Order;
use QuadLayers\WCIM\Helpers;
use QuadLayers\WCIM\Entities\Invoice;
use QuadLayers\WCIM\Models\Invoices_Model;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Invoice_List_Table extends \WP_List_Table {

	public $items;
	public $messages = array();

	public function __construct() {
		add_action( 'admin_footer', array( $this, 'print_styles' ) );
		parent::__construct(
			array(
				'singular' => 'invoice',
				'plural'   => 'invoices',
				'ajax'     => false,
			)
		);
	}

	public function print_styles() {
		?>
		<style type="text/css">
			table.wp-list-table {
				table-layout: auto;
			}
			table.wp-list-table tr th, 
			table.wp-list-table tr td {
				vertical-align: middle;
			}
		</style>
		<?php
	}

	public function get_page() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['_wp_http_referer'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' ) );
			exit;
		}

		$this->prepare_items();

		$template = new Template();

		$search_number_       = isset( $_REQUEST['number_'] ) ? wp_unslash( sanitize_key( $_REQUEST['number_'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search_billing_name_ = isset( $_REQUEST['billing_name_'] ) ? wp_unslash( sanitize_key( $_REQUEST['billing_name_'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		?>
			<div id="<?php echo esc_attr( sanitize_key( __CLASS__ ) ); ?>" class="wrap post-type-shop_order">
				<h2><?php echo esc_html__( 'Invoices', 'wc-invoice-manager' ); ?><a class="button" style="margin-left: 5px;" href="<?php echo esc_url( $template->get_list_url() ); ?>"><?php echo esc_html__( 'Templates', 'wc-invoice-manager' ); ?></a><a class="button button-primary" style="margin-left: 5px;" href="<?php echo esc_url( $template->get_edit_url() ); ?>"><?php echo strpos( $template->get_edit_url(), 'post-new.php?post_type=invoice-template' ) !== false ? esc_html__( 'Add New', 'wc-invoice-manager' ) : esc_html__( 'Edit', 'wc-invoice-manager' ); ?></a></h2>
				<form id="<?php echo esc_attr( sanitize_key( __CLASS__ ) ); ?>" method="get">
				<?php
				if ( $this->messages ) {
					echo '<div class="updated">';
					foreach ( $this->messages as $message ) {
						echo '<p>' . wp_kses_post( $message ) . '</p>';
					}
					echo '</div>';
				}
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$page = isset( $_REQUEST['page'] ) ? sanitize_key( wp_unslash( $_REQUEST['page'] ) ) : '';
				?>
					<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>"/>
					<?php
					$this->search_box(
						esc_html__( 'Search', 'wc-invoice-manager' ),
						'number',
						esc_html__( 'Search by invoice number', 'wc-invoice-manager' ),
						'number',
						$search_number_
					);
					$this->search_box(
						esc_html__( 'Search', 'wc-invoice-manager' ),
						'billing_name',
						esc_html__( 'Search by billing name', 'wc-invoice-manager' ),
						'billing_name',
						$search_billing_name_
					);
					$this->display()
					?>
				</form>
			</div>
		<?php
	}
	public function init_screen_option() {
		add_action( 'load-invoice-manager_page_wcim_list_wp', array( $this, 'add_screen_option' ) );
	}

	public function get_bulk_actions() {
		$actions = array(
			'download' => esc_html__( 'Download', 'wc-invoice-manager' ),
			'save'     => esc_html__( 'Update', 'wc-invoice-manager' ),
		);
		return $actions;
	}

	protected function date_dropdown() {

		global $wp_locale;

		$invoices_model = Invoices_Model::instance();
		$months         = $invoices_model->get_months();
		$month_count    = count( $months );

		if ( ! $month_count || ( 1 == $month_count && 0 == $months[0]->month ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$m = isset( $_GET['m'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['m'] ) ) : 0;
		?>
		<label for="filter-by-date" class="screen-reader-text"><?php echo esc_html__( 'Filter by date', 'wc-invoice-manager' ); ?></label>

		<select name="m" id="filter-by-date">
			<option <?php selected( $m, 0 ); ?> value="0"><?php echo esc_html__( 'All dates', 'wc-invoice-manager' ); ?></option>

			<?php
			foreach ( $months as $arc_row ) {

				if ( 0 === $arc_row->year ) {
					continue;
				}

				$month = zeroise( $arc_row->month, 2 );

				$year = $arc_row->year;

				printf( "<option %s value='%s'>%s</option>\n", selected( $m, $year . $month, false ), esc_attr( $arc_row->year . $month ), sprintf( '%1$s %2$d', esc_attr( $wp_locale->get_month( $month ) ), esc_attr( $year ) ) );
			}
			?>
		</select>
		<?php
	}

	protected function extra_tablenav( $which ) {
		?>
		<div class="alignleft actions">
			<?php
			if ( 'top' === $which && ! is_singular() ) {

				ob_start();

				$this->date_dropdown();

				$output = ob_get_clean();

				if ( ! empty( $output ) ) {

					echo wp_kses(
						$output,
						array(
							'label'  => array(
								'for'   => array(),
								'class' => array(),
							),
							'select' => array(
								'name'             => array(),
								'id'               => array(),
								'class'            => array(),
								'data-placeholder' => array(),
								'data-allow_clear' => array(),
							),
							'option' => array(
								'value'    => array(),
								'selected' => array(),
							),
						)
					);

					submit_button( esc_html__( 'Filter', 'wc-invoice-manager' ), '', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
				}
			}

			if ( $this->is_trash && current_user_can( get_post_type_object( $this->screen->post_type )->cap->edit_others_posts ) && $this->has_items() ) {
				submit_button( esc_html__( 'Empty Trash', 'wc-invoice-manager' ), 'apply', 'delete_all', false );
			}
			?>
		</div>
		<?php
	}

	public function search_box( $text, $input_id, $placeholder = null, $input_name = '', $query = '' ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$orderby = sanitize_key( wp_unslash( $_REQUEST['orderby'] ) );
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $orderby ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			$order = sanitize_key( wp_unslash( $_REQUEST['order'] ) );
			echo '<input type="hidden" name="order" value="' . esc_attr( $order ) . '" />';
		}
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
			$mime_type = sanitize_key( wp_unslash( $_REQUEST['post_mime_type'] ) );
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $mime_type ) . '" />';
		}
		if ( ! empty( $_REQUEST['detached'] ) ) {
			$detached = sanitize_key( wp_unslash( $_REQUEST['detached'] ) );
			echo '<input type="hidden" name="detached" value="' . esc_attr( $detached ) . '" />';
		}
			// phpcs:enable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
		?>
			<p class="search-box">
				<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
				<input type="search" placeholder="<?php echo esc_attr( $placeholder ); ?>" id="<?php echo esc_attr( $input_id ); ?>" name="<?php echo esc_attr( $input_name ); ?> " value="<?php echo esc_attr( $query ); ?>" />
				<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
			</p>
			<?php
	}

	public function column_default( $invoice, $column_name ) {

		$invoice_order        = $invoice->get_order();
		$invoice_order_id     = $invoice_order instanceof WC_Order ? $invoice_order->get_id() : null;
		$invoice_order_status = $invoice_order instanceof WC_Order ? $invoice_order->get_status() : 'deleted';
		switch ( $column_name ) {
			case 'ID':
				return $invoice->get( 'ID' );
			case 'order_id':
				if ( 'deleted' === $invoice_order_status ) {
					return '-';
				} else {
					return '<a href="' . esc_url( admin_url( "post.php?post={$invoice_order_id}&action=edit" ) ) . '">' . esc_html( $invoice_order_id ) . '</a>';
				}
			case 'number':
				return $invoice->get( 'number' );
			case 'code':
				return $invoice->get( 'code' );
			case 'billing_name':
				return $invoice->get( 'billing_name' );
			case 'datetime':
				?>
				<time datetime="<?php echo esc_attr( $invoice->get( 'datetime' ) ); ?>"><?php echo esc_html( $invoice->get( 'datetime' ) ); ?></time>
				<?php
				return;
			case 'order_status':
				$invoice_order_status_label = ucfirst( wc_get_order_status_name( $invoice_order_status ) );
				return $invoice_order_status_label;
			case 'total_currency':
				return get_woocommerce_currency_symbol() . $invoice->get( 'total' );
			case 'file':
				if ( 'deleted' !== $invoice_order_status ) :
					?>
					<a href="<?php echo esc_url( $invoice->get_pdf_link() ); ?>" target="_blank" title="<?php echo esc_html__( 'Download PDF', 'wc-invoice-manager' ); ?>">
						<?php echo esc_html( $invoice->get_filename() ); ?>
					</a>
					<?php
				endif;
				return;
			case 'actions':
				if ( 'deleted' === $invoice_order_status ) {
					return;
				}
				?>
					<a class="button button-primary" href="<?php echo esc_url( $invoice->get_action_link( 'save' ) ); ?>">
						<?php esc_html_e( 'Update', 'wc-invoice-manager' ); ?>
					</a>
					<?php
				return;
			default:
				return $invoice->get_property( $column_name );

		}
	}

	public function column_cb( $invoice ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', 'invoices_ids', $invoice->get( 'ID' ) );
	}

	public function get_columns() {
		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'ID'             => esc_html__( 'ID', 'wc-invoice-manager' ),
			'order_id'       => esc_html__( 'Order', 'wc-invoice-manager' ),
			'number'         => esc_html__( 'Number', 'wc-invoice-manager' ),
			'code'           => esc_html__( 'Code', 'wc-invoice-manager' ),
			'billing_name'   => esc_html__( 'Billing Name', 'wc-invoice-manager' ),
			'datetime'       => esc_html__( 'Date Time', 'wc-invoice-manager' ),
			'order_status'   => esc_html__( 'Order Status', 'wc-invoice-manager' ),
			'total_currency' => esc_html__( 'Total currency', 'wc-invoice-manager' ),
			'file'           => esc_html__( 'File', 'wc-invoice-manager' ),
			'actions'        => esc_html__( 'Actions', 'wc-invoice-manager' ),
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'ID'       => array( 'ID', false ),
			'order_id' => array( 'order_id', true ),
			'datetime' => array( 'datetime', true ),
			'number'   => array( 'number', false ),
			'code'     => array( 'code', false ),
		);
		return $sortable_columns;
	}

	public function prepare_items() {
		$current_page = $this->get_pagenum();
		$per_page     = $this->get_items_per_page( 'invoices_per_page' );
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$orderby      = ! empty( $_REQUEST['orderby'] ) ? sanitize_key( wp_unslash( $_REQUEST['orderby'] ) ) : 'ID';
		$orderby      = 'id' === $orderby ? 'ID' : $orderby;
		$order        = empty( $_REQUEST['order'] ) || 'desc' == $_REQUEST['order'] ? 'DESC' : 'ASC';
		$number       = ! empty( $_REQUEST['number_'] ) ? intval( sanitize_text_field( wp_unslash( $_REQUEST['number_'] ) ) ) : '';
		$m            = ! empty( $_REQUEST['m'] ) ? sanitize_key( wp_unslash( $_REQUEST['m'] ) ) : '';
		$billing_name = ! empty( $_REQUEST['billing_name_'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['billing_name_'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		$this->process_bulk_action();

		$invoice_date_start = null;
		$invoice_date_end   = null;

		if ( ! empty( $m ) ) {
			$m                  = strtotime( $m . '01' );
			$invoice_date_start = strtotime( gmdate( 'Y-m-d H:i:s', $m ) );
			$invoice_date_end   = strtotime( gmdate( 'Y-m-t H:i:s', $m ) );
		}

		$where = array();

		if ( ! empty( $billing_name ) ) {
			$where[] = array( 'billing_name', $billing_name, '=' );
		}

		if ( ! empty( $number ) ) {
			$where[] = array( 'number', $number, '=' );
		}

		if ( ! empty( $invoice_date_start ) && ! empty( $invoice_date_end ) ) {
			// separate in two wheres.
			$where[] = array( 'datetime', gmdate( 'Y-m-d', $invoice_date_start ), '>=' );
			$where[] = array( 'datetime', gmdate( 'Y-m-d', $invoice_date_end ), '<=' );
		}

		/**
		 * Total invoices limited by pagination
		 */
		$invoices_model = Invoices_Model::instance();

		$invoices = $invoices_model->get_by( $orderby, $order, $per_page, ( $current_page - 1 ) * $per_page, $where );

		/**
		 * Total invoices per query
		 */
		$total_invoices = $invoices_model->count();

		$this->items = $invoices;

		$this->set_pagination_args(
			array(
				'total_items' => $total_invoices,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_invoices / $per_page ),
			)
		);
	}

	public function process_bulk_action() {

		switch ( $this->current_action() ) {
			case 'save':
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( isset( $_REQUEST['invoices_ids'] ) ) {
					$invoice_ids = array_map( 'intval', $_REQUEST['invoices_ids'] );
					if ( $invoice_ids ) {
						$template       = new Template();
						$invoices_model = Invoices_Model::instance();
						foreach ( $invoice_ids as $invoice_id ) {
							$invoice = $invoices_model->find( $invoice_id );
							if ( ! $invoice instanceof Invoice ) {
								continue;
							}
							$template->get_pdf( $invoice );
						}
					}
				}
				break;
			case 'download':
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( isset( $_REQUEST['invoices_ids'] ) ) {
					$invoice_ids = array_map( 'intval', $_REQUEST['invoices_ids'] );

					if ( $invoice_ids ) {
						Helpers::get_download_zip_from_invoices( $invoice_ids );
					}
				}
				break;
			default:
				break;
		}
	}
}
