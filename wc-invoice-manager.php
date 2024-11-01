<?php
/**
 * Plugin Name:             WooCommerce Invoice Manager
 * Plugin URI:              https://wordpress.org/plugins/wc-invoice-manager/
 * Description:             Manage WooCommerce invoices with the first Gutenberg-based editor; it's user-friendly.
 * Version:                 1.0.7
 * Text Domain:             wc-invoice-manager
 * Author:                  QuadLayers
 * Author URI:              https://quadlayers.com
 * License:                 GPLv3
 * Domain Path:             /languages
 * Request at least:        4.7
 * Tested up to:            6.6
 * Requires PHP:            5.6
 * WC requires at least:    4.0
 * WC tested up to:         9.3
 *
 * @package                 WooCommerce Invoice Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
*   Definition globals variables
*/
define( 'QUADLAYERS_WCIM_PLUGIN_NAME', 'WooCommerce Invoice Manager' );
define( 'QUADLAYERS_WCIM_PLUGIN_VERSION', '1.0.7' );
define( 'QUADLAYERS_WCIM_PLUGIN_FILE', __FILE__ );
define( 'QUADLAYERS_WCIM_PLUGIN_DIR', __DIR__ . DIRECTORY_SEPARATOR );
define( 'QUADLAYERS_WCIM_DOMAIN', 'wcim' );
define( 'QUADLAYERS_WCIM_PREFIX', QUADLAYERS_WCIM_DOMAIN );
define( 'QUADLAYERS_WCIM_WORDPRESS_URL', 'https://wordpress.org/plugins/wc-invoice-manager/' );
define( 'QUADLAYERS_WCIM_REVIEW_URL', 'https://wordpress.org/support/plugin/wc-invoice-manager/reviews/?filter=5#new-post' );
define( 'QUADLAYERS_WCIM_SUPPORT_URL', 'https://wordpress.org/support/plugin/wc-invoice-manager/' );
define( 'QUADLAYERS_WCIM_GROUP_URL', 'https://www.facebook.com/groups/quadlayers' );
define( 'QUADLAYERS_WCIM_PREMIUM_SELL_URL', 'https://quadlayers.com/products/woocommerce-invoice-manager/?utm_source=wcim_admin' );
define( 'QUADLAYERS_WCIM_DOCUMENTATION_URL', 'https://quadlayers.com/documentation/woocommerce-invoice-manager/?utm_source=wcim_admin' );
define( 'QUADLAYERS_WCIM_DEVELOPER', false );
/**
 * Load composer autoload
 */
require_once __DIR__ . '/vendor/autoload.php';
/**
 * Load vendor_packages packages
 */
require_once __DIR__ . '/vendor_packages/wp-i18n-map.php';
require_once __DIR__ . '/vendor_packages/wp-plugin-table-links.php';
require_once __DIR__ . '/vendor_packages/wp-notice-plugin-required.php';
require_once __DIR__ . '/vendor_packages/wp-plugin-feedback.php';
/**
 * Load plugin classes
 */
require_once __DIR__ . '/lib/class-plugin.php';
/**
 * On plugin activation
 */
register_activation_hook(
	__FILE__,
	function () {
		do_action( 'quadlayers_wcim_activation' );
	}
);
/**
 * On plugin deactivation
 */
register_deactivation_hook(
	__FILE__,
	function () {
		do_action( 'quadlayers_wcim_deactivation' );
	}
);
/**
 * Declarate compatibility with WooCommerce Custom Order Tables
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);
