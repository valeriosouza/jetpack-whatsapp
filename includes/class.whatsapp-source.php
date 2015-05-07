<?php

if( !function_exists('add_action') ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}
class jetwhats_Share_WhatsApp extends Sharing_Source {
	var $shortname = 'whatsapp';

	function __construct( $id, array $settings ) {
		parent::__construct( $id, $settings );

		if( 'official' == $this->button_style )
			$this->smart = true;
		else
			$this->smart = false;
	}

	function get_name() {
		return __( 'WhatsApp', 'jetpack-whatsapp' );
	}

	function has_custom_button_style() {
		return $this->smart;
	}

	private function guess_locale_from_lang( $lang ) {
		if( strpos( $lang, 'ja' ) === 0 )
			return 'ja';

		if( strpos( $lang, 'zh' ) === 0 )
			return 'zh-hant';

		return 'en';
	}

	function get_display( $post ) {
		include_once jetwhats__PLUGIN_DIR . 'includes/class.mobile.php';
		$locale = $this->guess_locale_from_lang( get_locale() );
		if ( wp_is_mobile() and $iOS or $Android ) {
			if( $this->smart )
				return sprintf(
					'<div class="whatsapp_button"><a href="whatsapp://send?text=%s:%20%s%20-%20%s" class="share-whatsapp %s" title="%s"></a></div>',
					__('Read this','jetpack-whatsapp'),
					rawurlencode( $this->get_share_title( $post->ID ) ),
					rawurlencode( $this->get_share_url( $post->ID ) ),
					esc_attr( $locale ),
					esc_attr__( 'WhatsApp it!', 'jetpack-whatsapp' )
				);
			else
				return $this->get_link( get_permalink( $post->ID ), _x( 'WhatsApp', 'share to', 'jetpack-whatsapp' ), __( 'Click to share on WhatsApp', 'jetpack-whatsapp' ), 'share=whatsapp' );
		}
	}

	function display_header() {
	}

	function display_footer() {
		$this->js_dialog( $this->shortname );
	}

	function process_request( $post, array $post_data ) {
		$whatsapp_url = 'whatsapp://send?text='.rawurlencode(__('Read this','jetpack-whatsapp').': '.$this->get_share_title( $post->ID ).' - '.$this->get_share_url( $post->ID ) ).'';

		// Record stats
		parent::process_request( $post, $post_data );

		// Redirect to WhatsApp
		wp_redirect( $whatsapp_url );
		die();
	}
}
