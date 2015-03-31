if( sharing_js_options && sharing_js_options.counts ) {
	WPCOMSharing.done_urls_jetwhats = [];

	var jetwhats_Sharing = {
		get_counts: function( url ) {
			var https_url, http_url, urls, id, service, service_urls, service_url;

			id = WPCOM_sharing_counts[ url ];

			if('undefined' != typeof WPCOMSharing.done_urls_jetwhats[ id ] ) {
				return;
			}

			https_url = encodeURIComponent( url.replace( /^http:\/\//i, 'https://' ) );
			http_url  = encodeURIComponent( url.replace( /^https:\/\//i, 'http://' ) );

			urls = {
				feedly: [
					feedly_api + '?url=' + encodeURIComponent( url ) + '&callback=jetwhats_Sharing.update_feedly_count'
				],
				hatena: [
					'http://api.b.st-hatena.com/entry.counts?url=' +
						https_url +
						'&url=' +
						http_url +
						'&callback=jetwhats_Sharing.update_hatena_count'
				],
				google: [
					google_api + '?url=' + encodeURIComponent( url ) + '&callback=jetwhats_Sharing.update_google_count'
				]
			};

			if('https:' == window.location.protocol ) {
				delete urls['hatena'];
			}

			for( service in urls ) {
				if( ! jQuery('a[data-shared=sharing-' + service + '-' + id  + ']').length ) {
					continue;
				}

				while( ( service_url = urls[ service ].pop() ) ) {
					jQuery.getScript( service_url );
				}
			}
			WPCOMSharing.done_urls_jetwhats[ id ] = true;
		},
		update_feedly_count: function( data ) {
			if( feedly_smart ) {
				jQuery('.sd-social-official .feedly_button .count-number span').text( data.subscribers );
				jQuery('.sd-social-official .feedly_button .count-wrap').show();
			} else {
				if('undefined' != typeof data.subscribers && ( data.subscribers * 1 ) > 0 ) {
					WPCOMSharing.inject_share_count('sharing-feedly-' + WPCOM_sharing_counts[ data.url ], data.subscribers );
				}
			}
		},
		update_hatena_count: function( data ) {
			if('undefined' != typeof data && 'undefined' != typeof Object.keys( data ) && Object.keys( data ).length > 0 ){
				if('undefined' != typeof data[ Object.keys( data )[0] ] ) {
					shareCount += data[ Object.keys( data )[0] ];
				}

				if('undefined' != typeof data[ Object.keys( data )[1] ] ) {
					shareCount += data[ Object.keys( data )[1] ];
				}

				if( shareCount > 0 ) {
					WPCOMSharing.inject_share_count('sharing-hatena-' + WPCOM_sharing_counts[ WPCOMSharing.get_permalink( Object.keys( data )[0] ) ], shareCount );
				}
			}
		},
		update_google_count: function( data ) {
			if('undefined' != typeof data.count && ( data.count * 1 ) > 0 ) {
				WPCOMSharing.inject_share_count('sharing-google-' + WPCOM_sharing_counts[ data.url ], data.count );
			}
		}
	};
}

jQuery(document).ready(function($) {
	if('undefined' != typeof WPCOM_sharing_counts ) {
		for( var url in WPCOM_sharing_counts ) {
			jetwhats_Sharing.get_counts( url );
		}
	}
});
