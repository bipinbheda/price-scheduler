<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'price_scheduler' ) ) {

	/**
	 * The main price_scheduler class
	 */
	class price_scheduler {
		private static $_instance = null;

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		public function price_scheduler_meta_keys( $key ) {
			$meta_keys = array(
				'regular_price'    => '_price_sc_new_regular_price',
				'schedule_date_on' => 'price_scheduler_date_on',
			);
			return isset( $meta_keys[ $key ] ) ? $meta_keys[ $key ] : '';
		}

		function __construct() {
			add_action( 'plugins_loaded', array( $this, 'price_scheduler_plugins_loaded' ), 1 );

			add_action( 'woocommerce_product_options_pricing', array( $this, 'price_scheduler_add_new_price_meta_field' ), 1 );
			add_action( 'woocommerce_process_product_meta', array( $this, 'price_scheduler_proces_new_price_meta_field' ), 1 );
		}

		public function price_scheduler_plugins_loaded() {
			if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				add_action( 'admin_notices', array( $this, 'action__cfgeo_admin_notices_deactive' ) );
				deactivate_plugins( PRICE_SC_PLUGIN_BASENAME );
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		}

		/**
		 * [action__cfgeo_admin_notices_deactive Message if CF7 plugin is not activated/installed]
		 *
		 * @return [type] [Error message]
		 */
		function action__cfgeo_admin_notices_deactive() {
			echo '<div class="error">' .
					sprintf(
						__( '<p><strong><a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a></strong> is required to use <strong>%s</strong>.</p>', 'price-scheduler' ),
						'Price Scheduler Plugin'
					) .
				'</div>';
		}

		public function price_scheduler_add_new_price_meta_field() {
			woocommerce_wp_text_input(
				array(
					'id'        => $this->price_scheduler_meta_keys( 'regular_price' ),
					'value'     => $this->price_scheduler_get_new_price(),
					'label'     => __( 'New Regular price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
					'data_type' => 'price',
				)
			);

			$schedule_date_on_key = $this->price_scheduler_meta_keys( 'schedule_date_on' );

			echo '<p class="form-field">
				<label for="' . $schedule_date_on_key . '">' . esc_html__( 'Sale price dates', 'woocommerce' ) . '</label>
				<input type="text" class="short" name="' . $schedule_date_on_key . '" id="' . $schedule_date_on_key . '" value="' . esc_attr( $this->price_schedule_get_date_on() ) . '" placeholder="' . esc_html( _x( 'From&hellip;', 'placeholder', 'woocommerce' ) ) . ' YYYY-MM-DD" maxlength="10" pattern="' . esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ) . '" />
				' . wc_help_tip( __( 'The sale will start at 00:00:00 of "From" date and end at 23:59:59 of "To" date.', 'woocommerce' ) ) . '
			</p>';
		}

		public function price_scheduler_get_new_price() {
			global $post, $thepostid, $product_object;
			return get_post_meta( $thepostid, $this->price_scheduler_meta_keys( 'regular_price' ), true );
		}

		public function price_schedule_get_date_on() {
			global $thepostid;

			$schedule_date_on           = get_post_meta( $thepostid, $this->price_scheduler_meta_keys( 'schedule_date_on' ), true );
			$schedule_date_on_timestamp = $schedule_date_on->getOffsetTimestamp();
			return $schedule_date_on_timestamp ? date_i18n( 'Y-m-d', $schedule_date_on_timestamp ) : '';
		}

		public function price_scheduler_set_new_price( $id, $value ) {
			return update_post_meta( $id, $this->price_scheduler_meta_keys( 'regular_price' ), $value );
		}

		public function price_schedule_set_date_on( $id, $value ) {

			$datetime = wc_string_to_datetime( $value );

			return update_post_meta( $id, $this->price_scheduler_meta_keys( 'schedule_date_on' ), $datetime );
		}

		public function price_scheduler_proces_new_price_meta_field( $id ) {
			$product              = wc_get_product( $id );
			$request_data         = $_REQUEST;
			$regular_price_key    = $this->price_scheduler_meta_keys( 'regular_price' );
			$schedule_date_on_key = $this->price_scheduler_meta_keys( 'schedule_date_on' );

			if ( $product->is_type( 'simple' ) || $product->is_type( 'external' ) ) {

				if ( isset( $request_data[ $regular_price_key ] ) ) {
					// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
					$new_regular_price = ( '' === $request_data[ $regular_price_key ] ) ? '' : wc_format_decimal( $request_data[ $regular_price_key ] );
					$this->price_scheduler_set_new_price( $id, $new_regular_price );
				} else {
					$new_regular_price = null;
				}

				// Handle dates.
				$schedule_date_on = '';
				// Force date from to beginning of day.
				if ( isset( $request_data[ $schedule_date_on_key ] ) ) {
					$schedule_date_on = wc_clean( wp_unslash( $request_data[ $schedule_date_on_key ] ) );

					if ( ! empty( $schedule_date_on ) ) {
						$schedule_date_on = date( 'Y-m-d 00:00:00', strtotime( $schedule_date_on ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					}
				}

				if ( $schedule_date_on ) {
					$this->price_schedule_set_date_on( $id, $schedule_date_on );
				}
			}
		}
	}
}
