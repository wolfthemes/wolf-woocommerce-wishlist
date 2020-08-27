/*! Wolf WooCommerce Wishlist Wordpress Plugin v1.1.5 */ 
/*!
 * WooCommerce Wishlist 1.1.6
 */
/* jshint -W062 */
var WolfWooCommerceWishlist=WolfWooCommerceWishlist||{},WolfWooCommerceWishlistJSParams=WolfWooCommerceWishlistJSParams||{},console=console||{};WolfWooCommerceWishlist=function(a){"use strict";return{clickEventFlag:!1,wishlistArray:[],cookieName:"",cookie:null,processing:!1,/**
		 * Init UI
		 */
init:function(){this.cookieName=WolfWooCommerceWishlistJSParams.siteSlug+"_wc_wishlist",this.cookie=Cookies.get(this.cookieName),// get raw cookie value set by PHP
this.wishlistArray=this.cookie?this.cookie.split(/,/):[],// set defatul wishlist as array
this.wishlistArray=this.arrayUnique(this.wishlistArray),// remove duplicates
//$.cookie( this.cookieName, this.wishlistArray, { path : '/' } ); // set cookie again to remove duplicates if any
this.build(),
// Avoid firing click event several times and mess up everyting
this.clickEventFlag||(this.clickEvent(),this.removeButton()),this.clickEventFlag=!0},/**
		 * Set class and button text
		 */
build:function(){var b,c,d,e,f=this;a(".wolf_add_to_wishlist").each(function(){b=a(this),c=b.data("product-id"),d=WolfWooCommerceWishlistJSParams.l10n.addToWishlist,e=WolfWooCommerceWishlistJSParams.l10n.removeFromWishlist,c&&(
// in list
-1!==a.inArray(c.toString(),f.wishlistArray)?b.addClass("wolf_in_wishlist").attr("title",e):b.removeClass("wolf_in_wishlist").attr("title",d))})},/**
		 * Action on click
		 */
clickEvent:function(){var b,c,d,e=this,f=WolfWooCommerceWishlistJSParams.l10n.addToWishlist,g=WolfWooCommerceWishlistJSParams.l10n.removeFromWishlist;a(document).on("click",".wolf_add_to_wishlist",function(h){h.preventDefault(),b=a(this),c=b.data("product-id"),d=b.data("product-title"),c&&(b.hasClass("wolf_in_wishlist")?(e.removeFromWishlist(b,c),b.find(".wolf-add-to-wishlist-button-text").length&&b.find(".wolf-add-to-wishlist-button-text").text(f)):(e.addToWishlist(b,c),b.find(".wolf-add-to-wishlist-button-text").length&&b.find(".wolf-add-to-wishlist-button-text").text(g),/* Use to track add to wishlist event */
a(window).trigger("add_to_wishlist",[c,d])))})},/**
		 * Add product to wishlist
		 */
addToWishlist:function(b,c){var d=WolfWooCommerceWishlistJSParams.l10n.removeFromWishlist;b.addClass("wolf_in_wishlist"),-1===a.inArray(c,this.wishlistArray)&&(this.wishlistArray.push(c.toString()),this.wishlistArray=this.arrayUnique(this.wishlistArray),this.updateDataBase(this.wishlistArray),Cookies.set(this.cookieName,this.wishlistArray.join(","),{path:"/",expires:7}),b.attr("title",d))},/**
		 * Remove product from wishlist
		 */
removeFromWishlist:function(a,b){a.removeClass("wolf_in_wishlist");var c=this.wishlistArray.indexOf(b.toString()),d=WolfWooCommerceWishlistJSParams.l10n.addToWishlist;-1!==c&&this.wishlistArray.splice(c,1),this.wishlistArray=this.arrayUnique(this.wishlistArray),this.updateDataBase(this.wishlistArray),""==this.wishlistArray?(
//alert( 'clear cookie' );
Cookies.set(this.cookieName,"",{path:"/",expires:0}),this.updateDataBase("[]")):Cookies.set(this.cookieName,this.wishlistArray.join(","),{path:"/",expires:7}),a.attr("title",d)},/**
		 * Remove wishlist button on wishlist page
		 */
removeButton:function(){var b=this;a(document).on("click",".www-remove",function(c){if(c.preventDefault(),!b.processing){b.processing=!0;var d=a(this),e=d.parent().parent(),f=d.data("product-id");f&&(b.removeFromWishlist(d,f),e.fadeOut("slow",function(){a(this).remove(),
// if ( 1 > $( '.wolf-woocommerce-wishlist-product' ).length ) {
// 	Cookies.set( this.cookieName, '', { path: '/', expires: 0 } );
// 	_this.updateDataBase( '[]' );
// 	_this.processing = false;
// 	location.reload();
// }
b.processing=!1}))}})},/**
		 * Update database through AJAX
		 */
updateDataBase:function(b){var c={wishlistIds:b,userId:WolfWooCommerceWishlistJSParams.userId,action:"www_ajax_update_wishlist"};a.post(WolfWooCommerceWishlistJSParams.ajaxUrl,c,function(a){})},/**
		 * Remove duplicate from array
		 */
arrayUnique:function(b){var c=[];return a.each(b,function(b,d){-1==a.inArray(d,c)&&c.push(d)}),c}}}(jQuery),function(a){"use strict";a(document).ready(function(){WolfWooCommerceWishlist.init()})}(jQuery);