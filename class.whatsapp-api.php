<?php

$instance[] = Feedly_API::init();
$instance[] = Google_API::init();

abstract class jetwhats_API {
	const OPTION_NAME_ACTIVATED = 'jetwhats-api_activated';
	const API_ENDPOINT = 'jetwhats-api';

	static function init() {
		if( !static::$instance ) {
			$name = get_called_class();
			static::$instance = new $name();
		}
		return static::$instance;
	}

	function __construct() {
		add_filter( 'force_ssl',         array( &$this, 'force_ssl' ),     10, 3 );
		add_action( 'init',              array( &$this, 'add_rewrite_endpoint' ) );
		add_action( 'delete_option',     array( $this,  'delete_option' ), 10, 1 );
		add_filter( 'query_vars',        array( $this,  'query_vars' ) );
		add_action( 'template_redirect', array( $this,  'template_redirect' ) );
	}

	function force_ssl( $force_ssl, $post_id = 0, $url = '' ) {
		global $wp_query;
		if( is_object( $wp_query ) && isset( $wp_query->query[ static::API_ENDPOINT ] ) && $url == set_url_scheme( $url, 'https' ) ) {
			$force_ssl = true;
		}
		return $force_ssl;
	}

	static function activation(){
		update_option( static::OPTION_NAME_ACTIVATED, true );
		flush_rewrite_rules();
	}

	static function deactivation(){
		delete_option( static::OPTION_NAME_ACTIVATED, true );
		flush_rewrite_rules();
	}
	public function delete_option( $option ){
		if( 'rewrite_rules' === $option && get_option( static::OPTION_NAME_ACTIVATED ) ) { 
			$this->add_rewrite_endpoint();
		}
	}

	public function add_rewrite_endpoint() {
		add_rewrite_endpoint( static::API_ENDPOINT, EP_ROOT );
		add_rewrite_endpoint( Google_API::API_ENDPOINT, EP_ROOT );
	}

	public function query_vars( $vars ) {
		$vars[] = static::API_ENDPOINT;
		return $vars;
	}

	abstract function template_redirect();
}

class Feedly_API extends jetwhats_API {
	static $instance;

	const API_ENDPOINT = 'feedly-api';

	public function template_redirect() {
		global $wp_query;
		if( is_object( $wp_query ) && isset( $wp_query->query[ self::API_ENDPOINT ] ) ) {
			$feed_url       = get_bloginfo('rss2_url');
			$feedly_url     = 'https://cloud.feedly.com/v3/feeds/' . rawurlencode( 'feed/' . $feed_url );
			$transient_name = 'jetwhats-feedly-api_' . hash( 'crc32b', $feedly_url );

			if( ( $response = get_transient( $transient_name ) ) === false ) {
				$response = wp_remote_get( $feedly_url, array( 'httpversion' => '1.1' ) );
				$status   = wp_remote_retrieve_response_code( $response );

				if( !is_wp_error( $response ) && $status == 200 ) {
					set_transient( $transient_name, $response, HOUR_IN_SECONDS );
				}
			} else {
				$status = wp_remote_retrieve_response_code( $response );
			}

			$body = json_decode( wp_remote_retrieve_body( $response ) );

			nocache_headers();
			header('Content-Type: application/javascript; charset=UTF-8');

			$callback = 'update_feedly_count';
			if( !empty( $_GET['callback'] ) ) {
				$callback = esc_js( $_GET['callback'] );
			}

			if( !empty( $_GET['url'] ) ) {
				$body->{'url'} = esc_js( $_GET['url'] );
			}

			echo $callback . '(';
			if( !is_wp_error( $response ) && $status == 200 ) {
				echo json_encode( $body, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE );
			} else {
				status_header( $status );
				echo json_encode( array(
					'meta' => array(
						'code'    => $status,
						'message' => wp_remote_retrieve_response_message( $response ),
					),
				) );
			}
			echo ');';
			exit;
		}
	}
}

class Google_API extends jetwhats_API {
	static $instance;

	const API_ENDPOINT = 'google-api';

	public function template_redirect() {
		global $wp_query;

		if( is_object( $wp_query ) && isset( $wp_query->query[ self::API_ENDPOINT ] ) ) {
			if( !empty( $_GET['url'] ) ) {
				$url = $_GET['url'];
			} else {
				$url = home_url();
			}

			$transient_name = 'jetwhats-google-api_' . hash( 'crc32b', $url );

			if( ( $response = get_transient( $transient_name ) ) === false ) {
				$response = wp_remote_post( 'https://clients6.google.com/rpc', array(
					'httpversion' => '1.1',
					'body'        => '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . $url . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]',
					'headers'     => array( 'Content-Type' => 'application/json' ),
				) );
				$status   = wp_remote_retrieve_response_code( $response );

				if( !is_wp_error( $response ) && $status == 200 ) {
					set_transient( $transient_name, $response, HOUR_IN_SECONDS );
				}
			} else {
				$status = wp_remote_retrieve_response_code( $response );
			}

			$result = json_decode( wp_remote_retrieve_body( $response ), true );
			$result = $result[0];

			if( isset( $result['error'] ) || !isset( $result['result']['metadata']['globalCounts']['count'] ) ) {
				$count = 0;
			} else {
				$count = intval( $result['result']['metadata']['globalCounts']['count'] );
			}

			$data = array(
				'url'   => $url,
				'count' => $count,
			);

			nocache_headers();
			header('Content-Type: application/javascript; charset=UTF-8');

			$callback = 'update_google_count';
			if( !empty( $_GET['callback'] ) ) {
				$callback = esc_js( $_GET['callback'] );
			}

			echo $callback . '(';
			if( !is_wp_error( $response ) && $status == 200 ) {
				echo json_encode( $data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE );
			} else {
				status_header( $status );
				echo json_encode( array(
					'meta' => array(
						'code'    => $status,
						'message' => wp_remote_retrieve_response_message( $response ),
					),
				) );
			}
			echo ');';
			exit;
		}
	}
}
