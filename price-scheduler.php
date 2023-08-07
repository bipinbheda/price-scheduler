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

// register_activation_hook(PRICE_SC_FILE, 'price_scheduler_activation');

// price_scheduler_activation();
function price_scheduler_activation() {
    wp_clear_scheduled_hook('price_scheduler_daily_cron');

    if ( ! wp_next_scheduled ( 'price_scheduler_daily_cron' ) ) {

        // $gmt_offset = sprintf('%02d:%02d', (int) $gmt_offset, fmod($gmt_offset, 1) * 60);

        $ve = get_option( 'gmt_offset' ) > 0 ? '-' : '+';

        wp_schedule_event( strtotime( '00:00 tomorrow ' . $ve . absint( get_option( 'gmt_offset' ) ) . ' HOURS' ), 'daily', 'price_scheduler_daily_cron' );
    }
}

require_once( PRICE_SC_DIR . '/inc/class.admin-price-scheduler.php' );
require_once( PRICE_SC_DIR . '/inc/class.price-scheduler-cron.php' );

/**
 * Initialize the main class
 */
if ( class_exists( 'price_scheduler' ) ) {

    function price_scheduler() {
        return price_scheduler::instance();
    }

    price_scheduler();
}
