<?php
/**
 * Plugin Name: Lovat Api
 * Description: Lovat Api, return orders data in intermediate time "from" "to"
 * Version: 1.0.2
 * Author: Denys Maksiura
 * Requires at least: 5.2
 * Requires PHP: 7.0
 * WC requires at least: 4.4.4
 * @package Lovat
 */
// Define constants.
define('LOVAT_API_PLUGIN_VERSION', '1.0.2');
define('LOVAT_CACHE_OPTION_VALUE', 'LOVAT_CACHE_VALUE');
define('ISSET_TOKEN_BY_USER', 'ISSET_TOKEN_BY_USER_USER_ID');
define('LOVAT_GENERATED_KEYS', 'LOVAT_GENERATED_KEYS');
define('LOVAT_API_PLUGIN_DIR', untrailingslashit(plugin_dir_path(__FILE__)));
define('LOVAT_API_URL', plugin_dir_url(__FILE__));

// Include the main class.
require plugin_dir_path(__FILE__) . 'includes/class-lovat.php';

//// Load core packages and the autoloader.
include_once(LOVAT_API_PLUGIN_DIR . '/autoload.php');

//activate plugin, create
function activate_lovat_api()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-lovat-activator.php';
	Lovat_Activator::activate();
}

//deactivate plugin
function deactivate_lovat_api()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-lovat-deactivator.php';
	Lovat_Deactivator::deactivate();
}

add_action('admin_notices', 'isset_woocommerce_plugin');

function isset_woocommerce_plugin()
{
	if (!is_plugin_active('woocommerce/woocommerce.php')) {
		print '<div class="notice notice-error">
             <p><b>Lovat api</b> warning: "For the application to work, please install or activate the application WooCommerce".</p>
         </div>';
	}
}

register_activation_hook(__FILE__, 'activate_lovat_api');
register_deactivation_hook(__FILE__, 'deactivate_lovat_api');

// Main instance of plugin.
function Lovat()
{
	return Lovat::instance();
}

$GLOBALS['lovat_api'] = Lovat();
