/**
 * Admin Enqueue Script
 *
 * @package     wpsyncsheets-for-woocommerce
 */

(function($) {
	"use strict";
	$( document ).ready(
		function(){
			$( '#wpssw_metabox_sync_btn' ).on(
				'click',
				function( e ){
					e.preventDefault();
					var orderId = getParameterByName( 'post' );
					$( '#wpssw_syncbtn_meta_box .syncbtnloader' ).show();
					$( '#wpssw_metabox_sync_btn' ).attr( 'disabled',true );
					var sync_nonce_token;
					sync_nonce_token = admin_ajax_object.sync_nonce_token;
					$.ajax(
						{
							url : admin_ajax_object.ajaxurl,
							type : 'post',
							data :"action=wpssw_sync_single_order_data&order_id=" + orderId + "&sync_nonce_token=" + sync_nonce_token,// sync_single_entry.
							success : function( response ) {
								if (response === 'successful') {
									alert( 'Sync Successfully' );
									$( '#wpssw_syncbtn_meta_box .syncbtnloader' ).hide();
									$( '#wpssw_metabox_sync_btn' ).attr( 'disabled',false );
								} else if (String( response ) === 'statussheetnotexist') {
									alert( "This Order's status sheet is deleted from your spreadsheet so first save your general settings and try again." );
									$( '#wpssw_syncbtn_meta_box .syncbtnloader' ).hide();
									$( '#wpssw_metabox_sync_btn' ).attr( 'disabled',false );
									return false;
								} else if (String( response ) === 'allordersheetnotexist') {
									alert( 'All Orders sheet is deleted from your spreadsheet so first save your general settings and try again.' );
									$( '#wpssw_syncbtn_meta_box .syncbtnloader' ).hide();
									$( '#wpssw_metabox_sync_btn' ).attr( 'disabled',false );
									return false;
								} else {
									alert( 'Your Google Sheets API limit has been reached. Please take a look at our FAQ.' );
									$( '#wpssw_syncbtn_meta_box .syncbtnloader' ).hide();
									$( '#wpssw_metabox_sync_btn' ).attr( 'disabled',false );
								}
							}
						}
					)
					.fail(
						function() {
							alert( 'Error' );
							$( '#wpssw_syncbtn_meta_box .syncbtnloader' ).hide();
							$( '#wpssw_metabox_sync_btn' ).attr( 'disabled',false );
						}
					);
				}
			);
			$( '.wpssw_syncbtn_column .wpssw_single_order_sync_btn' ).on(
				'click',
				function( e ){
					e.preventDefault();
					var orderId = $( this ).closest( "tr" ).attr( "id" );
					var ID      = orderId.slice( (orderId.indexOf( "-" ) + 1) );
					syncData( ID );
				}
			);
			function syncData(ID){
				var trId = 'post-' + ID;
				var sync_nonce_token;
				sync_nonce_token = admin_ajax_object.sync_nonce_token;
				$( '#' + trId + ' .syncbtnloader' ).show();
				$( '#' + trId + ' .wpssw_single_order_sync_btn' ).hide();
				$.ajax(
					{
						url : admin_ajax_object.ajaxurl,
						type : 'post',
						data :"action=wpssw_sync_single_order_data&order_id=" + ID + "&sync_nonce_token=" + sync_nonce_token,// sync_single_entry.
						success : function( response ) {
							if (response === 'successful') {
								alert( 'Sync Successfully' );
								$( '#' + trId + ' .syncbtnloader' ).hide();
								$( '#' + trId + ' .wpssw_single_order_sync_btn' ).css( "display", "inline-block" );
							} else if (String( response ) === 'statussheetnotexist') {
								alert( "This Order's status sheet is deleted from your spreadsheet so first save your general settings and try again." );
								$( '#' + trId + ' .syncbtnloader' ).hide();
								$( '#' + trId + ' .wpssw_single_order_sync_btn' ).css( "display", "inline-block" );
								return false;
							} else if (String( response ) === 'allordersheetnotexist') {
								alert( 'All Orders sheet is deleted from your spreadsheet so first save your general settings and try again.' );
								$( '#' + trId + ' .syncbtnloader' ).hide();
								$( '#' + trId + ' .wpssw_single_order_sync_btn' ).css( "display", "inline-block" );
								return false;
							} else {
								console.log( response );
								alert( 'Your Google Sheets API limit has been reached. Please take a look at our FAQ.' );
								$( '#' + trId + ' .syncbtnloader' ).hide();
								$( '#' + trId + ' .wpssw_single_order_sync_btn' ).css( "display", "inline-block" );
							}
						}
					}
				)
				.fail(
					function() {
						alert( 'Error' );
						$( '#' + trId + ' .syncbtnloader' ).hide();
						$( '#' + trId + ' .wpssw_single_order_sync_btn' ).css( "display", "inline-block" );
					}
				);
			}
		}
	);
})( jQuery );
function getParameterByName(name, url) {
	"use strict";
	if ( ! url) {
		url = window.location.href;
	}
	name        = name.replace( /[\[\]]/g, '\\jQuery&' );
	var regex   = new RegExp( '[?&]' + name + '(=([^&#]*)|&|#|jQuery)' ),
		results = regex.exec( url );
	if ( ! results) {
		return null;
	}
	if ( ! results[2]) {
		return '';
	}
	return decodeURIComponent( results[2].replace( /\+/g, ' ' ) );
}
