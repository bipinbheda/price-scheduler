<?php

// add_action('init','fun_callback_init');
// price_scheduler_activation();
function fun_callback_init() {
	$WC_DateTime = new WC_DateTime('tomorrow');
	echo '<pre>';print_r($WC_DateTime->set_utc_offset(get_option( 'gmt_offset' )));echo '</pre>';
	$cron_time_stamp = $WC_DateTime->getOffsetTimestamp();
	wp_clear_scheduled_hook('price_scheduler_daily_cron');
	wp_schedule_event( $cron_time_stamp, 'daily', 'price_scheduler_daily_cron' );
}	