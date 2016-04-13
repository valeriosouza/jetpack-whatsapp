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
		return __( 'WhatsApp', 'whatsapp-jetpack-button' );
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
		$locale = $this->guess_locale_from_lang( get_locale() );
			if( $this->smart )
				if ( get_option( 'whatsapp_jetpack_button_pro_active' ) ) {
					return sprintf(
						'<div class="whatsapp_button"><a href="whatsapp://send?text=%s: %s - %s%s" class="share-whatsapp %s" title="%s"></a></div>',
						__('Look at this','whatsapp-jetpack-button'),
						rawurlencode( $this->get_share_title( $post->ID ) ),
						rawurlencode( $this->get_share_url( $post->ID ) ),
						rawurlencode( '?utm_source=jetpack-sharing&utm_medium=whatsapp&utm_campaign=mobile' ),
						esc_attr( $locale ),
						esc_attr__( 'WhatsApp it!', 'whatsapp-jetpack-button' )
					);
				} else {
					return sprintf(
						'<div class="whatsapp_button"><a href="whatsapp://send?text=%s: %s - %s" class="share-whatsapp %s" title="%s"></a></div>',
						__('Look at this','whatsapp-jetpack-button'),
						rawurlencode( $this->get_share_title( $post->ID ) ),
						rawurlencode( $this->get_share_url( $post->ID ) ),
						esc_attr( $locale ),
						esc_attr__( 'WhatsApp it!', 'whatsapp-jetpack-button' )
					);	
				}
			else
				return $this->get_link( get_permalink( $post->ID ), _x( 'WhatsApp', 'share to', 'whatsapp-jetpack-button' ), __( 'Click to share on WhatsApp', 'whatsapp-jetpack-button' ), 'share=whatsapp' );
	}

	function display_header() {
	}

	function display_footer() {
		$this->js_dialog( $this->shortname );
	}

	function process_request( $post, array $post_data ) {
		if ( get_option( 'whatsapp_jetpack_button_pro_active' ) ) {
			$url = add_query_arg( array(
			    'utm_source' => 'jetpack-sharing',
			    'utm_medium' => 'whatsapp',
			    'utm_campaign' => 'mobile'
			), $this->get_share_url( $post->ID ) );
		} else {
			$url = $this->get_share_url( $post->ID );
		}

		$params = array(
		    'text' => __( 'Look at this', 'whatsapp-jetpack-button' ) . ': ' . $this->get_share_title( $post->ID ).' - '.$url,
		);

		$whatsapp_url = 'whatsapp://send?' . http_build_query( $params );
		// Record stats
		parent::process_request( $post, $post_data );

		// Redirect to WhatsApp
		wp_redirect( $whatsapp_url );
		die();
	}
}
