<?php
namespace QuadLayers\WCIM\Controllers;

use QuadLayers\WCIM\Services\File;
use QuadLayers\WCIM\Services\Invoice_List_Table;

class Admin_Menu_Invoices_List {

	protected static $instance;

	public $messages = array();

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 10 );
		add_filter( 'parent_file', array( $this, 'highlight_custom_post_type_menu' ) );
		add_filter( 'set-screen-option', array( $this, 'save_screen_options' ), 10, 3 );
	}

	public function admin_menu() {
		$menu_slug         = Admin_Menu::get_menu_slug();
		$invoice_page_slug = add_submenu_page(
			$menu_slug,
			esc_html__( 'Invoices', 'wc-invoice-manager' ),
			esc_html__( 'Invoices', 'wc-invoice-manager' ),
			'manage_options',
			$menu_slug . '_list_wp',
			function () {
				( new Invoice_List_Table() )->get_page();
			}
		);
		add_action( "load-$invoice_page_slug", array( $this, 'add_screen_options' ) );
	}

	public function highlight_custom_post_type_menu( $parent_file ) {
		global $current_screen;

		if ( 'invoice-template' === $current_screen->post_type ) {
			$parent_file = Admin_Menu::get_menu_slug();  // Assuming self::get_menu_slug() is accessible, or replace it with the specific slug.
		}

		return $parent_file;
	}

	public function save_screen_options( $status, $option, $value ) {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['qlwim_invoices'] ) ) {
			return $value;
		}

		return $status;
	}

	public function add_screen_options() {

		$option = 'per_page';
		$args   = array(
			'label'   => esc_html__( 'Number of items per page', 'wc-invoice-manager' ),
			'default' => 20,
			'option'  => 'invoices_per_page',
		);

		add_screen_option( $option, $args );
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
