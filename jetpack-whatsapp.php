<?php
/*
 * Plugin Name: Jetpack WhatsApp
 * Plugin URI: http://wordpress.org/plugins/whatsapp-jetpack-button/
 * Description: Add WhatsApp button to Jetpack Sharing
 * Version: 1.0.0
 * Author: Valerio Souza
 * Author URI: http://www.valeriosouza.com.br
 * License: GPLv3 or later
 * Text Domain: jetpack-whatsapp
 * Domain Path: /languages/
 * GitHub Branch: beta
 * GitHub Plugin URI: https://github.com/valeriosouza/jetpack-whatsapp
*/

if( !function_exists('add_action') ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

if( version_compare( get_bloginfo('version'), '3.8', '<' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	deactivate_plugins( __FILE__ );
}

define( 'jetwhats__PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'jetwhats__PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'jetwhats__PLUGIN_FILE', __FILE__ );
define( 'jetwhats__VERSION',     '0.1.0' );

add_action( 'init', array( 'Jetpack_Whatsapp_Pack', 'init' ) );

class Jetpack_Whatsapp_Pack {
	static $instance;

	
	private $data;

	static function init() {
		if( !self::$instance ) {
			if( did_action('plugins_loaded') ) {
				self::plugin_textdomain();
			} else {
				add_action( 'plugins_loaded', array( __CLASS__, 'plugin_textdomain' ) );
			}

			self::$instance = new Jetpack_Whatsapp_Pack;
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts',    array( &$this, 'register_assets' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_menu_assets' ) );

		if( did_action('plugins_loaded') ) {
			$this->require_services();
		} else {
			add_action( 'plugins_loaded', array( &$this, 'require_services' ) );
		}
		add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );
	}

	function register_assets() {
		if( get_option('sharedaddy_disable_resources') ) {
			return;
		}

		if( !Jetpack::is_module_active('sharedaddy') ) {
			return;
		}
		wp_enqueue_script( 'jetpack-whatsapp', jetwhats__PLUGIN_URL . 'count.js', array('jquery','sharing-js'), jetwhats__VERSION, true );
		wp_enqueue_style( 'jetpack-whatsapp', jetwhats__PLUGIN_URL . 'style.css', array(), jetwhats__VERSION );
	}

	function admin_menu_assets( $hook ) {
		if( $hook == 'settings_page_sharing' ) {
			wp_enqueue_style( 'jetpack-whatsapp', jetwhats__PLUGIN_URL . 'style.css', array('sharing', 'sharing-admin'), jetwhats__VERSION );
		}
	}

	function require_services() {
		if( class_exists('Jetpack') ) {
			require_once( jetwhats__PLUGIN_DIR . 'class.whatsapp-service.php' );
		}
	}

	static function plugin_textdomain() {
		//load_plugin_textdomain( 'jetpack-whatsapp', false, dirname( plugin_basename( jetwhats__PLUGIN_FILE ) ) . '/languages/' );
		$locale = get_locale();

		load_plugin_textdomain( 'jetpack-whatsapp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	function plugin_row_meta( $links, $file ) {
		if( plugin_basename( jetwhats__PLUGIN_FILE ) === $file ) {
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url('https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=P5QTGDB64SU8E&lc=US&item_name=WordPress%20Plugins&no_note=0&cn=Adicionar%20instru%c3%a7%c3%b5es%20especiais%20para%20o%20vendedor%3a&no_shipping=1&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted'),
				__( 'Donate', 'jetpack-whatsapp' )
			);
		}
		return $links;
	}
}
