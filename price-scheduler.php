<?php
/**
 * Plugin Name:     Price Scheduler
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     price-scheduler
 * Domain Path:     /languages
 * Version:         1.0
 *
 * @package         Price_Scheduler
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !defined( 'PRICE_SC_VERSION' ) ) {
    define( 'PRICE_SC_VERSION', '1.0' ); // Version of plugin
}

if ( !defined( 'PRICE_SC_FILE' ) ) {
    define( 'PRICE_SC_FILE', __FILE__ ); // Plugin File
}

if ( !defined( 'PRICE_SC_DIR' ) ) {
    define( 'PRICE_SC_DIR', dirname( __FILE__ ) ); // Plugin dir
}

if ( !defined( 'PRICE_SC_URL' ) ) {
    define( 'PRICE_SC_URL', plugin_dir_url( __FILE__ ) ); // Plugin url
}

if ( !defined( 'PRICE_SC_PLUGIN_BASENAME' ) ) {
    define( 'PRICE_SC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); // Plugin base name
}

/**
 * Initialize the main class
 */
if ( ! function_exists( 'price_scheduler' ) ) {

    if ( is_admin() ) {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        require_once( PRICE_SC_DIR . '/inc/class.admin-price-scheduler.php' );
    }

    function price_scheduler() {
        return price_scheduler::instance();
    }

    price_scheduler();

}
