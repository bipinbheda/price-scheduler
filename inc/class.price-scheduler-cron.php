<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'price_scheduler_cron' ) ) {
	/**
	 * The main price_scheduler_cron class
	 */
	class price_scheduler_cron {
		private static $_instance = null;

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		private function __construct() {
			// add_action('init', array( $this, 'do_this_hourly' ) );
			add_action('price_scheduler_daily_cron', array( $this, 'price_scheduler_daily_do_this_daily' ) );

			add_filter( 'woocommerce_product_data_store_cpt_get_products_query', array($this, 'handle_custom_query_var'), 10, 2 );
		}

		function handle_custom_query_var( $query, $query_vars ) {
			if ( ! empty( $query_vars['price_sc_date_on'] ) ) {
				$query['meta_query'][] = array(
					'key' => '_regular_price',
					'value' => $query_vars['price_sc_date_on'],
					// 'meta_compare' => 'BETWEEN',
				);
			}

			return $query;
		}

		public function update_product_price( $product_id ) {
			if ( ! empty( $product_id ) && is_int( $product_id ) ) {
				$new_price = get_post_meta( $product_id, 'price_sc_new_regular_price', true );
				if ( ! empty( $new_price ) ) {
					$product = wc_get_product( $product_id );
					$product->set_price( $new_price );
					$product->set_regular_price( $new_price );

					$product->save();

					return true;
				}
			}
			return false;
		}

		function price_scheduler_daily_do_this_daily() {
			if ( ! isset($_GET['test']) ) {
				// return false;
			}
			global $wpdb;

			$start_time = wp_date( wc_date_format() );
			$start_time = wc_string_to_timestamp( $start_time . '+1 day' );
			$end_time = wc_string_to_timestamp( '1 day'.'11:59:59' );

			$args = array(
				'post_type'        => array('product', 'product_variation'),
				'posts_per_page'   => -1,
				'meta_query' => array(
					array(
						'key' => 'price_sc_date_on',
						'value' => array( $start_time, $end_time ),
						'compare' => 'BETWEEN'
					)
				)
			);
			$query = new WP_Query( $args );
			$posts = array_chunk($query->posts, 50);

			if( ! empty( $posts ) ) {
				foreach ($posts as $chunk_key => $current_chunk) {
					foreach ($current_chunk as $key => $current_post) {
						$data = $this->update_product_price($current_post->ID);
					}
				}
			}
		}
	}

	price_scheduler_cron::instance();
}