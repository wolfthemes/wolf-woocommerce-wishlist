/*!
 * WooCommerce Wishlist 1.1.6
 */
/* jshint -W062 */

var WolfWooCommerceWishlist =  WolfWooCommerceWishlist || {},
	WolfWooCommerceWishlistJSParams = WolfWooCommerceWishlistJSParams || {},
	console = console || {};

WolfWooCommerceWishlist = function( $ ) {

	'use strict';

	return {

		clickEventFlag : false,
		wishlistArray : [],
		cookieName : '',
		cookie : null,
		processing : false,

		/**
		 * Init UI
		 */
		init : function () {

			this.cookieName = WolfWooCommerceWishlistJSParams.siteSlug + '_wc_wishlist';
			this.cookie = Cookies.get( this.cookieName ); // get raw cookie value set by PHP
			this.wishlistArray = this.cookie ? this.cookie.split( /,/ ) : []; // set defatul wishlist as array
			this.wishlistArray = this.arrayUnique( this.wishlistArray ); // remove duplicates
			//$.cookie( this.cookieName, this.wishlistArray, { path : '/' } ); // set cookie again to remove duplicates if any

			this.build();

			// Avoid firing click event several times and mess up everyting
			if ( ! this.clickEventFlag ) {
				this.clickEvent();
				this.removeButton();
			}

			this.clickEventFlag = true;

			//console.log( this.wishlistArray );
			//console.log( $.cookie( this.cookieName ) );
		},

		/**
		 * Set class and button text
		 */
		build : function() {
			var _this = this,
				$button,
				productId,
				addText,
				removeText;

			$( '.wolf_add_to_wishlist' ).each( function() {

				$button = $( this ),
				productId = $button.data( 'product-id' ),
				addText = WolfWooCommerceWishlistJSParams.l10n.addToWishlist,
				removeText = WolfWooCommerceWishlistJSParams.l10n.removeFromWishlist;

				if ( productId ) {

					// in list
					if ( -1 !== $.inArray( productId.toString(), _this.wishlistArray ) ) {

						$button.addClass( 'wolf_in_wishlist' )
							.attr( 'title', removeText );


					} else {

						$button.removeClass( 'wolf_in_wishlist' )
							.attr( 'title', addText );

					}
				}
			} );
		},

		/**
		 * Action on click
		 */
		clickEvent : function () {

			var _this = this,
				$button,
				productId,
				productTitle,
				addText = WolfWooCommerceWishlistJSParams.l10n.addToWishlist,
				removeText = WolfWooCommerceWishlistJSParams.l10n.removeFromWishlist;

			$( document ).on( 'click', '.wolf_add_to_wishlist', function( event ) {

				event.preventDefault();

				$button = $( this ),
				productId = $button.data( 'product-id' ),
				productTitle = $button.data( 'product-title' );

				if ( productId ) {

					if ( $button.hasClass( 'wolf_in_wishlist' ) ) {

						_this.removeFromWishlist( $button, productId );

						if ( $button.find( '.wolf-add-to-wishlist-button-text' ).length ) {
							$button.find( '.wolf-add-to-wishlist-button-text' ).text( addText );
						}

					} else {
						_this.addToWishlist( $button, productId );

						if ( $button.find( '.wolf-add-to-wishlist-button-text' ).length ) {
							$button.find( '.wolf-add-to-wishlist-button-text' ).text( removeText );
						}

						/* Use to track add to wishlist event */
						$( window ).trigger( 'add_to_wishlist', [ productId, productTitle ] );
					}
				}
			} );
		},

		/**
		 * Add product to wishlist
		 */
		addToWishlist : function ( $button, productId ) {

			var text = WolfWooCommerceWishlistJSParams.l10n.removeFromWishlist;

			$button.addClass( 'wolf_in_wishlist' );

			if ( -1 === $.inArray( productId, this.wishlistArray ) ) {

				this.wishlistArray.push( productId.toString() );

				this.wishlistArray = this.arrayUnique( this.wishlistArray );
				this.updateDataBase( this.wishlistArray );

				Cookies.set( this.cookieName, this.wishlistArray.join( ',' ), { path: '/', expires: 7 } );

				$button.attr( 'title', text );
			}

			//console.log( this.wishlistArray );
			//console.log( $.cookie( this.cookieName ) );
		},

		/**
		 * Remove product from wishlist
		 */
		removeFromWishlist : function ( $button, productId ) {

			$button.removeClass( 'wolf_in_wishlist' );

			var index = this.wishlistArray.indexOf( productId.toString()  ),
				text = WolfWooCommerceWishlistJSParams.l10n.addToWishlist;

			if ( -1 !== index ) {
				this.wishlistArray.splice( index, 1 );
			}

			this.wishlistArray = this.arrayUnique( this.wishlistArray );

			this.updateDataBase( this.wishlistArray );

			if ( '' == this.wishlistArray ) {

				//alert( 'clear cookie' );
				Cookies.set( this.cookieName, '', { path: '/', expires: 0 } );
				this.updateDataBase( '[]' );
			
			} else {
				Cookies.set( this.cookieName, this.wishlistArray.join( ',' ), { path: '/', expires: 7 } );
			}

			$button.attr( 'title', text );

			//console.log( this.wishlistArray );
			//console.log( Cookies.get( this.cookieName ) );
		},

		/**
		 * Remove wishlist button on wishlist page
		 */
		removeButton : function () {

			var _this = this,
				$button,
				$tableCell,
				productId;

			$( document ).on( 'click', '.www-remove', function( event ) {
				event.preventDefault();

				if ( _this.processing ) {
					return;
				}

				_this.processing = true;

				var $button = $( this ),
					$tableCell = $button.parent().parent(),
					productId = $button.data( 'product-id' );

				if ( productId ) {
					_this.removeFromWishlist( $button, productId );
					$tableCell.fadeOut( 'slow', function() {
						$( this ).remove();

						// if ( 1 > $( '.wolf-woocommerce-wishlist-product' ).length ) {
						// 	Cookies.set( this.cookieName, '', { path: '/', expires: 0 } );
						// 	_this.updateDataBase( '[]' );

						// 	_this.processing = false;
						// 	location.reload();
						// }

						_this.processing = false;
					} );
				}
			} );
		},

		/**
		 * Update database through AJAX
		 */
		updateDataBase : function ( wishlistArray ) {

			var data = {
				wishlistIds : wishlistArray,
				userId : WolfWooCommerceWishlistJSParams.userId,
				action : 'www_ajax_update_wishlist'
			};

			$.post( WolfWooCommerceWishlistJSParams.ajaxUrl, data, function( response ) {
				if ( response ) {
					//console.log( response );

					if ( 'empty' === response ) {
						//location.reload();
					}
				}
			} );
		},

		/**
		 * Remove duplicate from array
		 */
		arrayUnique : function ( array ) {
			var result = [];
			$.each( array, function( i, e ) {
				if ( -1 == $.inArray( e, result ) ) {
					result.push( e );
				}
			} );
			return result;
		}
	};

}( jQuery );

( function( $ ) {

	'use strict';

	$( document ).ready( function() {
		WolfWooCommerceWishlist.init();
	} );

} )( jQuery );