<?php

if( !function_exists('add_action') ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

class Share_Feedly extends Sharing_Source {
	public $shortname = 'feedly';

	function __construct( $id, array $settings ) {
		parent::__construct( $id, $settings );

		if( 'official' == $this->button_style )
			$this->smart = true;
		else
			$this->smart = false;
	}

	function get_link_addr( $url, $query = '' ) {
		$url = apply_filters( 'sharing_display_link', $url );
		if( !empty( $query ) ) {
			if( stripos( $url, '?' ) === false )
				$url .= '?' . $query;
			else
				$url .= '&amp;' . $query;
		}

		return $url;
	}

	function get_name() {
		return __('Feedly', 'jetwhats');
	}

	function has_custom_button_style() {
		return $this->smart;
	}

	function get_display( $post ) {
		if( apply_filters( 'jetpack_register_post_for_share_counts', true, $post->ID, 'feedly' ) ) {
			sharing_register_post_for_share_counts( $post->ID );
		}

		$feed_id = 'feed/' . get_bloginfo('rss2_url');

		if( $this->smart ) {
			$button = '';
			$button .= '<div class="feedly_button"><div class="feedly">';
			$button .= '<table cellpadding="0" cellspacing="0"><tr>';
			$button .= '<td class="button-wrap">';

			$button .= '<div data-scribe="component:button">';
			$button .= sprintf(
					'<a rel="nofollow" href="%s" class="share-feedly">%s</a>',
					esc_url( $this->get_link_addr( get_permalink( $post->ID ), 'share=feedly' ) ),
					'<i></i>'
				);
			$button .= '</div>';

			$button .= '</td>';
			$button .= '<td class="count-wrap">';

			$button .= '<div data-scribe="component:count">';
			$button .= '<div class="count-number">';
			$button .= sprintf(
					'<span data-feed-id="%s">-</span>',
					rawurlencode( $feed_id )
				);
			$button .= '</div>';
			$button .= '<div class="count-arrow">';
			$button .= '<s></s>';
			$button .= '<i></i>';
			$button .= '</div>';
			$button .= '</div>';

			$button .= '</td>';
			$button .= '</tr></table>';
			$button .= '</div></div>';
			
			return $button;
		} else {
			return $this->get_link(
					get_permalink( $post->ID ),
					_x( 'Feedly', 'share to', 'jetwhats' ),
					__( 'Subscribe on Feedly', 'jetwhats' ),
					'share=feedly',
					'sharing-feedly-' . $post->ID
				);
		}
	}

	function display_header() {
	}

	function display_footer() {
		global $post;
	?>
		<script>
			var feedly_api = '<?php echo esc_js( set_url_scheme( home_url( Feedly_API::API_ENDPOINT . '/' ) ) ); ?>';
			<?php if( $this->smart ): ?>
			var feedly_smart = true;
			<?php else: ?>
			var feedly_smart = false;
			<?php endif; ?>
		</script>
	<?php
		$this->js_dialog( $this->shortname, array( 'width' => 1024, 'height' => 576 ) );
	}

	function process_request( $post, array $post_data ) {
		$feed_url   = get_bloginfo('rss2_url');
		$feedly_url = $this->http() . '://feedly.com/#' . rawurlencode( 'subscription/' . $feed_url );

		// Redirect to Feedly
		wp_redirect( $feedly_url );
		die();
	}
}

class Share_LINE extends Sharing_Source {
	var $shortname = 'line';

	function __construct( $id, array $settings ) {
		parent::__construct( $id, $settings );

		if( 'official' == $this->button_style )
			$this->smart = true;
		else
			$this->smart = false;
	}

	function get_name() {
		return __( 'LINE', 'jetwhats' );
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
			return sprintf(
				'<div class="line_button"><a href="http://line.me/R/msg/text/?%1$s%0D%0A%2$s" class="share-line %3$s" title="%4$s"></a></div>',
				rawurlencode( $this->get_share_title( $post->ID ) ),
				rawurlencode( $this->get_share_url( $post->ID ) ),
				esc_attr( $locale ),
				esc_attr__( 'LINE it!', 'jetwhats' )
			);
		else
			return $this->get_link( get_permalink( $post->ID ), _x( 'LINE', 'share to', 'jetwhats' ), __( 'Click to share on LINE', 'jetwhats' ), 'share=line' );
	}

	function display_header() {
	}

	function display_footer() {
		$this->js_dialog( $this->shortname );
	}

	function process_request( $post, array $post_data ) {
		$line_url = sprintf(
			'http://line.me/R/msg/text/?%1$s%0D%0A%2$s',
			rawurlencode( $this->get_share_title( $post->ID ) ),
			rawurlencode( $this->get_share_url( $post->ID ) )
		);

		// Record stats
		parent::process_request( $post, $post_data );

		// Redirect to LINE
		wp_redirect( $line_url );
		die();
	}
}

class Share_Delicious extends Sharing_Source {
	var $shortname = 'delicious';

	function __construct( $id, array $settings ) {
		parent::__construct( $id, $settings );

		if ( 'official' == $this->button_style )
			$this->smart = true;
		else
			$this->smart = false;
	}

	function get_name() {
		return __( 'Delicious', 'jetwhats' );
	}

	function get_display( $post ) {
		return $this->get_link( get_permalink( $post->ID ), _x( 'Delicious', 'share to', 'jetwhats' ), __( 'Click to save on Delicious', 'jetwhats' ), 'share=delicious' );
	}

	function display_header() {
	}

	function display_footer() {
		$this->js_dialog( $this->shortname, array( 'width' => 550, 'height' => 550 ) );
	}

	function process_request( $post, array $post_data ) {
		$delicious_url = sprintf(
			'https://delicious.com/save?v=5&provider=%1$s&noui&jump=close&url=%2$s&title=%3$s',
			rawurlencode( get_bloginfo('name') ),
			rawurlencode( $this->get_share_url( $post->ID ) ),
			rawurlencode( $this->get_share_title( $post->ID ) )
		);

		// Record stats
		parent::process_request( $post, $post_data );

		// Redirect to Delicious
		wp_redirect( $delicious_url );
		die();
	}
}

class Share_Instapaper extends Sharing_Source {
	var $shortname = 'instapaper';

	function __construct( $id, array $settings ) {
		parent::__construct( $id, $settings );

		if( 'official' == $this->button_style )
			$this->smart = true;
		else
			$this->smart = false;
	}

	function get_name() {
		return __( 'Instapaper', 'jetwhats' );
	}

	function has_custom_button_style() {
		return $this->smart;
	}

	function get_display( $post ) {
		if( $this->smart )
			return sprintf(
				'<div class="instapeper_button"><iframe border="0" scrolling="no" width="78" height="17" allowtransparency="true" frameborder="0" style="margin-bottom: -3px; z-index: 1338; border: 0px; background-color: transparent; overflow: hidden;" src="https://www.instapaper.com/e2?url=%1$s&title=%2$s&description=%3$s"></iframe></div>',
				rawurlencode( $this->get_share_url( $post->ID ) ),
				rawurlencode( $this->get_share_title( $post->ID ) ),
				rawurlencode( $this->get_url_excerpt($post) )
			);
		else
			return $this->get_link( get_permalink( $post->ID ), _x( 'Instapaper', 'share to', 'jetwhats' ), __( 'Save this for later with Instapaper', 'jetwhats' ), 'share=instapaper' );
	}

	function display_header() {
	}

	function display_footer() {
		if( !$this->smart )
			$this->js_dialog( $this->shortname );
	}

	function process_request( $post, array $post_data ) {
		$instapaper_url = sprintf(
			'https://www.instapaper.com/hello2?url=%1$s&title=%2$s&description=%3$s',
			rawurlencode( $this->get_share_url( $post->ID ) ),
			rawurlencode( $this->get_share_title( $post->ID ) ),
			rawurlencode( $this->get_url_excerpt($post) )
		);

		// Record stats
		parent::process_request( $post, $post_data );

		// Redirect to Instapaper
		wp_redirect( $instapaper_url );
		die();
	}

	function get_url_excerpt( $post ) {
		$url_excerpt = $post->post_excerpt;
		if( empty( $url_excerpt ) )
			$url_excerpt = $post->post_content;

		$url_excerpt = strip_tags( strip_shortcodes( $url_excerpt ) );
		$url_excerpt = wp_html_excerpt( $url_excerpt, 100 );
		$url_excerpt = rtrim( preg_replace( '/[^ .]*$/', '', $url_excerpt ) );
		
		return $url_excerpt;
	}
}

class Share_Hatena extends Sharing_Source {
	var $shortname = 'hatena';

	function __construct( $id, array $settings ) {
		parent::__construct( $id, $settings );

		if( 'official' == $this->button_style )
			$this->smart = true;
		else
			$this->smart = false;
	}

	function get_name() {
		return __( 'Hatena', 'jetwhats' );
	}

	function has_custom_button_style() {
		return $this->smart;
	}

	private function guess_locale_from_lang( $lang ) {
		if( strpos( $lang, 'ja' ) === 0 )
			return 'ja';

		return 'en';
	}

	private function get_resource_host() {
		if( is_ssl() )
			return 'https://b.hatena.ne.jp';

		return 'http://b.st-hatena.com';
	}

	function get_display( $post ) {
		$locale = $this->guess_locale_from_lang( get_locale() );

		if( $this->smart )
			return sprintf(
				'<div class="hatena_button">
					<a href="http://b.hatena.ne.jp/entry/%1$s" class="hatena-bookmark-button" data-hatena-bookmark-title="%2$s" data-hatena-bookmark-layout="standard-balloon" data-hatena-bookmark-lang="%3$s" title="%4$s">
						<img src="%5$s" alt="%3$s" width="20" height="20" style="border: none;" />
					</a>
				</div>',
				$this->get_share_url( $post->ID ),
				esc_attr( $this->get_share_title( $post->ID ) ),
				esc_attr( $locale ),
				esc_attr__( 'Add this entry to Hatena Bookmark', 'jetwhats' ),
				esc_url( $this->get_resource_host() . '/images/entry-button/button-only@2x.png' )
			);
		else
			return $this->get_link(
				get_permalink( $post->ID ),
				_x( 'Hatena', 'share to', 'jetwhats' ),
				__( 'Add this entry to Hatena Bookmark', 'jetwhats' ),
				'share=hatena',
				'sharing-hatena-' . $post->ID
			);
	}

	function display_header() {
		if( !$this->smart )
			wp_enqueue_style( 'jetwhats', jetwhats__PLUGIN_URL . 'style.css', array('sharedaddy'), jetwhats__VERSION );
	}

	function display_footer() {
		if( $this->smart ) {
	?>
		<script type="text/javascript" src="<?php echo $this->get_resource_host(); ?>/js/bookmark_button.js" charset="utf-8" async="async"></script>
	<?php
		} else
			$this->js_dialog( $this->shortname );
	}

	function process_request( $post, array $post_data ) {
		$hatena_url = sprintf(
			'http://b.hatena.ne.jp/entry/%1$s',
			$this->get_share_url( $post->ID )
		);

		// Record stats
		parent::process_request( $post, $post_data );

		// Redirect to Hatena
		wp_redirect( $hatena_url );
		die();
	}
}

class Share_Google extends Share_GooglePlus1 {
	public function display_footer() {
		if( !$this->smart ) {
		?>
			<script>var google_api = '<?php echo esc_js( set_url_scheme( home_url( Google_API::API_ENDPOINT . '/' ) ) ); ?>';</script>
		<?php
		}

		parent::display_footer();
	}
}
