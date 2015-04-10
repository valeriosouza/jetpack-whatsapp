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
waShBtn=function(){this.isIos===!0&&this.cntLdd(window,this.crBtn)},waShBtn.prototype.isIos=navigator.userAgent.match(/Android|iPhone/i)&&!navigator.userAgent.match(/iPod|iPad/i)?!0:!1,waShBtn.prototype.cntLdd=function(win,fn){var done=!1,top=!0,doc=win.document,root=doc.documentElement,add=doc.addEventListener?"addEventListener":"attachEvent",rem=doc.addEventListener?"removeEventListener":"detachEvent",pre=doc.addEventListener?"":"on",init=function(e){("readystatechange"!=e.type||"complete"==doc.readyState)&&(("load"==e.type?win:doc)[rem](pre+e.type,init,!1),!done&&(done=!0)&&fn.call(win,e.type||e))},poll=function(){try{root.doScroll("left")}catch(e){return void setTimeout(poll,50)}init("poll")};if("complete"==doc.readyState)fn.call(win,"lazy");else{if(doc.createEventObject&&root.doScroll){try{top=!win.frameElement}catch(e){}top&&poll()}doc[add](pre+"DOMContentLoaded",init,!1),doc[add](pre+"readystatechange",init,!1),win[add](pre+"load",init,!1)}},waShBtn.prototype.addStyling=function(){var s=document.createElement("style"),c="body,html{padding:0;margin:0;height:100%;width:100%}.wa_btn{background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNi4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkViZW5lXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iMTZweCIgaGVpZ2h0PSIxNnB4IiB2aWV3Qm94PSIwIDAgMTYgMTYiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDE2IDE2IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxnPg0KCTxnPg0KCQk8cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZmlsbD0iI0ZGRkZGRiIgZD0iTTguMTI5LDAuOTQ1Yy0zLjc5NSwwLTYuODcyLDMuMDc3LTYuODcyLDYuODczDQoJCQljMCwxLjI5OCwwLjM2LDIuNTEyLDAuOTg2LDMuNTQ5bC0xLjI0LDMuNjg4bDMuODA1LTEuMjE5YzAuOTg0LDAuNTQ0LDIuMTE2LDAuODU0LDMuMzIxLDAuODU0YzMuNzk1LDAsNi44NzEtMy4wNzUsNi44NzEtNi44NzENCgkJCVMxMS45MjQsMC45NDUsOC4xMjksMC45NDV6IE04LjEyOSwxMy41MzhjLTEuMTYyLDAtMi4yNDQtMC4zNDgtMy4xNDctMC45NDZsLTIuMTk4LDAuNzA1bDAuNzE1LTIuMTI0DQoJCQljLTAuNjg2LTAuOTQ0LTEuMDktMi4xMDMtMS4wOS0zLjM1NGMwLTMuMTU1LDIuNTY2LTUuNzIyLDUuNzIxLTUuNzIyczUuNzIxLDIuNTY2LDUuNzIxLDUuNzIyUzExLjI4MywxMy41MzgsOC4xMjksMTMuNTM4eg0KCQkJIE0xMS4zNTIsOS4zNzljLTAuMTc0LTAuMDk0LTEuMDItMC41NS0xLjE3OC0wLjYxNUMxMC4wMTQsOC43LDkuODk4LDguNjY2LDkuNzc1LDguODM3QzkuNjUyLDkuMDA3LDkuMzAxLDkuMzksOS4xOTMsOS41MDUNCgkJCUM5LjA4OCw5LjYxNyw4Ljk4NCw5LjYyOSw4LjgxMiw5LjUzM2MtMC4xNzEtMC4wOTYtMC43My0wLjMtMS4zNzgtMC45MjNjLTAuNTA0LTAuNDg0LTAuODM0LTEuMDcyLTAuOTMtMS4yNTINCgkJCWMtMC4wOTUtMC4xOCwwLTAuMjcxLDAuMDkxLTAuMzU0QzYuNjc3LDYuOTI4LDYuNzc4LDYuODA1LDYuODcsNi43MDZjMC4wOTEtMC4xLDAuMTI0LTAuMTcxLDAuMTg3LTAuMjg2DQoJCQljMC4wNjItMC4xMTUsMC4wMzgtMC4yMTgtMC4wMDMtMC4zMDhDNy4wMTIsNi4wMjMsNi42OTQsNS4xNDYsNi41NjEsNC43OUM2LjQyOCw0LjQzNCw2LjI4LDQuNDg2LDYuMTc3LDQuNDgyDQoJCQlDNi4wNzUsNC40NzksNS45NTgsNC40NTksNS44NDEsNC40NTZDNS43MjQsNC40NTEsNS41MzMsNC40ODcsNS4zNjYsNC42NTdjLTAuMTY3LDAuMTctMC42MzcsMC41NzYtMC42NjksMS40MzkNCgkJCXMwLjU2NSwxLjcyMiwwLjY0OCwxLjg0MWMwLjA4NCwwLjEyMSwxLjE0LDEuOTkxLDIuODk3LDIuNzYyYzEuNzU2LDAuNzcsMS43NjYsMC41MzQsMi4wODgsMC41MTgNCgkJCWMwLjMyMi0wLjAxOCwxLjA1NS0wLjM4NiwxLjIxNS0wLjc4OWMwLjE2Mi0wLjQwNSwwLjE3Ni0wLjc1NSwwLjEzNS0wLjgzMUMxMS42MzksOS41MjEsMTEuNTIzLDkuNDc1LDExLjM1Miw5LjM3OXoiLz4NCgk8L2c+DQo8L2c+DQo8L3N2Zz4NCg==);border:1px solid rgba(0,0,0,.1);display:inline-block!important;position:relative;font-family:Arial,sans-serif;letter-spacing:.4px;cursor:pointer;font-weight:400;text-transform:none;color:#fff;border-radius:2px;background-color:#5cbe4a;background-repeat:no-repeat;line-height:1.2;text-decoration:none;text-align:left}.wa_btn_s{font-size:12px;background-size:16px;background-position:5px 2px;padding:3px 6px 3px 25px}.wa_btn_m{font-size:16px;background-size:20px;background-position:4px 2px;padding:4px 6px 4px 30px}.wa_btn_l{font-size:16px;background-size:20px;background-position:5px 5px;padding:8px 6px 8px 30px}";return s.type="text/css",s.styleSheet?s.styleSheet.cssText=c:s.appendChild(document.createTextNode(c)),s},waShBtn.prototype.crBtn=function(){var b=[].slice.call(document.querySelectorAll(".wa_btn"));iframe=new Array;for(var i=0;i<b.length;i++){var parent=b[i].parentNode,t=b[i].getAttribute("data-text"),u=b[i].getAttribute("data-href"),o=b[i].getAttribute("href"),at="?text="+encodeURIComponent(t);t&&(at+="%20"),at+=encodeURIComponent(u?u:document.URL),b[i].setAttribute("href",o+at),b[i].setAttribute("target","_top"),iframe[i]=document.createElement("iframe"),iframe[i].width=1,iframe[i].height=1,iframe[i].button=b[i],iframe[i].style.border=0,iframe[i].style.overflow="hidden",iframe[i].border=0,iframe[i].setAttribute("scrolling","no"),iframe[i].addEventListener("load",function(){this.contentDocument.body.appendChild(this.button),this.contentDocument.getElementsByTagName("head")[0].appendChild(theWaShBtn.addStyling());var meta=document.createElement("meta");meta.setAttribute("charset","utf-8"),this.contentDocument.getElementsByTagName("head")[0].appendChild(meta),this.width=Math.ceil(this.contentDocument.getElementsByTagName("a")[0].getBoundingClientRect().width),this.height=Math.ceil(this.contentDocument.getElementsByTagName("a")[0].getBoundingClientRect().height)},!1),parent.insertBefore(iframe[i],b[i])}};var theWaShBtn=new waShBtn;
