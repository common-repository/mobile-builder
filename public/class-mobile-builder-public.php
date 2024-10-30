<?php

use \Firebase\JWT\JWT;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://rnlab.io
 * @since      1.0.0
 *
 * @package    Mobile_Builder
 * @subpackage Mobile_Builder/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Mobile_Builder
 * @subpackage Mobile_Builder/public
 * @author     RNLAB <ngocdt@rnlab.io>
 */
class Mobile_Builder_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 *  Then key to encode token
	 * @since    1.0.0
	 * @access   private
	 * @var      string $key The key to encode token
	 */
	private $key;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since      1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->key         = defined( 'MOBILE_BUILDER_JWT_SECRET_KEY' ) ? MOBILE_BUILDER_JWT_SECRET_KEY : "example_key";

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.2.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Blog_1_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Blog_1_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( isset( $_GET['mobile'] ) ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/checkout.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Registers a REST API route
	 *
	 * @since 1.0.0
	 */
	public function add_api_routes() {
		$namespace = $this->plugin_name . '/v' . intval( $this->version );
		$review    = new WC_REST_Product_Reviews_Controller();
		$customer  = new WC_REST_Customers_Controller();

		/**
		 * @since 1.3.4
		 */
		register_rest_route( $namespace, 'reviews', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $review, 'create_item' ),
			'permission_callback' => '__return_true',
		) );

		/**
		 * @since 1.3.4
		 */
		register_rest_route( $namespace, 'customers/(?P<id>[\d]+)', array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => array( $customer, 'update_item' ),
			'permission_callback' => array( $this, 'update_item_permissions_check' ),
		) );

		register_rest_route( $namespace, 'token', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'app_token' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, 'login', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'login' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, 'logout', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'logout' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, 'login-otp', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'login_otp' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, 'current', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'current' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, 'facebook', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'login_facebook' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, 'google', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'login_google' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, 'apple', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'login_apple' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, 'register', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'register' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, 'lost-password', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'retrieve_password' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, 'settings', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'settings' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, 'change-password', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'change_password' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, 'update-location', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'update_location' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, 'zones', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'zones' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, 'get-continent-code-for-country', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_continent_code_for_country' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, 'payment-stripe', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'payment_stripe' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, 'payment-hayperpay', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'payment_hayperpay' ),
			'permission_callback' => '__return_true',
		) );

		/**
		 * Add payment router
		 *
		 * @author Ngoc Dang
		 * @since 1.1.0
		 */
		register_rest_route( $namespace, 'process_payment', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'rnlab_process_payment' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_field( 'post', '_categories', array(
			'get_callback' => function ( $post ) {
				$cats = array();
				foreach ( $post['categories'] as $c ) {
					$cat    = get_category( $c );
					$cats[] = $cat->name;
				}

				return $cats;
			},
		) );

		/**
		 * register rest post field
		 *
		 * @author Ngoc Dang
		 * @since 1.1.0
		 */
		register_rest_field( 'post', 'rnlab_featured_media_url',
			array(
				'get_callback'    => array( $this, 'get_featured_media_url' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);

		/**
		 * Check mobile phone number
		 *
		 * @author Ngoc Dang
		 * @since 1.2.0
		 */
		register_rest_route( $namespace, 'check-phone-number', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'mbd_check_phone_number' ),
			'permission_callback' => '__return_true',
		) );

		/**
		 * Check email and username
		 *
		 * @author Ngoc Dang
		 * @since 1.2.0
		 */
		register_rest_route( $namespace, 'check-info', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'mbd_validate_user_info' ),
			'permission_callback' => '__return_true',
		) );

		/**
		 * Get recursion category
		 *
		 * @author Ngoc Dang
		 * @since 1.3.4
		 */
		register_rest_route( $namespace, 'categories', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'categories' ),
			'permission_callback' => '__return_true',
		) );

	}

	/**
	 * Check mobile phone number
	 *
	 * @author Ngoc Dang
	 * @since 1.2.0
	 */
	public function mbd_check_phone_number( $request ) {
		$digits_phone = $request->get_param( 'digits_phone' );
		$type         = $request->get_param( 'type' );

		$users = get_users( array(
			"meta_key"     => "digits_phone",
			"meta_value"   => $digits_phone,
			"meta_compare" => "="
		) );

		if ( $type == "register" ) {
			if ( count( $users ) > 0 ) {
				$error = new WP_Error();
				$error->add( 403, __( "Your phone number already exist in database!", "mobile-builder" ), array( 'status' => 400 ) );

				return $error;
			}

			return new WP_REST_Response( array( "data" => __( "Phone number not exits!", "mobile-builder" ) ), 200 );
		}

		// Login folow
		if ( count( $users ) == 0 ) {
			$error = new WP_Error();
			$error->add( 403, __( "Your phone number not exist in database!", "mobile-builder" ), array( 'status' => 400 ) );

			return $error;
		}

		return new WP_REST_Response( array( "data" => __( "Phone number number exist!", "mobile-builder" ) ), 200 );
	}

	/**
	 * Change the way encode token
	 *
	 * @author Ngoc Dang
	 * @since 1.3.4
	 */
	public function custom_digits_rest_token_data( $token, $user_id ) {
		$user = get_user_by( 'id', $user_id );
		if ( $user ) {
			$token = $this->generate_token( $user );
			$data  = array(
				'token' => $token,
				'user'  => $this->mbd_get_userdata( $user ),
			);
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( new WP_Error(
				404,
				__( 'Something wrong!.', "mobile-builder" ),
				array(
					'status' => 403,
				)
			) );
		}
	}

	/**
	 * Change checkout template
	 *
	 * @author Ngoc Dang
	 * @since 1.2.0
	 */
	public function woocommerce_locate_template( $template, $template_name, $template_path ) {
		if ( 'checkout/form-checkout.php' == $template_name && isset( $_GET['mobile'] ) ) {
			return plugin_dir_path( __DIR__ ) . 'templates/checkout/form-checkout.php';
		}

		if ( 'checkout/thankyou.php' == $template_name && isset( $_GET['mobile'] ) ) {
			return plugin_dir_path( __DIR__ ) . 'templates/checkout/thankyou.php';
		}

		if ( 'checkout/form-pay.php' == $template_name && isset( $_GET['mobile'] ) ) {
			return plugin_dir_path( __DIR__ ) . 'templates/checkout/form-pay.php';
		}

		return $template;
	}

	/**
	 * Find the selected Gateway, and process payment
	 *
	 * @author Ngoc Dang
	 * @since 1.1.0
	 */
	public function rnlab_process_payment( $request = null ) {

		// Create a Response Object
		$response = array();

		// Get parameters
		$order_id       = $request->get_param( 'order_id' );
		$payment_method = $request->get_param( 'payment_method' );

		$error = new WP_Error();

		// Perform Pre Checks
		if ( ! class_exists( 'WooCommerce' ) ) {
			$error->add( 400, __( "Failed to process payment. WooCommerce either missing or deactivated.", 'mobile-builder' ), array( 'status' => 400 ) );

			return $error;
		}
		if ( empty( $order_id ) ) {
			$error->add( 401, __( "Order ID 'order_id' is required.", 'mobile-builder' ), array( 'status' => 400 ) );

			return $error;
		} else if ( wc_get_order( $order_id ) == false ) {
			$error->add( 402, __( "Order ID 'order_id' is invalid. Order does not exist.", 'mobile-builder' ), array( 'status' => 400 ) );

			return $error;
		} else if ( wc_get_order( $order_id )->get_status() !== 'pending' && wc_get_order( $order_id )->get_status() !== 'failed' ) {
			$error->add( 403, __( "Order status is '" . wc_get_order( $order_id )->get_status() . "', meaning it had already received a successful payment. Duplicate payments to the order is not allowed. The allow status it is either 'pending' or 'failed'. ", 'mobile-builder' ), array( 'status' => 400 ) );

			return $error;
		}
		if ( empty( $payment_method ) ) {
			$error->add( 404, __( "Payment Method 'payment_method' is required.", 'mobile-builder' ), array( 'status' => 400 ) );

			return $error;
		}

		// Find Gateway
		$avaiable_gateways = WC()->payment_gateways->get_available_payment_gateways();
		$gateway           = $avaiable_gateways[ $payment_method ];

		if ( empty( $gateway ) ) {
			$all_gateways = WC()->payment_gateways->payment_gateways();
			$gateway      = $all_gateways[ $payment_method ];

			if ( empty( $gateway ) ) {
				$error->add( 405, __( "Failed to process payment. WooCommerce Gateway '" . $payment_method . "' is missing.", 'mobile-builder' ), array( 'status' => 400 ) );

				return $error;
			} else {
				$error->add( 406, __( "Failed to process payment. WooCommerce Gateway '" . $payment_method . "' exists, but is not available.", 'mobile-builder' ), array( 'status' => 400 ) );

				return $error;
			}
		} else if ( ! has_filter( 'rnlab_pre_process_' . $payment_method . '_payment' ) ) {
			$error->add( 407, __( "Failed to process payment. WooCommerce Gateway '" . $payment_method . "' exists, but 'REST Payment - " . $payment_method . "' is not available.", 'mobile-builder' ), array( 'status' => 400 ) );

			return $error;
		} else {

			// Pre Process Payment
			$parameters = apply_filters( 'rnlab_pre_process_' . $payment_method . '_payment', array(
				"order_id"       => $order_id,
				"payment_method" => $payment_method
			) );

			if ( $parameters['pre_process_result'] === true ) {

				// Process Payment
				$payment_result = $gateway->process_payment( $order_id );
				if ( $payment_result['result'] === "success" ) {
					$response['code']    = 200;
					$response['message'] = __( "Payment Successful.", "mobile-builder" );
					$response['data']    = $payment_result;

					// Return Successful Response
					return new WP_REST_Response( $response, 200 );
				} else {
					return new WP_Error( 500, __( 'Payment Failed, Check WooCommerce Status Log for further information.', "mobile-builder" ), $payment_result );
				}
			} else {
				return new WP_Error( 408, __( 'Payment Failed, Pre Process Failed.', "mobile-builder" ), $parameters['pre_process_result'] );
			}

		}

	}

	/**
	 * Registers a REST API route
	 *
	 * @since 1.0.5
	 */
	public function payment_hayperpay( $request ) {
		$response = array();

		$order_id             = $request->get_param( 'order_id' );
		$wc_gate2play_gateway = new WC_gate2play_Gateway();
		$payment_result       = $wc_gate2play_gateway->process_payment( $order_id );

		if ( $payment_result['result'] === "success" ) {
			$response['code']     = 200;
			$response['message']  = __( "Your Payment was Successful", "mobile-builder" );
			$response['redirect'] = $payment_result['redirect'];
		} else {
			$response['code']    = 401;
			$response['message'] = __( "Please enter valid card details", "mobile-builder" );
		}

		return new WP_REST_Response( $response );
	}

	public function payment_stripe( $request ) {
		$response = array();

		$order_id      = $request->get_param( 'order_id' );
		$stripe_source = $request->get_param( 'stripe_source' );

		$error = new WP_Error();

		if ( empty( $order_id ) ) {
			$error->add( 401, __( "Order ID 'order_id' is required.", 'mobile-builder' ), array( 'status' => 400 ) );

			return $error;
		} else if ( wc_get_order( $order_id ) == false ) {
			$error->add( 402, __( "Order ID 'order_id' is invalid. Order does not exist.", 'mobile-builder' ),
				array( 'status' => 400 ) );

			return $error;
		}

		if ( empty( $stripe_source ) ) {
			$error->add( 404, __( "Payment source 'stripe_source' is required.", 'mobile-builder' ),
				array( 'status' => 400 ) );

			return $error;
		}

		$wc_gateway_stripe = new WC_Gateway_Stripe();

		$_POST['stripe_source']  = $stripe_source;
		$_POST['payment_method'] = "stripe";

		// Fix empty cart in process_payment
		WC()->session = new WC_Session_Handler();
		WC()->session->init();
		WC()->customer = new WC_Customer( get_current_user_id(), true );
		WC()->cart     = new WC_Cart();

		$payment_result = $wc_gateway_stripe->process_payment( $order_id );

		if ( $payment_result['result'] === "success" ) {
			$response['code']    = 200;
			$response['message'] = __( "Your Payment was Successful", "mobile-builder" );

			// $order = wc_get_order( $order_id );

			// set order to completed
			// if ( $order->get_status() == 'processing' ) {
			// 	$order->update_status( 'completed' );
			// }

		} else {
			$response['code']    = 401;
			$response['message'] = __( "Please enter valid card details", "mobile-builder" );
		}

		return new WP_REST_Response( $response );
	}

	public function get_continent_code_for_country( $request ) {
		$cc         = $request->get_param( 'cc' );
		$wc_country = new WC_Countries();

		wp_send_json( $wc_country->get_continent_code_for_country( $cc ) );
	}

	public function zones() {
		$delivery_zones = (array) WC_Shipping_Zones::get_zones();

		$data = [];
		foreach ( $delivery_zones as $key => $the_zone ) {

			$shipping_methods = [];

			foreach ( $the_zone['shipping_methods'] as $value ) {

				$shipping_methods[] = array(
					'instance_id'        => $value->instance_id,
					'id'                 => $value->instance_id,
					'method_id'          => $value->id,
					'method_title'       => $value->title,
					'method_description' => $value->method_description,
					'settings'           => array(
						'cost' => array(
							'value' => $value->cost
						)
					),
				);
			}

			$data[] = array(
				'id'               => $the_zone['id'],
				'zone_name'        => $the_zone['zone_name'],
				'zone_locations'   => $the_zone['zone_locations'],
				'shipping_methods' => $shipping_methods,
			);

		}

		wp_send_json( $data );
	}

	public function change_password( $request ) {

		$current_user = wp_get_current_user();
		if ( ! $current_user->exists() ) {
			return new WP_Error(
				'user_not_login',
				__( 'Please login first.', "mobile-builder" ),
				array(
					'status' => 403,
				)
			);
		}

		$username     = $current_user->user_login;
		$password_old = $request->get_param( 'password_old' );
		$password_new = $request->get_param( 'password_new' );

		// try login with username and password
		$user = wp_authenticate( $username, $password_old );

		if ( is_wp_error( $user ) ) {
			$error_code = $user->get_error_code();

			return new WP_Error(
				$error_code,
				$user->get_error_message( $error_code ),
				array(
					'status' => 403,
				)
			);
		}

		wp_set_password( $password_new, $current_user->ID );

		return $current_user->ID;
	}

	/**
	 *
	 * Update User Location
	 *
	 * @param $request
	 *
	 * @return int|WP_Error
	 * @since 1.4.3
	 *
	 */
	public function update_location( $request ) {

		$current_user = wp_get_current_user();

		if ( ! $current_user->exists() ) {
			return new WP_Error(
				'user_not_login',
				__( 'Please login first.', "mobile-builder" ),
				array(
					'status' => 403,
				)
			);
		}

		$location = $request->get_param( 'location' );

		update_user_meta( $current_user->ID, 'mbd_location', $location );

		return $current_user->ID;
	}


	public function settings( $request ) {

		$decode = $request->get_param( 'decode' );

		$result = wp_cache_get( 'settings_' . $decode, 'rnlab' );

		if ( $result ) {
			return $result;
		}

		try {
			global $woocommerce_wpml;

			$admin = new Mobile_Builder_Admin( MOBILE_BUILDER_PLUGIN_NAME, MOBILE_BUILDER_CONTROL_VERSION );

			$currencies = array();

			$languages    = apply_filters( 'wpml_active_languages', array(), 'orderby=id&order=desc' );
			$default_lang = apply_filters( 'wpml_default_language', substr( get_locale(), 0, 2 ) );

			$currency = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'USD';

			if ( ! empty( $woocommerce_wpml->multi_currency ) && ! empty( $woocommerce_wpml->settings['currencies_order'] ) ) {
				$currencies = $woocommerce_wpml->multi_currency->get_currencies( 'include_default = true' );
			}

			$configs = get_option( 'mobile_builder_configs', array(
				"requireLogin"       => false,
				"toggleSidebar"      => false,
				"isBeforeNewProduct" => 5
			) );

			$gmw = get_option( 'gmw_options' );

			$templates      = array();
			$templates_data = $admin->template_configs();

			if ( $decode ) {
				foreach ( $templates_data as $template ) {
					$template->data     = json_decode( $template->data );
					$template->settings = json_decode( $template->settings );
					$templates[]        = $template;
				}
			}

			$result = array(
				'language'               => $default_lang,
				'languages'              => $languages,
				'currencies'             => $currencies,
				'currency'               => $currency,
				'enable_guest_checkout'  => get_option( 'woocommerce_enable_guest_checkout', true ),
				'timezone_string'        => get_option( 'timezone_string' ) ? get_option( 'timezone_string' ) : wc_timezone_string(),
				'date_format'            => get_option( 'date_format' ),
				'time_format'            => get_option( 'time_format' ),
				'configs'                => maybe_unserialize( $configs ),
				'default_location'       => $gmw ? $gmw['post_types_settings'] : $gmw,
				'templates'              => $decode ? $templates : $templates_data,
				'checkout_user_location' => apply_filters( 'wcfmmp_is_allow_checkout_user_location', true ),
			);

			wp_cache_set( 'settings_' . $decode, $result, 'rnlab' );

			wp_send_json( $result );
		} catch ( Exception $e ) {
			return new WP_Error(
				'error_setting',
				__( 'Some thing wrong.', "mobile-builder" ),
				array(
					'status' => 403,
				)
			);
		}
	}

	/**
	 * Create token for app
	 *
	 * @param $request
	 *
	 * @return bool|WP_Error
	 */
	public function app_token() {

		$wp_auth_user = defined( 'WP_AUTH_USER' ) ? WP_AUTH_USER : "wp_auth_user";

		$user = get_user_by( 'login', $wp_auth_user );

		if ( $user ) {
			$token = $this->generate_token( $user, array( 'read_only' => true ) );

			return $token;
		} else {
			return new WP_Error(
				'create_token_error',
				__( 'You did not create user wp_auth_user', "mobile-builder" ),
				array(
					'status' => 403,
				)
			);
		}
	}

	/**
	 * Lost password for user
	 *
	 * @param $request
	 *
	 * @return bool|WP_Error
	 */
	public function retrieve_password( $request ) {
		$errors = new WP_Error();

		$user_login = $request->get_param( 'user_login' );

		if ( empty( $user_login ) || ! is_string( $user_login ) ) {
			$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Enter a username or email address.', "mobile-builder" ) );
		} elseif ( strpos( $user_login, '@' ) ) {
			$user_data = get_user_by( 'email', trim( wp_unslash( $user_login ) ) );
			if ( empty( $user_data ) ) {
				$errors->add( 'invalid_email',
					__( '<strong>ERROR</strong>: There is no account with that username or email address.', "mobile-builder" ) );
			}
		} else {
			$login     = trim( $user_login );
			$user_data = get_user_by( 'login', $login );
		}

		if ( $errors->has_errors() ) {
			return $errors;
		}

		if ( ! $user_data ) {
			$errors->add( 'invalidcombo',
				__( '<strong>ERROR</strong>: There is no account with that username or email address.', "mobile-builder" ) );

			return $errors;
		}

		// Redefining user_login ensures we return the right case in the email.
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;
		$key        = get_password_reset_key( $user_data );

		if ( is_wp_error( $key ) ) {
			return $key;
		}

		if ( is_multisite() ) {
			$site_name = get_network()->site_name;
		} else {
			/*
			 * The blogname option is escaped with esc_html on the way into the database
			 * in sanitize_option we want to reverse this for the plain text arena of emails.
			 */
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$message = __( 'Someone has requested a password reset for the following account:', "mobile-builder" ) . "\r\n\r\n";
		/* translators: %s: site name */
		$message .= sprintf( __( 'Site Name: %s', "mobile-builder" ), $site_name ) . "\r\n\r\n";
		/* translators: %s: user login */
		$message .= sprintf( __( 'Username: %s', "mobile-builder" ), $user_login ) . "\r\n\r\n";
		$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.', "mobile-builder" ) . "\r\n\r\n";
		$message .= __( 'To reset your password, visit the following address:', "mobile-builder" ) . "\r\n\r\n";
		$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ),
				'login' ) . ">\r\n";

		/* translators: Password reset notification email subject. %s: Site title */
		$title = sprintf( __( '[%s] Password Reset', "mobile-builder" ), $site_name );

		/**
		 * Filters the subject of the password reset email.
		 *
		 * @param string $title Default email title.
		 * @param string $user_login The username for the user.
		 * @param WP_User $user_data WP_User object.
		 *
		 * @since 4.4.0 Added the `$user_login` and `$user_data` parameters.
		 *
		 * @since 2.8.0
		 */
		$title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );

		/**
		 * Filters the message body of the password reset mail.
		 *
		 * If the filtered message is empty, the password reset email will not be sent.
		 *
		 * @param string $message Default mail message.
		 * @param string $key The activation key.
		 * @param string $user_login The username for the user.
		 * @param WP_User $user_data WP_User object.
		 *
		 * @since 2.8.0
		 * @since 4.1.0 Added `$user_login` and `$user_data` parameters.
		 *
		 */
		$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

		if ( $message && ! wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ) {
			return new WP_Error(
				'send_email',
				__( 'Possible reason: your host may have disabled the mail() function.', "mobile-builder" ),
				array(
					'status' => 403,
				)
			);
		}

		return true;
	}

	/**
	 *  Get current user login
	 *
	 * @param $request
	 *
	 * @return mixed
	 */
	public function current( $request ) {
		$current_user = wp_get_current_user();

		return $current_user->data;
	}

	/**
	 *  Validate user
	 *
	 * @param $request
	 *
	 * @return mixed
	 */
	public function mbd_validate_user_info( $request ) {

		$email = $request->get_param( 'email' );
		$name  = $request->get_param( 'name' );

		// Validate email
		if ( ! is_email( $email ) || email_exists( $email ) ) {
			return new WP_Error(
				"email",
				__( "Your input email not valid or exist in database.", "mobile-builder" ),
				array(
					'status' => 403,
				)
			);
		}

		// Validate username
		if ( username_exists( $name ) || empty( $name ) ) {
			return new WP_Error(
				"name",
				__( "Your username exist.", "mobile-builder" ),
				array(
					'status' => 403,
				)
			);
		}

		return array( "message" => __( "success!", "mobile-builder" ) );
	}

	/**
	 *  Register new user
	 *
	 * @param $request
	 *
	 * @return mixed
	 */
	public function register( $request ) {
		$email      = $request->get_param( 'email' );
		$name       = $request->get_param( 'name' );
		$first_name = $request->get_param( 'first_name' );
		$last_name  = $request->get_param( 'last_name' );
		$password   = $request->get_param( 'password' );
		$subscribe  = $request->get_param( 'subscribe' );
		$role       = $request->get_param( 'role' );

		if ( ! $role || $role != 'wcfm_vendor' ) {
			$role = "customer";
		}

		$enable_phone_number = $request->get_param( 'enable_phone_number' );

		// Validate email
		if ( ! is_email( $email ) || email_exists( $email ) ) {
			return new WP_Error(
				"email",
				__( "Your input email not valid or exist in database.", "mobile-builder" ),
				array(
					'status' => 403,
				)
			);
		}

		// Validate username
		if ( username_exists( $name ) || empty( $name ) ) {
			return new WP_Error(
				"name",
				__( "Your username exist.", "mobile-builder" ),
				array(
					'status' => 403,
				)
			);
		}

		// Validate first name
		if ( mb_strlen( $first_name ) < 2 ) {
			return new WP_Error(
				"first_name",
				__( "First name not valid.", "mobile-builder" ),
				array(
					'status' => 403,
				)
			);
		}

		// Validate last name
		if ( mb_strlen( $last_name ) < 2 ) {
			return new WP_Error(
				"last_name",
				__( "Last name not valid.", "mobile-builder" ),
				array(
					'status' => 403,
				)
			);
		}

		// Validate password
		if ( empty( $password ) ) {
			return new WP_Error(
				"password",
				__( "Password is required.", "mobile-builder" ),
				array(
					'status' => 403,
				)
			);
		}

		$user_id = wp_insert_user( array(
			"user_pass"    => $password,
			"user_email"   => $email,
			"user_login"   => $name,
			"display_name" => "$first_name $last_name",
			"first_name"   => $first_name,
			"last_name"    => $last_name,
			"role"         => $role,

		) );

		if ( is_wp_error( $user_id ) ) {
			$error_code = $user_id->get_error_code();

			return new WP_Error(
				$error_code,
				$user_id->get_error_message( $error_code ),
				array(
					'status' => 403,
				)
			);
		}

		// Update phone number
		if ( $enable_phone_number ) {
			$digits_phone     = $request->get_param( 'digits_phone' );
			$digt_countrycode = $request->get_param( 'digt_countrycode' );
			$digits_phone_no  = $request->get_param( 'digits_phone_no' );

			// Validate phone
			if ( ! $digits_phone || ! $digt_countrycode || ! $digits_phone_no ) {
				wp_delete_user( $user_id );

				return new WP_Error(
					'number_not_validate',
					__( 'Your phone number not validate', "mobile-builder" ),
					array(
						'status' => 403,
					)
				);
			}

			// Check phone number in database
			$users = get_users( array(
				"meta_key"     => "digits_phone",
				"meta_value"   => $digits_phone,
				"meta_compare" => "="
			) );

			if ( count( $users ) > 0 ) {
				wp_delete_user( $user_id );

				return new WP_Error(
					'phone_number_exist',
					__( "Your phone number already exist in database!", "mobile-builder" ),
					array( 'status' => 400 )
				);
			}

			add_user_meta( $user_id, 'digt_countrycode', $digt_countrycode, true );
			add_user_meta( $user_id, 'digits_phone_no', $digits_phone_no, true );
			add_user_meta( $user_id, 'digits_phone', $digits_phone, true );
		}

		// Subscribe
		add_user_meta( $user_id, 'mbd_subscribe', $subscribe, true );

		$user  = get_user_by( 'id', $user_id );
		$token = $this->generate_token( $user );
		$data  = array(
			'token' => $token,
			'user'  => $this->mbd_get_userdata( $user ),
		);

		return $data;

	}

	public function getUrlContent( $url ) {
		$parts  = parse_url( $url );
		$host   = $parts['host'];
		$ch     = curl_init();
		$header = array(
			'GET /1575051 HTTP/1.1',
			"Host: {$host}",
			'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'Accept-Language:en-US,en;q=0.8',
			'Cache-Control:max-age=0',
			'Connection:keep-alive',
			'Host:adfoc.us',
			'User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.116 Safari/537.36',
		);

		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 0 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
		$result = curl_exec( $ch );
		curl_close( $ch );

		return $result;
	}

	/**
	 * Login with google
	 *
	 * @param $request
	 */
	public function login_google( $request ) {
		$idToken = $request->get_param( 'idToken' );

		$url  = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . $idToken;
		$data = array( 'idToken' => $idToken );

		// use key 'http' even if you send the request to https://...
		$options = array(
			'http' => array(
				'header' => "application/json; charset=UTF-8\r\n",
				'method' => 'GET'
			)
		);

		$context = stream_context_create( $options );
		$json    = $this->getUrlContent( $url );
		$result  = json_decode( $json );

		if ( $result === false ) {
			$error = new WP_Error();
			$error->add( 403, __( "Get Firebase user info error!", "mobile-builder" ), array( 'status' => 400 ) );

			return $error;
		}

		// Email not exist
		$email = $result->email;
		if ( ! $email ) {
			return new WP_Error(
				'email_not_exist',
				__( 'User not provider email', "mobile-builder" ),
				array(
					'status' => 403,
				)
			);
		}

		$user = get_user_by( 'email', $email );

		// Return data if user exist in database
		if ( $user ) {
			$token = $this->generate_token( $user );
			$data  = array(
				'token' => $token,
				'user'  => $this->mbd_get_userdata( $user ),
			);

			return $data;
		} else {

			$user_id = wp_insert_user( array(
				"user_pass"     => wp_generate_password(),
				"user_login"    => $result->email,
				"user_nicename" => $result->name,
				"user_email"    => $result->email,
				"display_name"  => $result->name,
				"first_name"    => $result->given_name,
				"last_name"     => $result->family_name

			) );

			if ( is_wp_error( $user_id ) ) {
				$error_code = $user->get_error_code();

				return new WP_Error(
					$error_code,
					$user_id->get_error_message( $error_code ),
					array(
						'status' => 403,
					)
				);
			}

			$user = get_user_by( 'id', $user_id );

			$token = $this->generate_token( $user );
			$data  = array(
				'token' => $token,
				'user'  => $this->mbd_get_userdata( $user ),
			);

			add_user_meta( $user_id, 'mbd_login_method', 'google', true );
			add_user_meta( $user_id, 'mbd_avatar', $result->picture, true );

			return $data;
		}
	}

	/**
	 * Login With Apple
	 *
	 * @param $request
	 *
	 * @return array | object
	 * @throws Exception
	 */
	public function login_apple( $request ) {
		try {
			$identityToken = $request->get_param( 'identityToken' );
			$userIdentity  = $request->get_param( 'user' );
			$fullName      = $request->get_param( 'fullName' );

			$tks = \explode( '.', $identityToken );
			if ( \count( $tks ) != 3 ) {
				return new WP_Error(
					'error_login_apple',
					__( 'Wrong number of segments', "mobile-builder" ),
					array(
						'status' => 403,
					)
				);
			}

			list( $headb64 ) = $tks;

			if ( null === ( $header = JWT::jsonDecode( JWT::urlsafeB64Decode( $headb64 ) ) ) ) {
				return new WP_Error(
					'error_login_apple',
					__( 'Invalid header encoding', "mobile-builder" ),
					array(
						'status' => 403,
					)
				);
			}

			if ( ! isset( $header->kid ) ) {
				return new WP_Error(
					'error_login_apple',
					__( '"kid" empty, unable to lookup correct key', "mobile-builder" ),
					array(
						'status' => 403,
					)
				);
			}

			$publicKeyDetails = Mobile_Builder_Public_Key::getPublicKey( $header->kid );
			$publicKey        = $publicKeyDetails['publicKey'];
			$alg              = $publicKeyDetails['alg'];

			$payload = JWT::decode( $identityToken, $publicKey, [ $alg ] );

			if ( $payload->sub !== $userIdentity ) {
				return new WP_Error(
					'validate-user',
					__( 'User not validate', "mobile-builder" ),
					array(
						'status' => 403,
					)
				);
			}

			$user1 = get_user_by( 'email', $payload->email );
			$user2 = get_user_by( 'login', $userIdentity );

			// Return data if user exist in database
			if ( $user1 ) {
				$token = $this->generate_token( $user1 );

				return array(
					'token' => $token,
					'user'  => $this->mbd_get_userdata( $user1 ),
				);
			}

			if ( $user2 ) {
				$token = $this->generate_token( $user2 );

				return array(
					'token' => $token,
					'user'  => $this->mbd_get_userdata( $user2 ),
				);
			}

			$userdata = array(
				"user_pass"    => wp_generate_password(),
				"user_login"   => $userIdentity,
				"user_email"   => $payload->email,
				"display_name" => $fullName['familyName'] . " " . $fullName['givenName'],
				"first_name"   => $fullName['familyName'],
				"last_name"    => $fullName['givenName']
			);

			$user_id = wp_insert_user( $userdata );

			if ( is_wp_error( $user_id ) ) {
				$error_code = $user_id->get_error_code();

				return new WP_Error(
					$error_code,
					$user_id->get_error_message( $error_code ),
					array(
						'status' => 403,
					)
				);
			}

			$user = get_user_by( 'id', $user_id );

			$token = $this->generate_token( $user );

			add_user_meta( $user_id, 'mbd_login_method', 'apple', true );

			return array(
				'token' => $token,
				'user'  => $this->mbd_get_userdata( $user ),
			);

		} catch ( Exception $e ) {
			return new WP_Error(
				'error_login_apple',
				$e->getMessage(),
				array(
					'status' => 403,
				)
			);
		}
	}

	public function login_facebook( $request ) {
		$token = $request->get_param( 'token' );

		$fb = new \Facebook\Facebook( [
			'app_id'                => MOBILE_BUILDER_FB_APP_ID,
			'app_secret'            => MOBILE_BUILDER_FB_APP_SECRET,
			'default_graph_version' => 'v2.10',
			//'default_access_token' => '{access-token}', // optional
		] );

		try {
			// Get the \Facebook\GraphNodes\GraphUser object for the current user.
			// If you provided a 'default_access_token', the '{access-token}' is optional.
			$response = $fb->get( '/me?fields=id,first_name,last_name,name,picture,email', $token );
		} catch ( \Facebook\Exceptions\FacebookResponseException $e ) {
			// When Graph returns an error
			echo __( 'Graph returned an error: ', "mobile-builder" ) . $e->getMessage();
			exit;
		} catch ( \Facebook\Exceptions\FacebookSDKException $e ) {
			// When validation fails or other local issues
			echo __( 'Facebook SDK returned an error: ', "mobile-builder" ) . $e->getMessage();
			exit;
		}

		$me = $response->getGraphUser();

		// Email not exist
		$email = $me->getEmail();
		if ( ! $email ) {
			return new WP_Error(
				'email_not_exist',
				__( 'User not provider email', "mobile-builder" ),
				array(
					'status' => 403,
				)
			);
		}

		$user = get_user_by( 'email', $email );

		// Return data if user exist in database
		if ( $user ) {
			$token = $this->generate_token( $user );
			$data  = array(
				'token' => $token,
				'user'  => $this->mbd_get_userdata( $user ),
			);

			return $data;
		} else {
			// Will create new user
			$first_name  = $me->getFirstName();
			$last_name   = $me->getLastName();
			$picture     = $me->getPicture();
			$name        = $me->getName();
			$facebook_id = $me->getId();

			$user_id = wp_insert_user( array(
				"user_pass"     => wp_generate_password(),
				"user_login"    => $email,
				"user_nicename" => $name,
				"user_email"    => $email,
				"display_name"  => $name,
				"first_name"    => $first_name,
				"last_name"     => $last_name

			) );

			if ( is_wp_error( $user_id ) ) {
				$error_code = $user->get_error_code();

				return new WP_Error(
					$error_code,
					$user_id->get_error_message( $error_code ),
					array(
						'status' => 403,
					)
				);
			}

			$user = get_user_by( 'id', $user_id );

			$token = $this->generate_token( $user );
			$data  = array(
				'token' => $token,
				'user'  => $this->mbd_get_userdata( $user ),
			);

			add_user_meta( $user_id, 'mbd_login_method', 'facebook', true );
			add_user_meta( $user_id, 'mbd_avatar', $picture, true );

			return $data;
		}

	}

	/**
	 * Do login with email and password
	 */
	public function login( $request ) {

		$username = $request->get_param( 'username' );
		$password = $request->get_param( 'password' );

		// try login with username and password
		$user = wp_authenticate( $username, $password );

		if ( is_wp_error( $user ) ) {
			return $user;
		}

		// Generate token
		$token = $this->generate_token( $user );

		// Return data
		$data = array(
			'token' => $token,
			'user'  => $this->mbd_get_userdata( $user ),
		);

		return $data;
	}

	/**
	 *
	 * Log out user
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function logout() {
		wp_logout();

		return array( "success" => true );
	}

	/**
	 * Do login with with otp
	 */
	public function login_otp( $request ) {

		try {

			if ( ! defined( 'MOBILE_BUILDER_FIREBASE_SERVER_KEY' ) ) {
				return new WP_Error(
					'not_exist_firebase_server_key',
					__( 'The MOBILE_BUILDER_FIREBASE_SERVER_KEY not define in wp-config.php', "mobile-builder" ),
					array(
						'status' => 403,
					)
				);
			}

			$idToken = $request->get_param( 'idToken' );

			$url  = 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/getAccountInfo?key=' . MOBILE_BUILDER_FIREBASE_SERVER_KEY;
			$data = array( 'idToken' => $idToken );

			// use key 'http' even if you send the request to https://...
			$options = array(
				'http' => array(
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'method'  => 'POST',
					'content' => http_build_query( $data )
				)
			);

			$context = stream_context_create( $options );
			$json    = file_get_contents( $url, false, $context );
			$result  = json_decode( $json );

			if ( $result === false ) {
				$error = new WP_Error();
				$error->add( 403, __( "Get Firebase user info error!", "mobile-builder" ), array( 'status' => 400 ) );

				return $error;
			}

			if ( ! isset( $result->users[0]->phoneNumber ) ) {
				return new WP_Error(
					'not_exist_firebase_user',
					__( 'The user not exist.', "mobile-builder" ),
					array(
						'status' => 403,
					)
				);
			}

			$phone_number = $result->users[0]->phoneNumber;

			$users = get_users( array(
				"meta_key"     => "digits_phone",
				"meta_value"   => $phone_number,
				"meta_compare" => "="
			) );

			if ( count( $users ) == 0 ) {
				$error = new WP_Error();
				$error->add( 403, __( "Did not find any members matching the phone number!", "mobile-builder" ), array( 'status' => 400 ) );

				return $error;
			}

			$user = $users[0];

			// Generate token
			$token = $this->generate_token( $user );

			// Return data
			$data = array(
				'token' => $token,
				'user'  => $this->mbd_get_userdata( $user ),
			);

			return $data;

		} catch ( Exception $err ) {
			return $err;
		}
	}

	/**
	 *  General token
	 *
	 * @param $user
	 *
	 * @return string
	 */
	public function generate_token( $user, $data = array() ) {
		$iat = time();
		$nbf = $iat;
		$exp = $iat + ( DAY_IN_SECONDS * 30 );

		$token = array(
			'iss'  => get_bloginfo( 'url' ),
			'iat'  => $iat,
			'nbf'  => $nbf,
			'exp'  => $exp,
			'data' => array_merge( array(
				'user_id' => $user->data->ID
			), $data ),
		);

		// Generate token
		return JWT::encode( $token, $this->key );
	}

	public function determine_current_user( $user ) {
		// Run only on REST API
		if ( ! mobile_builder_is_rest_api_request() ) {
			return $user;
		}

		$token = mobile_builder_token();

		if ( $token ) {
			$user_decode = $this->decode( $token );

			if ( is_wp_error( $user_decode ) ) {
				return $user;
			}

			return $user_decode->data->user_id;
		}

		return $user;
	}

	/**
	 * Decode token
	 * @return array|WP_Error
	 */
	public function decode( $token = '' ) {
		/*
		 * Get token on header
		 */

		if ( ! $token ) {

			$token = mobile_builder_token();

			if ( ! $token ) {
				return new WP_Error(
					'no_auth_header',
					__( 'Authorization header not found.', "mobile-builder" ),
					array(
						'status' => 403,
					)
				);
			}
		}

		$match = preg_match( '/Bearer\s(\S+)/', $token, $matches );

		if ( ! $match ) {
			return new WP_Error(
				'token_not_validate',
				__( 'Token not validate format.', "mobile-builder" ),
				array(
					'status' => 403,
				)
			);
		}

		$token = $matches[1];

		/** decode token */
		try {
			$data = JWT::decode( $token, $this->key, array( 'HS256' ) );

			if ( $data->iss != get_bloginfo( 'url' ) ) {
				return new WP_Error(
					'bad_iss',
					__( 'The iss do not match with this server', "mobile-builder" ),
					array(
						'status' => 403,
					)
				);
			}
			if ( ! isset( $data->data->user_id ) ) {
				return new WP_Error(
					'id_not_found',
					__( 'User ID not found in the token', "mobile-builder" ),
					array(
						'status' => 403,
					)
				);
			}

			return $data;

		} catch ( Exception $e ) {
			return new WP_Error(
				'invalid_token',
				$e->getMessage(),
				array(
					'status' => 403,
				)
			);
		}
	}

	public function get_featured_media_url( $object, $field_name, $request ) {
		$featured_media_url = '';
		$image_attributes   = wp_get_attachment_image_src(
			get_post_thumbnail_id( $object['id'] ),
			'full'
		);
		if ( is_array( $image_attributes ) && isset( $image_attributes[0] ) ) {
			$featured_media_url = (string) $image_attributes[0];
		}

		return $featured_media_url;
	}

	/**
	 * Get categories by parent
	 *
	 * @param $request
	 *
	 * @return array
	 * @since 1.3.4
	 * @author ngocdt
	 */
	function categories( $request ) {
		$parent = $request->get_param( 'parent' );

		$result = wp_cache_get( 'category_' . $parent, 'rnlab' );

		if ( $result ) {
			return $result;
		}

		$result = $this->get_category_by_parent_id( $parent );
		wp_cache_set( 'category_' . $parent, $result, 'rnlab' );

		return $result;
	}

	function get_category_by_parent_id( $parent ) {
		$args = array(
			'hierarchical'     => 1,
			'show_option_none' => '',
			'hide_empty'       => 0,
			'parent'           => $parent,
			'taxonomy'         => 'product_cat',
		);

		$categories = get_categories( $args );

		if ( count( $categories ) ) {
			$with_subs = [];
			foreach ( $categories as $category ) {

				$image = null;

				// Get category image.
				$image_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
				if ( $image_id ) {
					$attachment = get_post( $image_id );

					$image = array(
						'id'   => (int) $image_id,
						'src'  => wp_get_attachment_url( $image_id ),
						'name' => get_the_title( $attachment ),
						'alt'  => get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
					);
				}

				$with_subs[] = array(
					'id'         => (int) $category->term_id,
					'name'       => $category->name,
					'parent'     => $category->parent,
					'categories' => $this->get_category_by_parent_id( (int) $category->term_id ),
					'image'      => $image,
					'count'      => (int) $category->count
				);
			}

			return $with_subs;

		} else {
			return [];
		}
	}

	/**
	 * Check if a given request has access to read a customer.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|boolean
	 *
	 * @since 1.3.4
	 */
	public function update_item_permissions_check( $request ) {
		$id = (int) $request['id'];

		if ( get_current_user_id() != $id ) {
			return new WP_Error( 'mobile_builder', __( 'Sorry, you cannot change info.', "mobile-builder" ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * @param $user
	 *
	 * Add more info to user data response
	 *
	 * @return mixed
	 * @since 1.3.7
	 *
	 */
	public function mbd_get_userdata( $user ) {

		$user_data             = $user->data;
		$user_data->first_name = $user->first_name;
		$user_data->last_name  = $user->last_name;
		$user_data->avatar     = 'https://www.gravatar.com/avatar/' . md5( $user_data->user_email );
		$user_data->location   = get_user_meta( $user->ID, 'mbd_location', true );
		$user_data->roles      = $user->roles;

		return $user_data;
	}
}
