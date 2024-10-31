<?php
/**
 * WooCommerce RefRef Integration.
 *
 * @package  WC_RefRef_Integration
 * @category Integration
 * @author   nelsontky
 */

if ( ! class_exists( 'WC_RefRef_Integration' ) ) :

class WC_RefRef_Integration extends WC_Integration {
	/**
	 * Init and hook in the integration.
	 */
	public function __construct() {
		global $woocommerce;

		$this->id                 = 'wc-refref-integration';
		$this->method_title       = __( 'RefRef', 'woocommerce-integration' );
		$this->method_description = __( 'Integration of RefRef platform for WooCommerce.', 'woocommerce-integration' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->merchant_id        = $this->get_option( 'merchant_id' );
		$this->secret_token       = $this->get_option( 'secret_token' );

		// Actions.
		add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_order_status_changed',                     [$this, 'refref_on_order_changed'], 10, 3);
		add_action( 'woocommerce_thankyou',                                 [$this, 'refref_remove_input']);
		add_action( 'wp_footer',                                            [$this, 'refref_render_widget']);

		// Filters.
		add_filter( 'woocommerce_settings_api_sanitized_fields_' . $this->id, array( $this, 'sanitize_settings' ) );
	}

	public function refref_on_order_changed($order_id, $from, $to) {
		if (!is_admin() && $to === "on-hold") {
			$order = new WC_Order($order_id);
			$order_data = $order->get_data();

	  	$base_url = "https://refref.app/api/v1/woocommerce";
			$body = array(
				'orderJsonString' => json_encode($order_data),
				'referralCode'    => sanitize_text_field($_COOKIE["refrefReferralCode"]),
			);
			$headers = array(
				'merchant' => $this->merchant_id,
				'token'    => $this->secret_token,
			);

			$args = array(
				'body'    => $body,
				'headers' => $headers,
			);
			$response = wp_remote_post( $base_url, $args );

			if (is_wp_error($response)) {
				error_log(print_r($response, TRUE));
			}
		}
	}

	public function refref_remove_input() {
		// TODO: popup widget and say something
		wp_register_script( 'refref-remove-cookies', '' );
		wp_enqueue_script( 'refref-remove-cookies' );
		wp_add_inline_script("refref-remove-cookies", 'document.cookie = "refrefReferralCode= ; expires = Thu, 01 Jan 1970 00:00:00 GMT;  path=/";');
	}

	public function refref_render_widget() {
		if (!is_admin() && !empty($this->merchant_id)) {

			$widget_markup = '<div id="refref-widget__r1g83za" data-merchant-id="' . $this->merchant_id . '">';
			echo wp_kses($widget_markup, array( 
				'div' => array(
					'id' => array(),
					'data-merchant-id' => array(),
				)
			));

			wp_enqueue_script( 'refref-widget', "https://files.refref.app/static/widget.js");
		}
	}

	/**
	 * Initialize integration settings form fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'merchant_id' => array(
				'title'             => __( 'Merchant ID', 'woocommerce-integration' ),
				'type'              => 'text',
				'description'       => __( 'Enter your RefRef merchant ID. You can find this information at https://merchant.refref.app/connect', 'woocommerce-integration' ),
				'default'           => ''
			),
			'secret_token' => array(
				'title'             => __( 'Token', 'woocommerce-integration' ),
				'type'              => 'password',
				'description'       => __( 'Enter your RefRef secret token. You can find this information at https://merchant.refref.app/connect', 'woocommerce-integration' ),
				'default'           => ''
			),
		);
	}

	/**
	 * Sanitize our settings
	 */
	public function sanitize_settings( $settings ) {
		if ( isset( $settings ) && isset( $settings['merchant_id'] ) ) {
			$settings['merchant_id'] = sanitize_text_field( $settings['merchant_id'] );
		}

		if ( isset( $settings ) && isset( $settings['secret_token'] ) ) {
			$settings['secret_token'] = sanitize_text_field( $settings['secret_token'] );
		}

		return $settings;
	}
}

endif;
