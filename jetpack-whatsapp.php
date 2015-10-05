<?php
/*
 * Plugin Name: WhatsApp Sharing Button for Jetpack
 * Plugin URI: http://valeriosouza.com.br/portfolio/whatsapp-sharing-button-for-jetpack/?utm_source=plugin&utm_medium=plugin-url&utm_campaign=jetpack-whatsapp
 * Description: Add WhatsApp button to Jetpack Sharing
 * Version: 1.1.2
 * Author: Valerio Souza, WordLab Academy
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

add_action( 'admin_init', 'jw_check_dependencies' );

//Check if jetpack is active.
function jw_check_dependencies() {
	if ( ! is_plugin_active( 'jetpack/jetpack.php' ) ) {
		add_action( 'admin_notices', 'jw_dependencies_notice' );
		deactivate_plugins( __FILE__ );

		//I used it to not appear "plugin active" message! We can discuss a better way to do this.
		unset( $_GET['activate'] );
	}
}

//Show error notice if jetpack is NOT active.
function jw_dependencies_notice() {
    ?>
    <div class="error">
        <p><strong><?php _e( 'Jetpack has NOT been activated! You need to install and activate the Jetpack plugin to work sharing with WhatsApp.', 'jetpack-whatsapp' ); ?></strong></p>
    </div>
    <?php
}

define( 'jetwhats__PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'jetwhats__PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'jetwhats__PLUGIN_FILE', __FILE__ );
define( 'jetwhats__VERSION',     '1.1.2' );

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
		add_action( 'admin_notices', 		 array( &$this, 'plugin_donate_notice' ) );
		add_action( 'admin_init', 			 array( &$this, 'plugin_donate_ignore' ) );

		if( did_action('plugins_loaded') ) {
			$this->require_services();
		} else {
			add_action( 'plugins_loaded', array( &$this, 'require_services' ) );
		}
		add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_donate' ), 10, 2 );
		add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_more' ), 10, 2 );
	}

	function register_assets() {
		if( get_option('sharedaddy_disable_resources') ) {
			return;
		}

		if( !Jetpack::is_module_active('sharedaddy') ) {
			return;
		}
		wp_enqueue_script( 'jetpack-whatsapp', jetwhats__PLUGIN_URL . 'assets/js/main.js', array('jquery','sharing-js'), jetwhats__VERSION, true );
		wp_enqueue_style( 'jetpack-whatsapp', jetwhats__PLUGIN_URL . 'assets/css/style.css', array(), jetwhats__VERSION );
	}

	function admin_menu_assets( $hook ) {
		if( $hook == 'settings_page_sharing' ) {
			wp_enqueue_style( 'jetpack-whatsapp', jetwhats__PLUGIN_URL . 'assets/css/style.css', array('sharing', 'sharing-admin'), jetwhats__VERSION );
		}
	}

	function require_services() {
		if( class_exists('Jetpack') ) {
			require_once( jetwhats__PLUGIN_DIR . 'includes/class.whatsapp-service.php' );
		}
	}

	static function plugin_textdomain() {
		$locale = get_locale();

		load_plugin_textdomain( 'jetpack-whatsapp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	function plugin_row_donate( $links, $file ) {
		if( plugin_basename( jetwhats__PLUGIN_FILE ) === $file ) {
			$links[] = sprintf(
				'<a target="_blank" href="%s" style="color:#ffa100;font-weight:bold;">%s</a>',
				esc_url('http://wordlab.com.br/donate/?utm_source=plugin&utm_medium=donate&utm_campaign=jetpack-whatsapp'),
				__( 'Donate', 'jetpack-whatsapp' )
			);
		}
		return $links;
	}

	function plugin_row_more( $links, $file ) {
		if( plugin_basename( jetwhats__PLUGIN_FILE ) === $file ) {
			$links[] = sprintf(
				'<a target="_blank" href="%s" style="color:#ffa100;font-weight:bold;">%s</a>',
				esc_url('http://valeriosouza.com.br/project-tag/jetpack-addons/?utm_source=plugin&utm_medium=more-addons&utm_campaign=jetpack-whatsapp'),
				__( 'More add-ons', 'jetpack-whatsapp' )
			);
		}
		return $links;
	}

	/* Display a notice that can be dismissed */

	function plugin_donate_notice() {
		global $current_user ;
	        $user_id = $current_user->ID;
	        /* Check that the user hasn't already clicked to ignore the message */
		if ( ! get_user_meta($user_id, 'whatsapp_ignore_notice') ) {
	        echo '<div class="updated"><p>';
	        printf(__('Like the plugin to share the WhatsApp Sharing Button for Jetpack? Develop free plugins takes work! Be my boss and make a <a target="_blank" href="%1$s">donation of any amount</a>. | <a href="%2$s">Hide Notice</a>'), 'http://wordlab.com.br/donate/?utm_source=plugin&utm_medium=donate-notice&utm_campaign=jetpack-whatsapp', '?whatsapp_nag_ignore=0');
	        echo "</p></div>";
		}
	}

	function plugin_donate_ignore() {
		global $current_user;
	        $user_id = $current_user->ID;
	        /* If user clicks to ignore the notice, add that to their user meta */
	        if ( isset($_GET['whatsapp_nag_ignore']) && '0' == $_GET['whatsapp_nag_ignore'] ) {
	             add_user_meta($user_id, 'whatsapp_ignore_notice', 'true', true);
		}
	}

}
