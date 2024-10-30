<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Mobile_Builder
 * @subpackage Mobile_Builder/admin
 * @author     RNLAB <ngocdt@rnlab.io>
 */
class Mobile_Builder_Admin {

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
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mobile_Builder_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mobile_Builder_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, 'https://cdnjs.rnlab.io/' . $this->version . '/static/css/main.css', array(), $this->version,
			'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Auth_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Auth_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$namespace = $this->plugin_name . '/v' . intval( $this->version );

		wp_enqueue_media();

		wp_enqueue_script( $this->plugin_name, 'https://cdnjs.rnlab.io/' . $this->version . '/static/js/main.js', array(
			'jquery',
			'media-upload'
		), $this->version, true );

		$license = maybe_unserialize( get_option( 'mobile_builder_license' ) );

		wp_localize_script( $this->plugin_name, 'wp_rnlab_configs', array(
				'api_nonce'   => wp_create_nonce( 'wp_rest' ),
				'api_url'     => rest_url( '' ),
				'plugin_name' => $this->plugin_name,
				'vendor'      => 'wcfm',
				'app'         => isset( $license['app'] ) ? $license['app'] : '',
				'license'     => isset( $license['license'] ) ? $license['license'] : '',
			)
		);

	}

	/**
	 * Registers a REST API route
	 * @since 1.0.0
	 */
	public function add_api_routes() {
		$namespace        = $this->plugin_name . '/v' . intval( $this->version );
		$endpoint         = 'template-mobile';
		$endpoint_configs = 'configs';

		register_rest_route( $namespace, $endpoint, array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_template_config' ),
				'permission_callback' => '__return_true',
			),
		) );

		register_rest_route( $namespace, $endpoint, array(
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'add_template_config' ),
				'permission_callback' => array( $this, 'admin_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( $namespace, $endpoint, array(
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_template_config' ),
				'permission_callback' => array( $this, 'admin_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( $namespace, $endpoint, array(
			array(
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_template_config' ),
				'permission_callback' => array( $this, 'admin_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( $namespace, $endpoint_configs, array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_configs' ),
				'permission_callback' => '__return_true',
			),
		) );

		register_rest_route( $namespace, $endpoint_configs, array(
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_configs' ),
				'permission_callback' => array( $this, 'admin_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( $namespace, 'license', array(
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'add_license' ),
				'permission_callback' => array( $this, 'admin_permissions_check' ),
				'args'                => array(),
			),
		) );

	}

	/**
	 * @return array|object|null
	 * @since 1.0.0
	 */
	public function template_configs() {
		global $wpdb;
		$table_name = $wpdb->prefix . MOBILE_BUILDER_TABLE_NAME;

		return $wpdb->get_results( "SELECT * FROM $table_name", OBJECT );
	}

	/**
	 * @param $request
	 *
	 * @return WP_REST_Response
	 * @since    1.0.0
	 */
	public function get_template_config( $request ) {
		return new WP_REST_Response( $this->template_configs(), 200 );
	}

	/**
	 * @param $request
	 *
	 * @return WP_REST_Response
	 * @since    1.0.0
	 */
	public function add_template_config( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . MOBILE_BUILDER_TABLE_NAME;

		$data = $request->get_param( 'data' );

		$results = $wpdb->insert(
			$table_name,
			$data
		);

		return new WP_REST_Response( $results, 200 );
	}

	/**
	 * @param $request
	 *
	 * @return WP_REST_Response
	 * @since    1.0.0
	 */
	public function update_template_config( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . MOBILE_BUILDER_TABLE_NAME;

		$data  = $request->get_param( 'data' );
		$where = $request->get_param( 'where' );

		$results = $wpdb->update(
			$table_name,
			$data,
			$where
		);

		return new WP_REST_Response( $results, 200 );
	}

	/**
	 * @param $request
	 *
	 * @return WP_REST_Response
	 * @since    1.0.0
	 */
	public function delete_template_config( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . MOBILE_BUILDER_TABLE_NAME;

		$where = $request->get_param( 'where' );

		$results = $wpdb->delete(
			$table_name,
			$where
		);

		return new WP_REST_Response( $results, 200 );
	}

	/**
	 * @param $request
	 *
	 * @return WP_REST_Response
	 * @since    1.0.0
	 */
	public function get_configs( $request ) {

		$configs = get_option( 'mobile_builder_configs', array(
			"requireLogin"       => false,
			"toggleSidebar"      => false,
			"isBeforeNewProduct" => 5
		) );

		return new WP_REST_Response( maybe_unserialize( $configs ), 200 );
	}

	/**
	 * @param $request
	 *
	 * @return WP_REST_Response
	 * @since    1.0.0
	 */
	public function update_configs( $request ) {

		$data = $request->get_param( 'data' );

		if ( get_option( 'mobile_builder_configs' ) ) {
			$status = update_option( 'mobile_builder_configs', maybe_serialize( $data ) );
		} else {
			$status = add_option( 'mobile_builder_configs', maybe_serialize( $data ) );
		}

		return new WP_REST_Response( array( 'status' => $status ), 200 );
	}

	/**
	 *
	 * Add license code
	 *
	 * @param $request
	 *
	 * @return WP_REST_Response
	 * @since    1.0.0
	 */
	public function add_license( $request ) {

		$license = $request->get_param( 'data' );

		delete_option( 'mobile_builder_license' );
		$status = add_option( 'mobile_builder_license', maybe_serialize( $license ) );

		return new WP_REST_Response( array( 'status' => $status ), 200 );
	}

	/**
	 * @param $request
	 *
	 * @return mixed
	 * @since    1.0.0
	 */
	public function admin_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		/*
		 * Add a settings page for this plugin to the Settings menu.
		 */
		$hook_suffix = add_options_page(
			__( 'Mobile Builder', $this->plugin_name ),
			__( 'Mobile Builder', $this->plugin_name ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_admin_page' )
		);

		$hook_suffix = add_menu_page(
			__( 'Mobile Builder', $this->plugin_name ),
			__( 'Mobile Builder', $this->plugin_name ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_admin_page' ),
			'dashicons-excerpt-view'
		);

		// Load enqueue styles and script
		add_action( "admin_print_styles-$hook_suffix", array( $this, 'enqueue_styles' ) );
		add_action( "admin_print_scripts-$hook_suffix", array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		?>
        <div id="wp-rnlab"></div><?php
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_action_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __( 'Settings', $this->plugin_name ) . '</a>',
			),
			$links
		);
	}

}
