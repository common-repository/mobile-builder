<?php

/**
 * The product-facing functionality of the plugin.
 *
 * @link       https://rnlab.io
 * @since      1.0.0
 *
 * @package    Mobile_Builder
 * @subpackage Mobile_Builder/product
 */

/**
 * The product-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the product-facing stylesheet and JavaScript.
 *
 * @package    Mobile_Builder
 * @subpackage Mobile_Builder/product
 * @author     RNLAB <ngocdt@rnlab.io>
 */
class Mobile_Builder_Product {

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
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Registers a REST API route
	 *
	 * @since 1.0.0
	 */
	public function add_api_routes() {
		$namespace = $this->plugin_name . '/v' . intval( $this->version );

		$products = new WC_REST_Products_Controller();

		register_rest_route( $namespace, 'rating-count', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'rating_count' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( 'wc/v3', 'products-distance', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_items' ),
			'permission_callback' => array( $products, 'get_items_permissions_check' ),
		) );

		register_rest_route( $namespace, 'attributes/(?P<product_id>[a-zA-Z0-9-]+)', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_all_product_attributes' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $namespace, 'variations/(?P<product_id>[a-zA-Z0-9-]+)', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_all_product_variations' ),
			'permission_callback' => '__return_true',
		) );

	}

	/**
	 *
	 * Get list products variable
	 *
	 * @param $request
	 *
	 * @return array|WP_Error|WP_REST_Response
	 */
	function get_all_product_attributes( $request ) {
		// Get product id from request
		$product_id = $request->get_param( 'product_id' );

		// Get all attribute
		$attributes = wc_get_attribute_taxonomies();

		// Get variation product info
		$variation_product = new WC_Product_Variable( $product_id );

		// Product variation attributes
		$variation_attributes = $variation_product->get_variation_attributes();

		// Init product attribute
		$product_attributes = array();

		foreach ( $attributes as $key => $attribute ) {


			$slug = wc_attribute_taxonomy_name( $attribute->attribute_name );

			if ( isset( $variation_attributes[ $slug ] ) ) {

				$attr = new stdClass();

				$attr->id           = (int) $attribute->attribute_id;
				$attr->name         = $attribute->attribute_label;
				$attr->slug         = wc_attribute_taxonomy_name( $attribute->attribute_name );
				$attr->type         = $attribute->attribute_type;
				$attr->order_by     = $attribute->attribute_orderby;
				$attr->has_archives = (bool) $attribute->attribute_public;

				$option_term = array();
				foreach ( $variation_attributes[ $slug ] as $value ) {
					$term = get_term_by( 'slug', $value, $slug );

					// Type color
					if ( $term && $attribute->attribute_type == 'color' ) {
						$term->value = sanitize_hex_color( get_term_meta( $term->term_id, 'product_attribute_color', true ) );
					}

					// Type image
					if ( $term && $attribute->attribute_type == 'image' ) {
						$attachment_id = absint( get_term_meta( $term->term_id, 'product_attribute_image', true ) );
						$image_size    = function_exists( 'woo_variation_swatches' ) ? woo_variation_swatches()->get_option( 'attribute_image_size' ) : 'thumbnail';
						$term->value   = wp_get_attachment_image_url( $attachment_id, apply_filters( 'wvs_product_attribute_image_size', $image_size ) );
					}

					$option_term[] = $term ? $term : $value;
				}

				$attr->options = $option_term;
				$attr->terms   = $option_term;

				$product_attributes[] = $attr;
			}
		}

		return $product_attributes;
	}


	function get_all_product_variations( $request ) {
		$product_id = $request->get_param('product_id');
		$handle = new WC_Product_Variable($product_id);
		$variation_attributes = $handle->get_variation_attributes();
		$variation_attributes_data = array();
		$variation_attributes_label = array();
		foreach($variation_attributes as $key => $attribute){
			$variation_attributes_result['attribute_' . sanitize_title( $key )] = $attribute;
			$variation_attributes_label['attribute_' . sanitize_title( $key )] = $key;
		}
		return array(
			'variation_attributes_label' => $variation_attributes_label,
			'variation_attributes'=> $variation_attributes_result,
			'available_variations' => $handle->get_available_variations()
		);
	}

	/**
	 *
	 * Get products items
	 *
	 * @param $request
	 *
	 * @return array|WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		global $wpdb;


		$lat = $request->get_param( 'lat' );
		$lng = $request->get_param( 'lng' );

		$productsClass = new WC_REST_Products_Controller();
		$response      = $productsClass->get_items( $request );

		if ( $lat && $lng ) {

			$ids = array();
			foreach ( $response->data as $key => $value ) {
				$ids[] = $value['id'];
			}

			// Get all locations
			$table_name    = $wpdb->prefix . 'gmw_locations';
			$query         = "SELECT * FROM $table_name WHERE object_id IN (" . implode( ',', $ids ) . ")";
			$gmw_locations = $wpdb->get_results( $query, OBJECT );

			// Calculator the distance
			$origins = [];
			foreach ( $gmw_locations as $key => $value ) {
				$origins[] = $value->latitude . ',' . $value->longitude;
			}

			$origin_string       = implode( '|', $origins );
			$destinations_string = "$lat,$lng";
			$key                 = MOBILE_BUILDER_GOOGLE_API_KEY;

			$distance_matrix = mobile_builder_distance_matrix( $origin_string, $destinations_string, $key );

			// map distance matrix to product
			$data = [];
			foreach ( $response->data as $key => $item ) {
				$index                   = array_search( $item['id'], array_column( $gmw_locations, 'object_id' ) );
				$item['distance_matrix'] = $distance_matrix[ $index ];
				$data[]                  = $item;
			}

			$response->data = $data;
		}

		return $response;
	}

	/**
	 * Force currency for mobile checkout
	 *
	 * @since 1.2.0
	 */
	public function mbd_wcml_client_currency( $client_currency ) {
		if ( isset( $_GET['mobile'] ) && $_GET['mobile'] == 1 && isset( $_GET['currency'] ) ) {
			$client_currency = $_GET['currency'];
		}

		return $client_currency;
	}

	/**
	 * @param $request
	 *
	 * @return array|bool|mixed|WP_Error
	 * @since    1.0.0
	 */
	public function rating_count( $request ) {
		$product_id = $request->get_param( 'product_id' );

		$result = wp_cache_get( 'rating_count' . $product_id, 'rnlab' );

		if ( $result ) {
			return $result;
		}

		if ( $product_id ) {
			$product = new WC_Product( $product_id );

			$result = array(
				"5" => $product->get_rating_count( 5 ),
				"4" => $product->get_rating_count( 4 ),
				"3" => $product->get_rating_count( 3 ),
				"2" => $product->get_rating_count( 2 ),
				"1" => $product->get_rating_count( 1 ),
			);

			wp_cache_set( 'rating_count' . $product_id, $result, 'rnlab' );

			return $result;
		}

		return new WP_Error(
			"product_id",
			__( "Product ID not provider.", "mobile-builder" ),
			array(
				'status' => 403,
			)
		);

	}

	/**
	 * @param $response
	 *
	 * @return mixed
	 * @since    1.0.0
	 */
	public function custom_change_product_response( $response, $object, $request ) {

//		echo $request->get_param('lng');
//		echo $request->get_param('lat'); die;

		$type = $response->data['type'];

		if ( $type == 'variable' ) {
			$price_min                   = $object->get_variation_price();
			$price_max                   = $object->get_variation_price( 'max' );
			$response->data['price_min'] = $price_min;
			$response->data['price_max'] = $price_max;
		}

		global $woocommerce_wpml;
		if ( ! empty( $woocommerce_wpml->multi_currency ) && ! empty( $woocommerce_wpml->settings['currencies_order'] ) ) {

			$price = $response->data['price'];

			if ( $type == 'grouped' || $type == 'variable' ) {

				foreach ( $woocommerce_wpml->settings['currencies_order'] as $currency ) {

					if ( $currency != get_option( 'woocommerce_currency' ) ) {
						$response->data['from-multi-currency-prices'][ $currency ]['price'] = $woocommerce_wpml->multi_currency->prices->raw_price_filter( $price,
							$currency );
					}
				}
			}
		}

		return $response;
	}

	/**
	 * @param $response
	 *
	 * @return mixed
	 * @since    1.0.0
	 */
	public function custom_change_product_cat( $response ) {
		$response->data['name'] = wp_specialchars_decode( $response->data['name'] );

		return $response;
	}

	/**
	 * @param $title
	 *
	 * @return string
	 * @since    1.0.0
	 */
	public function custom_the_title( $title ) {
		return wp_specialchars_decode( $title );
	}
}
