<?php
namespace QuadLayers\WCIM;

use QuadLayers\WCIM\Api\Entities\Admin_Menu\Routes_Library as Routes_Library_Admin_Menu;
use QuadLayers\WCIM\Api\Entities\Tools\Routes_Library as Routes_Library_Tools;

class Plugin {

	protected static $instance;

	protected function __construct() {

		/**
		 * Load plugin textdomain.
		 */
		load_plugin_textdomain( 'wc-invoice-manager', false, QUADLAYERS_WCIM_PLUGIN_DIR . '/languages/' );
		add_action( 'admin_footer', array( __CLASS__, 'add_premium_css' ) );
		Setup::instance();

		add_action(
			'woocommerce_init',
			function () {
				/**
				 * Load plugin classes.
				 */
				Routes_Library_Admin_Menu::instance();
				Routes_Library_Tools::instance();
				Hooks\WC_Order::instance();
				Hooks\WC_Admin_Order::instance();
				Hooks\WC_Admin_Orders_List::instance();
				Hooks\WC_MyAccount_Order::instance();
				Hooks\WC_MyAccount_Orders_List::instance();
				Controllers\Helpers::instance();
				Controllers\Components::instance();
				Controllers\Admin_Menu::instance();
				Controllers\Admin_Menu_Invoices_List::instance();
				Controllers\Invoice_Actions::instance();
				Controllers\Invoice_Template::instance();
				Controllers\Invoice_Template_Blocks::instance();
			}
		);
	}

	public static function add_premium_css() {
		?>
			<style>
				.wcim__premium-field {
					opacity: 0.5;
					pointer-events: none;
				}
				.wcim__premium-field input,
				.wcim__premium-field textarea,
				.wcim__premium-field select {
					background-color: #eee;
				}
				.wcim__premium-badge::before {
					content: "Pro";
					display: inline-block;
					font-size: 10px;
					color: #ffffff;
					background-color: #f57c00;
					border-radius: 3px;
					width: 30px;
					height: 15px;
					line-height: 15px;
					text-align: center;
					margin-right: 5px;
					vertical-align: middle;
					font-weight: 600;
					text-transform: uppercase;
				}
				.wcim__premium-hide {
					display: none;
				}
				.wcim__premium-field .description {
					display: inline-block !important;
					vertical-align: middle;
				}
			</style>
		<?php
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

Plugin::instance();
