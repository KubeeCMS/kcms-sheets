/**
 * Admin Enqueue Script
 *
 * @package     wpsyncsheets-for-woocommerce
 */

(function($) {
	"use strict";
	$( document ).ready(
		function(){
			// Enable/Disable Header field input.
			$( '#woocommerce_spreadsheet' ).on(
				'change',
				function() {
					var newRequest = $( '#woocommerce_spreadsheet' ).val();
					if (String( newRequest ) === 'new') {
						$( '#header_fields' ).prop( 'disabled', false );
						$( "#productwise" ).removeClass( 'disabled' );
						$( "#orderwise" ).removeClass( 'disabled' );
						$( '.manage-row' ).prop( 'disabled', false );
						$( '#selectall' ).show();
						$( '#selectnone' ).show();
						$( '#prdselectall' ).show();
						$( '#prdselectnone' ).show();
						$( '#newsheet' ).show();
						$( '.synctr' ).hide();
						$( '.ord_import_row' ).hide();
						$( '.wpssw_crud_ord_row' ).hide();
						$( '#view_spreadsheet' ).hide();
						$( '#clear_spreadsheet' ).hide();
						$( "#sortable" ).sortable(
							{
								disabled: false
							}
						);
						$( ".headers_chk" ).attr( "disabled", false );
						$( '#wpssw-headers-notice' ).hide();
						$( '#prdassheetheaders' ).prop( 'disabled', false );
						$( "#product-sortable" ).sortable(
							{
								disabled: false
							}
						);
						$( '.manage_order' ).prop( 'disabled', false );
						$( "#asc_order" ).removeClass( 'disabled' );
						$( "#desc_order" ).removeClass( 'disabled' );
					} else {
						$( '#header_fields' ).prop( 'disabled', 'disabled' );
						$( "#productwise" ).addClass( 'disabled' );
						$( "#orderwise" ).addClass( 'disabled' );
						$( '.manage-row' ).prop( 'disabled', 'disabled' );
						$( '.synctr' ).show();
						$( '.ord_import_row' ).show();
						$( '.wpssw_crud_ord_row' ).show();
						$( '#selectall' ).hide();
						$( '#selectnone' ).hide();
						$( '#prdselectall' ).hide();
						$( '#prdselectnone' ).hide();
						$( '#newsheet' ).hide();
						$( '#view_spreadsheet' ).show();
						$( '#clear_spreadsheet' ).show();
						$( "#sortable" ).sortable(
							{
								disabled: true
							}
						);
						$( "#product-sortable" ).sortable(
							{
								disabled: true
							}
						);
						$( '#wpssw-headers-notice' ).show();
						$( '#prdassheetheaders' ).prop( 'disabled', true );
						$( '.manage_order' ).prop( 'disabled', true );
						$( "#asc_order" ).addClass( 'disabled' );
						$( "#desc_order" ).addClass( 'disabled' );
					}
				}
			);
			$( 'input[type=radio][name=order_ascdesc]' ).on(
				'change',
				function() {
					var val = $( 'input[name="order_ascdesc"]:checked' ).val();
					if (String( val ) === 'descorder') {
						$( "#asc_order" ).removeAttr( "checked" );
						$( "#desc_order" ).attr( 'checked', true );
					} else {
						$( "#asc_order" ).attr( 'checked', true );
						$( "#desc_order" ).removeAttr( "checked" );
					}
				}
			);
			$( 'input[type=radio][name=header_format]' ).on(
				'change',
				function() {
					$( '#sortable li' ).remove();
					var selectedOpt = this.value.toString();
					if ( String( selectedOpt ) === 'productwise') {
						$( '#header_fields option' ).remove();
						var prdWise     = $( '#prdwise' ).val();
						var productWise = prdWise.split( ',' );
						for (var p in productWise ) {
							var labelId = productWise[p].replace( / /g,"_" ).toLowerCase();
							$( "#sortable" ).append( '<li class="ui-state-default ui-sortable-handle"><label for="' + labelId + '"><span class="ui-icon ui-icon-caret-2-n-s"></span><span class="wootextfield">' + productWise[p] + '</span><span class="ui-icon ui-icon-pencil"></span><input type="checkbox" name="header_fields_custom[]" value="' + productWise[p] + '" class="headers_chk1" hidden="true" checked><input type="checkbox" name="header_fields[]" id="' + labelId + '" class="headers_chk" value="' + productWise[p] + '" checked><span class="checkbox-switch-new"></span></label></li>' );
						}
						$( '.repeat_checkbox' ).show();
					} else if ( String( selectedOpt ) === 'orderwise' ) {
						$( '#header_fields option' ).remove();
						var ordWise   = $( '#ordwise' ).val();
						var orderWise = ordWise.split( ',' );
						for (var p in orderWise ) {
							var labelId = orderWise[p].replace( / /g,"_" ).toLowerCase();
							$( "#sortable" ).append( '<li class="ui-state-default ui-sortable-handle"><label for="' + labelId + '"><span class="ui-icon ui-icon-caret-2-n-s"></span><span class="wootextfield">' + orderWise[p] + '</span><span class="ui-icon ui-icon-pencil"></span><input type="checkbox" name="header_fields_custom[]" value="' + orderWise[p] + '" class="headers_chk1" hidden="true" checked><input type="checkbox" name="header_fields[]" id="' + labelId + '" class="headers_chk" value="' + orderWise[p] + '" checked><span class="checkbox-switch-new"></span></label></li>' );
						}
						$( '.repeat_checkbox' ).hide();
					}
				}
			);
			// Select all headers.
			$( "#selectall" ).on(
				'click',
				function(){
					$( ".headers_chk" ).prop( 'checked', true );
					$( ".headers_chk1" ).prop( 'checked', true );
				}
			);
			// Select all headers.
			$( "#selectnone" ).on(
				'click',
				function(){
					$( ".headers_chk" ).prop( 'checked', false );
					$( ".headers_chk1" ).prop( 'checked', false );

					$( ".updateorder .headers_chk" ).prop( 'checked', true );
					$( ".updateorder  .headers_chk1" ).prop( 'checked', true );
					$( ".deleteorder .headers_chk" ).prop( 'checked', true );
					$( ".deleteorder  .headers_chk1" ).prop( 'checked', true );
				}
			);
			// Select all headers.
			$( "body" ).on(
				'click',
				"#prdselectall",
				function(){
					$( ".prdheaders_chk" ).prop( 'checked', true );
					$( ".prdheaders_chk1" ).prop( 'checked', true );
				}
			);
			// Select all headers.
			$( "body" ).on(
				'click',
				"#prdselectnone",
				function(){
					$( ".prdheaders_chk" ).prop( 'checked', false );
					$( ".prdheaders_chk1" ).prop( 'checked', false );
				}
			);
			// Select all category.
			$( "#prdcatselectall" ).on(
				'click',
				function(){
					$( ".prdcatheaders_chk" ).prop( 'checked', true );
				}
			);
			$( "#prdcatselectnone" ).on(
				'click',
				function(){
					$( ".prdcatheaders_chk" ).prop( 'checked', false );
				}
			);
			// Select all Product.
			$( "#woo-proselectall" ).on(
				'click',
				function(){
					$( ".woo-pro-headers-chk" ).prop( 'checked', true );
					$( ".woo-pro-headers-chk1" ).prop( 'checked', true );
				}
			);
			$( "#woo-proselectnone" ).on(
				'click',
				function(){
					$( ".woo-pro-headers-chk" ).prop( 'checked', false );
					$( ".woo-pro-headers-chk1" ).prop( 'checked', false );
					$( ".insertproduct .woo-pro-headers-chk" ).prop( 'checked', true );
					$( ".insertproduct  .woo-pro-headers-chk1" ).prop( 'checked', true );
					$( ".updateproduct .woo-pro-headers-chk" ).prop( 'checked', true );
					$( ".updateproduct  .woo-pro-headers-chk1" ).prop( 'checked', true );
					$( ".deleteproduct .woo-pro-headers-chk" ).prop( 'checked', true );
					$( ".deleteproduct  .woo-pro-headers-chk1" ).prop( 'checked', true );
				}
			);
			$( "#woo-custselectall" ).on(
				'click',
				function(){
					$( ".woo-cust-headers-chk" ).prop( 'checked', true );
					$( ".woo-cust-headers-chk1" ).prop( 'checked', true );
				}
			);
			$( "#woo-custselectnone" ).on(
				'click',
				function(){
					$( ".woo-cust-headers-chk" ).prop( 'checked', false );
					$( ".woo-cust-headers-chk1" ).prop( 'checked', false );
				}
			);
			$( "#woo-couponselectall" ).on(
				'click',
				function(){
					$( ".woo-coupon-headers-chk" ).prop( 'checked', true );
					$( ".woo-coupon-headers-chk1" ).prop( 'checked', true );
				}
			);
			$( "#woo-couponselectnone" ).on(
				'click',
				function(){
					$( ".woo-coupon-headers-chk" ).prop( 'checked', false );
					$( ".woo-coupon-headers-chk1" ).prop( 'checked', false );
				}
			);
			$( "#woo-eventselectall" ).on(
				'click',
				function(){
					$( ".woo-event-headers-chk" ).prop( 'checked', true );
					$( ".woo-event-headers-chk1" ).prop( 'checked', true );
				}
			);
			$( "#woo-eventselectnone" ).on(
				'click',
				function(){
					$( ".woo-event-headers-chk" ).prop( 'checked', false );
					$( ".woo-event-headers-chk1" ).prop( 'checked', false );
				}
			);
			$( document ).on(
				'click',
				'.ui-icon-pencil',
				function(e) {
					e.preventDefault();
					var $wpsswThis = $( this );
					var headerName = $( this ).siblings( '.wootextfield' ).html();
					$( this ).siblings( '.wootextfield' ).html( '<input type="text" style="width: 145px;" class="editheader" value="' + headerName + '">' );
					$( this ).parent().find( ".editheader" ).focus();
					$( this ).parent().find( ".editheader" ).val( '' );
					$( this ).parent().find( ".editheader" ).val( headerName );
					$( this ).parent().parent( 'li' ).addClass( 'custom' );
					setTimeout( function(){ $wpsswThis.removeClass( 'ui-icon-pencil' ).addClass( 'ui-icon-check' ); }, 10 );
				}
			);
			$( document ).on(
				'click',
				'.ui-icon-check',
				function(e) {
					e.preventDefault();
					var $wpsswThis = $( this );
					var input      = $( this ).parent().find( ".editheader" ).val();
					if ( ! $.trim( input ) ) {
						alert( 'Please enter Header Name' );
						$( this ).parent().find( ".editheader" ).focus();
						return false;
					}
					$( this ).siblings( '.wootextfield' ).html( input );
					$( this ).parent().parent( 'li' ).removeClass( 'custom' );
					setTimeout(
						function(){
							var input = $wpsswThis.siblings( "input" ).val();
							$wpsswThis.siblings( '.wootextfield' ).html( input );
							$wpsswThis.removeClass( 'ui-icon-check' ).addClass( 'ui-icon-pencil' ); },
						10
					);
				}
			);
			$( document ).on(
				'focusout',
				'.editheader',
				function(e) {
					e.preventDefault();
					if ( ! $.trim( $( this ).val() )) {
						return false;
					}
					var $wpsswThis = $( this );
					$( this ).parent().parent().find( '.headers_chk1' ).attr( 'value',$( this ).val() );
					$( this ).parent().parent().find( '.prdheaders_chk1' ).attr( 'value',$( this ).val() );
					$( this ).parent().parent().find( '.woo-pro-headers-chk1' ).attr( 'value',$( this ).val() );
					$( this ).parent().parent().find( '.woo-cust-headers-chk1' ).attr( 'value',$( this ).val() );
					$( this ).parent().parent().find( '.woo-coupon-headers-chk1' ).attr( 'value',$( this ).val() );
					$( this ).parent().parent().find( '.woo-event-headers-chk1' ).attr( 'value',$( this ).val() );
				}
			);
			// Select all category.
			$( "body" ).on(
				'click',
				"#producatselectall",
				function(){
					$( ".producatheaders_chk" ).prop( 'checked', true );
				}
			);
			$( "body" ).on(
				'click',
				"#producatselectnone",
				function(){
					$( ".producatheaders_chk" ).prop( 'checked', false );
				}
			);
			// Product Header.
			$( document ).on(
				'click',
				'.ui-icon-pencil-prd',
				function(e) {
					e.preventDefault();
					var $wpsswThis = $( this );
					var headerName = $( this ).siblings( '.wootextfield' ).html();
					$( this ).siblings( '.wootextfield' ).html( '<input type="text" style="width: 145px;" class="prdeditheader" value="' + headerName + '">' );
					$( this ).parent().find( ".prdeditheader" ).focus();
					$( this ).parent().find( ".prdeditheader" ).val( '' );
					$( this ).parent().find( ".prdeditheader" ).val( headerName );
					$( this ).parent().parent( 'li' ).addClass( 'custom' );
					setTimeout(
						function(){
							$wpsswThis.removeClass( 'ui-icon-pencil' ).addClass( 'ui-icon-check' );
							$wpsswThis.removeClass( 'ui-icon-pencil-prd' ).addClass( 'ui-icon-check-prd' );
						},
						10
					);
				}
			);
			$( document ).on(
				'click',
				'.ui-icon-check-prd',
				function(e) {
					e.preventDefault();
					var $wpsswThis = $( this );
					var input      = $( this ).parent().find( ".editheader" ).val();
					if ( ! $.trim( input ) ) {
						alert( 'Please enter Header Name' );
						$( this ).parent().find( ".prdeditheader" ).focus();
						return false;
					}
					$( this ).siblings( '.wootextfield' ).html( input );
					$( this ).parent().parent( 'li' ).removeClass( 'custom' );
					setTimeout(
						function(){
							var input = $wpsswThis.siblings( "input" ).val();
							$wpsswThis.siblings( '.wootextfield' ).html( input );
							$wpsswThis.removeClass( 'ui-icon-check' ).addClass( 'ui-icon-pencil' );
							$wpsswThis.removeClass( 'ui-icon-check-prd' ).addClass( 'ui-icon-pencil-prd' );
						},
						10
					);
				}
			);
			$( document ).on(
				'focusout',
				'.prdeditheader',
				function(e) {
					e.preventDefault();
					if ( ! $.trim( $( this ).val() )) {
						return false;
					}
					var $wpsswThis = $( this );
					$( this ).parent().parent().find( '.prdheaders_chk1' ).attr( 'value',$( this ).val() );
				}
			);
			$( document ).on(
				'change',
				'.headers_chk',
				function(e) {
					if ($( this ).is( ":checked" )) {
						$( this ).siblings( ':checkbox' ).prop( 'checked', true );
					} else if ($( this ).is( ":not(:checked)" )) {
						$( this ).siblings( ':checkbox' ).prop( 'checked', false );
					}
				}
			);
			$( document ).on(
				'change',
				'.prdheaders_chk',
				function(e) {
					if ($( this ).is( ":checked" )) {
						$( this ).siblings( ':checkbox' ).prop( 'checked', true );
					} else if ($( this ).is( ":not(:checked)" )) {
						$( this ).siblings( ':checkbox' ).prop( 'checked', true );
					}
				}
			);
			$( document ).on(
				'change',
				'.woo-pro-headers-chk',
				function(e) {
					if ($( this ).is( ":checked" )) {
						$( this ).siblings( ':checkbox' ).prop( 'checked', true );
					} else if ($( this ).is( ":not(:checked)" )) {
						$( this ).siblings( ':checkbox' ).prop( 'checked', false );
					}
				}
			);
			$( document ).on(
				'change',
				'.woo-cust-headers-chk',
				function(e) {
					if ($( this ).is( ":checked" )) {
						$( this ).siblings( ':checkbox' ).prop( 'checked', true );
					} else if ($( this ).is( ":not(:checked)" )) {
						$( this ).siblings( ':checkbox' ).prop( 'checked', false );
					}
				}
			);
			$( document ).on(
				'change',
				'.woo-coupon-headers-chk',
				function(e) {
					if ($( this ).is( ":checked" )) {
						$( this ).siblings( ':checkbox' ).prop( 'checked', true );
					} else if ($( this ).is( ":not(:checked)" )) {
						$( this ).siblings( ':checkbox' ).prop( 'checked', false );
					}
				}
			);
			$( document ).on(
				'change',
				'.woo-event-headers-chk',
				function(e) {
					if ($( this ).is( ":checked" )) {
						$( this ).siblings( ':checkbox' ).prop( 'checked', true );
					} else if ($( this ).is( ":not(:checked)" )) {
						$( this ).siblings( ':checkbox' ).prop( 'checked', false );
					}
				}
			);
			$( document ).on(
				'change',
				'#product_settings_checkbox',
				function(e) {
					if ($( this ).is( ":checked" )) {
						$( ".prd_spreadsheet_inputrow" ).removeClass( 'prd_spreadsheet_row' );
						$( ".prd_spreadsheet_row" ).fadeIn();
						var product_spreadsheet = $( '#product_spreadsheet' ).val();
						if (String( product_spreadsheet ) === 'new' ) {
							$( ".prd_spreadsheet_inputrow" ).show();
						}
					} else if ($( this ).is( ":not(:checked)" )) {
						$( ".prd_spreadsheet_inputrow" ).addClass( 'prd_spreadsheet_row' );
						$( ".prd_spreadsheet_row" ).fadeOut();
					}
				}
			);
			$( document ).on(
				'change',
				'#customer_settings_checkbox',
				function(e) {
					if ($( this ).is( ":checked" )) {
						$( ".cust_spreadsheet_inputrow" ).removeClass( 'cust_spreadsheet_row' );
						$( ".cust_spreadsheet_row" ).fadeIn();
						var customer_spreadsheet = $( '#customer_spreadsheet' ).val();
						if (String( customer_spreadsheet ) === 'new' ) {
							$( ".cust_spreadsheet_inputrow" ).show();
						}
					} else if ($( this ).is( ":not(:checked)" )) {
						$( ".cust_spreadsheet_inputrow" ).addClass( 'cust_spreadsheet_row' );
						$( ".cust_spreadsheet_row" ).fadeOut();
					}
				}
			);
			$( document ).on(
				'change',
				'#coupon_settings_checkbox',
				function(e) {
					if ($( this ).is( ":checked" )) {
						$( ".coupon_spreadsheet_inputrow" ).removeClass( 'coupon_spreadsheet_row' );
						$( ".coupon_spreadsheet_row" ).fadeIn();
						var coupon_spreadsheet = $( '#coupon_spreadsheet' ).val();
						if (String( coupon_spreadsheet ) === 'new' ) {
							$( ".coupon_spreadsheet_inputrow" ).show();
						}
					} else if ($( this ).is( ":not(:checked)" )) {
						$( ".coupon_spreadsheet_inputrow" ).addClass( 'coupon_spreadsheet_row' );
						$( ".coupon_spreadsheet_row" ).fadeOut();
					}
				}
			);
			$( document ).on(
				'change',
				'#event_settings_checkbox',
				function(e) {
					if ($( this ).is( ":checked" )) {
						$( ".event_spreadsheet_row" ).fadeIn();
					} else if ($( this ).is( ":not(:checked)" )) {
						$( ".event_spreadsheet_row" ).fadeOut();
					}
				}
			);
			$( '#category_select' ).on(
				'change',
				function() {
					if (this.checked) {
						$( '.td-prdcat-wpssw' ).fadeIn();
					} else {
						$( '.td-prdcat-wpssw' ).fadeOut();
					}
				}
			);
			$( '#product_category_select' ).on(
				'change',
				function() {
					if (this.checked) {
						$( '.td-producat-wpssw' ).fadeIn();
					} else {
						$( '.td-producat-wpssw' ).fadeOut();
					}
				}
			);
			$( document ).on(
				'change',
				'#color_code',
				function(e) {
					if (this.checked) {
						$( '#color_selection' ).fadeIn();
					} else {
						$( '#color_selection' ).fadeOut();
					}
				}
			);
			// Validate newsheet name.
			$( '#mainform' ).on(
				'submit',
				function(){
					var isFormValid = true;
					var newRequest  = $( '#woocommerce_spreadsheet' ).val();
					if ( String( newRequest ) === '' || parseInt( newRequest ) === 0 ) {
						alert( 'Please Select Spreadsheet.' );
						$( 'html, body' ).animate(
							{
								scrollTop:0
							},
							1200
						);
						$( '#woocommerce_spreadsheet' ).focus();
						return false;
					}
					var headerformat = $( 'input[type=radio][name=header_format]:checked' ).val();
					if ( String( headerformat ) === '' || typeof headerformat === "undefined" ) {
						alert( 'Please select header format.' );
						$( 'html, body' ).animate(
							{
								scrollTop:$( "#header_format" ).first().offset().top - 140
							},
							1200
						);
						$( '#header_format' ).focus();
						return false;
					}
					if (parseInt( $( '.wpssw-section-2' ).find( 'input[type=checkbox]:checked' ).length ) === 0) {
						alert( 'Please select at least one order status to get it work in spreadsheet' );
						$( 'html, body' ).animate(
							{
								scrollTop:$( ".wpssw-section-2" ).first().offset().top - 140
							},
							1200
						);
						setTimeout( function(){ $( ".wpssw-section-2" ).first().css( "border", "1px solid #ff5859" ); }, 1000 );
						$( '.wpssw-section-2' ).first().focus();
						return false;
					}

					if (parseInt( $( '#sortable' ).find( 'input[type=checkbox]:checked' ).length ) <= 4) {
						var count = 0;
						if ($( "#update_order_checkbox" ).is( ':checked' )) {
							count = count + 2;
						}
						if ($( "#delete_order_checkbox" ).is( ':checked' )) {
							count = count + 2;
						}
						var length = parseInt( $( '#sortable' ).find( 'input[type=checkbox]:checked' ).length );
						if (length == count) {
							alert( 'Please select at least one sheet headers to get it work in spreadsheet' );
							$( 'html, body' ).animate(
								{
									scrollTop:$( "#sortable" ).offset().top - 140
								},
								1200
							);
							setTimeout( function(){ $( "#sortable" ).css( "border", "1px solid #ff5859" ); }, 1000 );
							$( '#sortable' ).focus();
							return false;
						}
					}
					if (String( newRequest ) === 'new') {
						if (parseInt( $( '#spreadsheetname' ).val().length ) === 0) {
							$( '#newsheet' ).addClass( 'highlight' );
							isFormValid = false;
						} else {
							$( this ).removeClass( 'highlight' );
						}
						if ( ! isFormValid) {
							alert( 'Please enter Spreadsheet Name' );
							$( 'html, body' ).animate(
								{
									scrollTop:0
								},
								1200
							);
							$( '#spreadsheetname' ).focus();
						}
						return isFormValid;
					}
					var scheduling_enable = $( 'input[name="scheduling_enable"]:checked' ).val();
					if (scheduling_enable == "1") {
						var scheduling_run_on = $( 'input[name="scheduling_run_on"]:checked' ).val();
						if ( scheduling_run_on == 'weekly' ) {
							var weekly_days = $( '#weekly_days' ).val();
							if ( weekly_days == '' ) {
								alert( 'Schedule Auto Sync:  Please select week day.' );
								$( 'html, body' ).animate(
									{
										scrollTop:$( "#weekly" ).offset().top - 160
									},
									1200
								);
								return false;
							}
						}
						if ( scheduling_run_on == 'onetime' ) {
							var scheduling_date = $( 'input[name="scheduling_date"]' ).val();
							if ( ! scheduling_date ) {
								$( 'html, body' ).animate(
									{
										scrollTop:$( "#onetime" ).offset().top - 160
									},
									1200
								);
								alert( 'Schedule Auto Sync:  Please select date.' );
								return false;
							}
							var scheduling_time = $( 'input[name="scheduling_time"]' ).val();
							if ( ! scheduling_time ) {
								$( 'html, body' ).animate(
									{
										scrollTop:$( "#onetime" ).offset().top - 160
									},
									1200
								);
								alert( 'Schedule Auto Sync:  Please select time.' );
								return false;
							}
						}
					}
					if ($( "#import_order_checkbox" ).is( ':checked' )) {
						if ($( "#update_order_checkbox" ).is( ':checked' ) || $( "#delete_order_checkbox" ).is( ':checked' )) {
						} else {
							alert( 'Please make sure you have enable one of these options, update order or delete order' );
							return false;
						}
					}
				}
			);
			// Validate newsheet name.
			$( '#productform' ).on(
				'submit',
				function(){
					var is_enable = $( "#product_settings_checkbox" ).is( ':checked' );
					if ( ! is_enable ) {
					} else {
						var isFormValid = true;
						var newRequest  = $( '#product_spreadsheet' ).val();
						if ( String( newRequest ) === '' || parseInt( newRequest ) === 0 ) {
							alert( 'Please Select Spreadsheet.' );
							$( 'html, body' ).animate(
								{
									scrollTop:0
								},
								1200
							);
							$( '#product_spreadsheet' ).focus();
							return false;
						}
						if (String( newRequest ) === 'new') {
							if (parseInt( $( '#product_spreadsheet_name' ).val().length ) === 0) {
								$( '.prd_spreadsheet_inputrow' ).addClass( 'highlight' );
								isFormValid = false;
							} else {
								$( this ).removeClass( 'highlight' );
							}
							if ( ! isFormValid) {
								alert( 'Please enter Spreadsheet Name' );
								$( 'html, body' ).animate(
									{
										scrollTop:0
									},
									1200
								);
								$( '#product_spreadsheet_name' ).focus();
							}
							return isFormValid;
						}
						if (parseInt( $( '#woo-product-sortable' ).find( 'input[type=checkbox]:checked' ).length ) <= 6) {
							var count = 0;
							if ($( "#insert_checkbox" ).is( ':checked' )) {
								count = count + 2;
							}
							if ($( "#update_checkbox" ).is( ':checked' )) {
								count = count + 2;
							}
							if ($( "#delete_checkbox" ).is( ':checked' )) {
								count = count + 2;
							}
							var length = parseInt( $( '#woo-product-sortable' ).find( 'input[type=checkbox]:checked' ).length );
							if (length == count) {
								alert( 'Please select at least one sheet headers to get it work in spreadsheet' );
								$( 'html, body' ).animate(
									{
										scrollTop:$( "#woo-product-sortable" ).offset().top - 140
									},
									1200
								);
								setTimeout( function(){ $( "#woo-product-sortable" ).css( "border", "1px solid #ff5859" ); }, 1000 );
								$( '#woo-product-sortable' ).focus();
								return false;
							}
						}
						if ($( "#import_checkbox" ).is( ':checked' )) {
							if ($( "#insert_checkbox" ).is( ':checked' ) || $( "#update_checkbox" ).is( ':checked' ) || $( "#delete_checkbox" ).is( ':checked' )) {
							} else {
								alert( 'Please make sure you have enable one of these options, insert product, update product or delete product' );
								return false;
							}
						}
					}
				}
			);
			// Validate newsheet name.
			$( '#customerform' ).on(
				'submit',
				function(){
					var is_enable = $( "#customer_settings_checkbox" ).is( ':checked' );
					if ( ! is_enable ) {
					} else {
						var isFormValid = true;
						var newRequest  = $( '#customer_spreadsheet' ).val();
						if ( String( newRequest ) === '' || parseInt( newRequest ) === 0 ) {
							alert( 'Please Select Spreadsheet.' );
							$( 'html, body' ).animate(
								{
									scrollTop:0
								},
								1200
							);
							$( '#customer_spreadsheet' ).focus();
							return false;
						}
						if (String( newRequest ) === 'new') {
							if (parseInt( $( '#customer_spreadsheet_name' ).val().length ) === 0) {
								$( '.cust_spreadsheet_inputrow' ).addClass( 'highlight' );
								isFormValid = false;
							} else {
								$( this ).removeClass( 'highlight' );
							}
							if ( ! isFormValid ) {
								alert( 'Please enter Spreadsheet Name' );
								$( 'html, body' ).animate(
									{
										scrollTop:0
									},
									1200
								);
								$( '#customer_spreadsheet_name' ).focus();
								return false;
							}
						}
						if (parseInt( $( '#woo-customer-sortable' ).find( 'input[type=checkbox]:checked' ).length ) === 0) {
							alert( 'Please select at least one sheet headers to get it work in spreadsheet' );
							$( 'html, body' ).animate(
								{
									scrollTop:$( "#woo-customer-sortable" ).offset().top - 140
								},
								1200
							);
							setTimeout( function(){ $( "#woo-customer-sortable" ).css( "border", "1px solid #ff5859" ); }, 1000 );
							$( '#woo-customer-sortable' ).focus();
							return false;
						}
						return isFormValid;
					}
				}
			);
			// Validate newsheet name.
			$( '#couponform' ).on(
				'submit',
				function(){
					var is_enable = $( "#coupon_settings_checkbox" ).is( ':checked' );
					if ( ! is_enable ) {
					} else {
						var isFormValid = true;
						var newRequest  = $( '#coupon_spreadsheet' ).val();
						if ( String( newRequest ) === '' || parseInt( newRequest ) === 0 ) {
							alert( 'Please Select Spreadsheet.' );
							$( 'html, body' ).animate(
								{
									scrollTop:0
								},
								1200
							);
							$( '#coupon_spreadsheet' ).focus();
							return false;
						}
						if (String( newRequest ) === 'new') {
							if (parseInt( $( '#coupon_spreadsheet_name' ).val().length ) === 0) {
								$( '.coupon_spreadsheet_inputrow' ).addClass( 'highlight' );
								isFormValid = false;
							} else {
								$( this ).removeClass( 'highlight' );
							}
							if ( ! isFormValid ) {
								alert( 'Please enter Spreadsheet Name' );
								$( 'html, body' ).animate(
									{
										scrollTop:0
									},
									1200
								);
								$( '#coupon_spreadsheet_name' ).focus();
								return false;
							}
						}
						if (parseInt( $( '#woo-coupon-sortable' ).find( 'input[type=checkbox]:checked' ).length ) === 0) {
							alert( 'Please select at least one sheet headers to get it work in spreadsheet' );
							$( 'html, body' ).animate(
								{
									scrollTop:$( "#woo-coupon-sortable" ).offset().top - 140
								},
								1200
							);
							setTimeout( function(){ $( "#woo-coupon-sortable" ).css( "border", "1px solid #ff5859" ); }, 1000 );
							$( '#woo-coupon-sortable' ).focus();
							return false;
						}
						return isFormValid;
					}
				}
			);
			$( '#eventform' ).on(
				'submit',
				function(){
					var isEnable = $( "#event_settings_checkbox" ).is( ':checked' );
					if ( ! isEnable ) {
					} else {
						var isFormValid = true;
						var newRequest  = $( '#woocommerce_spreadsheet' ).val();
						if ( String( newRequest ) === '' || parseInt( newRequest ) === 0 ) {
							alert( 'Please Select Spreadsheet in general settings.' );
							$( 'html, body' ).animate(
								{
									scrollTop:0
								},
								1200
							);
							return false;
						}
						if (parseInt( $( '#woo-event-sortable' ).find( 'input[type=checkbox]:checked' ).length ) === 0) {
							alert( 'Please select at least one sheet headers to get it work in spreadsheet' );
							$( 'html, body' ).animate(
								{
									scrollTop:$( "#woo-event-sortable" ).offset().top - 140
								},
								1200
							);
							setTimeout( function(){ $( "#woo-event-sortable" ).css( "border", "1px solid #ff5859" ); }, 1000 );
							$( '#woo-event-sortable' ).focus();
							return false;
						}
					}
				}
			);
			$( '#product_spreadsheet' ).on(
				'change',
				function() {
					var newrequest = $( '#product_spreadsheet' ).val();
					if (newrequest == 'new') {
						$( ".prd_spreadsheet_inputrow" ).fadeIn();
						$( '#prodsynctr' ).hide();
						$( '.prd_import_row' ).hide();
						$( '.wpssw_crud_row' ).hide();
					} else {
						$( ".prd_spreadsheet_inputrow" ).fadeOut();
						if (String( newrequest ) === '' || parseInt( newrequest ) === 0) {
							$( '#prodsynctr' ).hide();
							$( '.prd_import_row' ).hide();
							$( '.wpssw_crud_row' ).hide();
						} else {
							$( '#prodsynctr' ).show();
							$( '.prd_import_row' ).show();
							if ($( "#import_checkbox" ).is( ':checked' )) {
								$( '.wpssw_crud_row' ).show();
							} else {
								$( '.wpssw_crud_row' ).hide();
							}
						}
					}
				}
			);

			$( '#customer_spreadsheet' ).on(
				'change',
				function() {
					var newrequest = $( '#customer_spreadsheet' ).val();
					if (newrequest == 'new') {
						$( ".cust_spreadsheet_inputrow" ).fadeIn();
					} else {
						$( ".cust_spreadsheet_inputrow" ).fadeOut();
					}
				}
			);
			$( '#event_spreadsheet' ).on(
				'change',
				function() {
					var newrequest = $( '#event_spreadsheet' ).val();
					if (newrequest == 'new') {
						$( ".event_spreadsheet_inputrow" ).fadeIn();
					} else {
						$( ".event_spreadsheet_inputrow" ).fadeOut();
					}
				}
			);
			$( '#coupon_spreadsheet' ).on(
				'change',
				function() {
					var newrequest = $( '#coupon_spreadsheet' ).val();
					if (newrequest == 'new') {
						$( ".coupon_spreadsheet_inputrow" ).fadeIn();
					} else {
						$( ".coupon_spreadsheet_inputrow" ).fadeOut();
					}
				}
			);
		}
	);
	$( document ).ready(
		function(){
			$( "#reset_settings" ).on(
				"click",
				function(e){
					e.preventDefault();
					jQuery.ajax(
						{
							url : admin_ajax_object.ajaxurl,
							type : 'post',
							data :"action=wpssw_reset_settings",
							beforeSend:function(){
								if (confirm( "It will unselect all spreadsheets from all settings tabs, so you need to set them up again. Are you sure you want to reset settings?" )) {
								} else {
									return false;
								}
							},
							success : function( response ) {
								if (String( response ) === 'successful') {
									location.reload();
								} else {
									alert( response );
								}
							},
							error: function (s) {
								alert( 'Error' );
							}
						}
					);
				}
			);
			$( "#sync" ).on(
				'click',
				function(e){
					doAjax();
				}
			);
			$( "#prodsync" ).on(
				'click',
				function(e){
					$( '#prodsyncloader' ).show();
					$( '#prodsynctext' ).show();
					$( this ).hide();
					doProductAjax();
				}
			);
			$( "#custsync" ).on(
				'click',
				function(e){
					$( '#custsyncloader' ).show();
					$( '#custsynctext' ).show();
					$( this ).hide();
					doCustomerAjax();
				}
			);
			$( "#couponsync" ).on(
				'click',
				function(e){
					$( '#couponsyncloader' ).show();
					$( '#couponsynctext' ).show();
					$( this ).hide();
					doCouponAjax();
				}
			);
			$( "#sync_event" ).on(
				'click',
				function(e){
					$( '#eventsyncloader' ).show();
					$( '#eventsynctest' ).show();
					$( this ).hide();
					doEventAjax();
				}
			);
			$( "#importsync" ).on(
				'click',
				function(e){
					if ( confirm( "Are you sure?" ) ) {
						$( '#importsyncloader' ).show();
						$( '#importsynctext' ).show();
						$( this ).hide();
						doImportAjax();
					} else {
						return false;
					}
				}
			);
			$( "#importsyncbtm" ).on(
				'click',
				function(e){
					$( '#importsyncloader' ).show();
					$( '#importsynctext' ).html( 'Updating...' );
					$( '#importsynctext' ).show();
					$( '#cancelsyncbtm' ).hide();
					$( this ).hide()
					doImportproductsAjax();
				}
			);
			$( "#cancelsyncbtm" ).on(
				'click',
				function(e){
					$( this ).hide();
					$( '#importsyncbt' ).hide();
					$( '#importsynctext' ).html( 'Checking...' );
					$( '#importsynctext' ).hide();
					$( '#importsyncbtm' ).hide();
					document.getElementById( "importsync" ).style.display = "inline-block";
				}
			);
			$( "#ordimportsync" ).on(
				'click',
				function(e){
					if ( confirm( "Are you sure?" ) ) {
						$( '#ordimportsyncloader' ).show();
						$( '#ordimportsynctext' ).show();
						$( this ).hide();
						doOrderAjax();
					} else {
						return false;
					}
				}
			);
			$( "#ordimportsyncbtm" ).on(
				'click',
				function(e){
					$( '#ordimportsyncloader' ).show();
					$( '#ordimportsynctext' ).html( 'Updating...' );
					$( '#ordimportsynctext' ).show();
					$( '#ordcancelsyncbtm' ).hide();
					$( this ).hide()
					doImportordersAjax();
				}
			);
			$( "#ordcancelsyncbtm" ).on(
				'click',
				function(e){
					$( this ).hide();
					$( '#ordimportsyncbt' ).hide();
					$( '#ordimportsynctext' ).html( 'Checking...' );
					$( '#ordimportsynctext' ).hide();
					$( '#ordimportsyncbtm' ).hide();
					document.getElementById( "ordimportsync" ).style.display = "inline-block";
				}
			);
			$( "#regenerate_total_graph" ).on(
				'click',
				function(e){
					var id            = $( this ).attr( 'id' );
					var chartLoaderId = $( '#' + id ).next().attr( "id" );
					var graphType     = $( 'input[name="total_orders_graph_type"]:checked' ).val();
					addRegenerateGraph( id,chartLoaderId,graphType );
				}
			);
			$( "#regenerate_sales_graph" ).on(
				'click',
				function(e){
					var id            = $( this ).attr( 'id' );
					var chartLoaderId = $( '#' + id ).next().attr( "id" );
					var graphType     = $( 'input[name="sales_orders_graph_type"]:checked' ).val();
					addRegenerateGraph( id,chartLoaderId,graphType );
				}
			);
			$( "#regenerate_product_graph" ).on(
				'click',
				function(e){
					var id            = $( this ).attr( 'id' );
					var chartLoaderId = $( '#' + id ).next().attr( "id" );
					var graphType     = $( 'input[name="products_sold_graph_type"]:checked' ).val();
					addRegenerateGraph( id,chartLoaderId,graphType );
				}
			);
			$( "#regenerate_customers_graph" ).on(
				'click',
				function(e){
					var id            = $( this ).attr( 'id' );
					var chartLoaderId = $( '#' + id ).next().attr( "id" );
					var graphType     = $( 'input[name="total_customers_graph_type"]:checked' ).val();
					addRegenerateGraph( id,chartLoaderId,graphType );
				}
			);
			$( "#regenerate_used_coupons_graph" ).on(
				'click',
				function(e){
					var id            = $( this ).attr( 'id' );
					var chartLoaderId = $( '#' + id ).next().attr( "id" );
					var graphType     = $( 'input[name="total_used_coupons_graph_type"]:checked' ).val();
					addRegenerateGraph( id,chartLoaderId,graphType );
				}
			);
			if ($( '#sales_orders_graph' ).is( ":checked" )) {
				$( ".sales-tdclass" ).removeClass( 'disabled-regenerate-graph' );
				$( "input[type=radio][name=sales_orders_graph_type]" ).prop( "required",true );
			} else {
				$( ".sales-tdclass" ).addClass( 'disabled-regenerate-graph' );
			}
			if ($( '#total_orders_graph' ).is( ":checked" )) {
				$( ".total-tdclass" ).removeClass( 'disabled-regenerate-graph' );
				$( "input[type=radio][name=total_orders_graph_type]" ).prop( "required",true );
			} else {
				$( ".total-tdclass" ).addClass( 'disabled-regenerate-graph' );
			}
			if ($( '#products_sold_graph' ).is( ":checked" )) {
				$( ".product-tdclass" ).removeClass( 'disabled-regenerate-graph' );
				$( "input[type=radio][name=products_sold_graph_type]" ).prop( "required",true );
			} else {
				$( ".product-tdclass" ).addClass( 'disabled-regenerate-graph' );
			}
			if ($( '#total_customers_graph' ).is( ":checked" )) {
				$( '.customers-tdclass' ).removeClass( 'disabled-regenerate-graph' );
				$( "input[type=radio][name=total_customers_graph_type]" ).prop( "required",true );
			} else {
				$( '.customers-tdclass' ).addClass( 'disabled-regenerate-graph' );
			}
			if ($( '#total_used_coupons_graph' ).is( ":checked" )) {
				$( ".used-coupons-tdclass" ).removeClass( 'disabled-regenerate-graph' );
				$( "input[type=radio][name=total_used_coupons_graph_type]" ).prop( "required",true );
			} else {
				$( ".used-coupons-tdclass" ).addClass( 'disabled-regenerate-graph' );
			}
			$( 'input[name="graphsheets_list[]"][type=checkbox]' ).change(
				function(){
					var graphId   = $( this ).attr( 'id' );
					var name      = $( this ).attr( 'class' );
					var className = '' + name + '-tdclass';
					if ($( '#' + graphId ).is( ":checked" )) {
						$( '.' + className ).removeClass( 'disabled-regenerate-graph' );
						$( "input[type=radio][name=" + graphId + "_type]" ).prop( "required",true );
					} else {
						$( '.' + className ).addClass( 'disabled-regenerate-graph' );
						$( "input[type=radio][name=" + graphId + "_type]" ).prop( "required",false );
					}
				}
			);
			function doProductAjax(args) {
				var productnonce = $( '#wpssw_product_settings' ).val();
				$.ajax(
					{
						url : admin_ajax_object.ajaxurl,
						type : 'post',
						data : 'action=wpssw_get_product_count&wpssw_product_settings=' + productnonce,
						success : function( response ) {
							try {
								obj               = JSON.parse( response );
								var totalproducts = obj.totalproducts;
								if ( totalproducts > 0 ) {
									if ( parseInt( totalproducts ) < 2000 ) {
										if ( parseInt( totalproducts ) > 50 ) {
											totalproducts = 50;
										}
									}
									syncProductData( totalproducts );
								} else {
									alert( 'All Products are synchronize successfully' );
									displayproductsync();
								}
							} catch (e) {
								alert( response );
								displayproductsync();
							}
						}
					}
				)
				.fail(
					function() {
						alert( 'Error' );
						$( '#prodsyncloader' ).hide();
						$( '#prodsynctext' ).hide();
						document.getElementById( "prodsync" ).style.display = "inline-block";
					}
				);
			}
			function displayproductsync(){
				$( '#prodsyncloader' ).hide();
				$( '#prodsynctext' ).hide();
				$( '#prodsynctext' ).html( 'Synchronizing...' );
				document.getElementById( "prodsync" ).style.display = "inline-block";
			}
			function doImportAjax(args) {
				$.ajax(
					{
						url : admin_ajax_object.ajaxurl,
						type : 'post',
						data :"action=wpssw_get_product_import_count",
						success : function( response ) {
							obj     = JSON.parse( response );
							var msg = [];
							if ( "insertproducts" in obj ) {
								msg.push( 'Insert Product - ' + obj.insertproducts );
							}
							if ( "updateproducts" in obj ) {
								msg.push( 'Update Product - ' + obj.updateproducts );
							}
							if ( "deleteproducts" in obj ) {
								msg.push( 'Delete Product - ' + obj.deleteproducts );
							}
							if ( msg.length === 0 ) {
								alert( 'To perform the import function, make sure you have added 1 to the respective columns of the spreadsheet as per the documentation.' );
								$( '#importsyncloader' ).hide();
								$( '#importsynctext' ).	hide();
								$( "#importsync" ).show();
							} else {
								document.getElementById( "importsyncbtm" ).style.display = "inline-block";
								document.getElementById( "cancelsyncbtm" ).style.display = "inline-block";
								$( '#importsyncloader' ).hide();
								$( '#importsynctext' ).html( '<b>' + msg.join( "<br>" ) + '</b>' );
							}
						},
						error: function (s) {
							alert( 'Error' );
							$( '#importsyncloader' ).hide();
							$( '#importsynctext' ).hide();

							document.getElementById( "importsync" ).style.display = "inline-block";
						}
					}
				)
				.fail(
					function() {
						alert( 'Error' );
						$( '#importsyncloader' ).hide();
						$( '#importsynctext' ).hide();
						document.getElementById( "importsync" ).style.display = "inline-block";
					}
				);
			}
			function doImportproductsAjax() {
				$.ajax(
					{
						url : admin_ajax_object.ajaxurl,
						type : 'post',
						data :"action=wpssw_product_import",
						success : function( response ) {
							if (response == 'successful') {
								alert( 'All products are imported successfully' );
								$( '#importsyncloader' ).hide();
								$( '#importsynctext' ).hide();
								$( '#importsynctext' ).html( 'Synchronizing...' );
								document.getElementById( "importsync" ).style.display = "inline-block";
							} else if (response == 'addproductname') {
								alert( 'Please add product name to insert product' );
								$( '#importsyncloader' ).hide();
								$( '#importsynctext' ).hide();
								document.getElementById( "importsync" ).style.display = "inline-block";
							} else if (response == 'addproductnamecolumn') {
								alert( 'Please enable Product Name header and add product name to insert product' );
								$( 'html, body' ).animate(
									{
										scrollTop:$( "#sortable" ).offset().top - 140
									},
									1200
								);
								setTimeout( function(){ $( "#sortable" ).css( "border", "1px solid #ff5859" ); }, 1000 );
								$( '#sortable' ).focus();
								$( '#importsyncloader' ).hide();
								$( '#importsynctext' ).hide();
								document.getElementById( "importsync" ).style.display = "inline-block";
							} else if (response == 'productIdexist') {
								alert( 'Please make sure you have removed Product Id from 1st column of spreadsheet. For more details check FAQ no. 9' );
								$( '#importsyncloader' ).hide();
								$( '#importsynctext' ).hide();
								document.getElementById( "importsync" ).style.display = "inline-block";
							} else if (response == 'addproductId') {
								alert( 'Please make sure you have added Product Id in 1st column of spreadsheet.' );
								$( '#importsyncloader' ).hide();
								$( '#importsynctext' ).hide();
								document.getElementById( "importsync" ).style.display = "inline-block";
							} else if (response == 'productnameexist') {
								alert( 'Product with similar name is already exists so please use different Product Name to insert new product' );
								$( '#importsyncloader' ).hide();
								$( '#importsynctext' ).hide();
								document.getElementById( "importsync" ).style.display = "inline-block";
							} else {
								alert( 'Your Google Sheets API limit has been reached. Please take a look at our FAQ.' );
								$( '#importsyncloader' ).hide();
								$( '#importsynctext' ).hide();
								document.getElementById( "importsync" ).style.display = "inline-block";
							}
						},
						complete: function(){
							var totalproducts = obj.totalproducts;
							if ( parseInt( totalproducts ) < 2000 ) {
								if ( parseInt( totalproducts ) > 50 ) {
									totalproducts = 50;
								}
							} else {
								totalproducts = 500;
							}
							if ( totalproducts > orderCount ) {
								setTimeout(
									function(){
									},
									2000
								);
							}
						}
					}
				)
				.fail(
					function() {
						alert( 'Error' );
						$( '#importsyncloader' ).hide();
						$( '#importsynctext' ).hide();
						document.getElementById( "importsync" ).style.display = "inline-block";
					}
				);
			}
			var order_import_obj;
			var order_import_sheet = 0;
			function doOrderAjax(args) {
				$.ajax(
					{
						url : admin_ajax_object.ajaxurl,
						type : 'post',
						data :"action=wpssw_get_order_import_count",
						success : function( response ) {
							if (String( response ) === 'spreadsheetnotexist') {
								alert( 'Please save your settings first and try again.' );
								$( '#ordimportsyncloader' ).hide();
								$( '#ordimportsynctext' ).hide();
								document.getElementById( "ordimportsync" ).style.display = "inline-block";
								$( 'html, body' ).animate(
									{
										scrollTop:$( 'html, body' ).get( 0 ).scrollHeight
									},
									2000
								);
								return false;
							} else if (String( response ) === 'sheetnotexist') {
								alert( 'Selected Order status sheet is not present in your spreadsheet so to import orders first save your settings and try again.' );
								$( '#ordimportsyncloader' ).hide();
								$( '#ordimportsynctext' ).hide();
								document.getElementById( "ordimportsync" ).style.display = "inline-block";
								$( 'html, body' ).animate(
									{
										scrollTop:$( 'html, body' ).get( 0 ).scrollHeight
									},
									2000
								);
								return false;
							}

							order_import_obj = JSON.parse( response );
							totalSheet       = Object.keys( order_import_obj ).length;
							var msg          = [];
							if ( totalSheet > 0 ) {
								$.each(
									order_import_obj,
									function( key, value ) {
										var sheetName = value['sheet_name'];

										if ( "updateorders" in value || "deleteorders" in value ) {
											msg.push( sheetName );
										}
										if ( "updateorders" in value ) {
											msg.push( 'Update Order - ' + value.updateorders );
										}
										if ( "deleteorders" in value ) {
											msg.push( 'Delete Order - ' + value.deleteorders );
										}
									}
								);
								if ( msg.length === 0 ) {
									alert( 'To perform the import function, make sure you have added 1 to the respective columns of the spreadsheet as per the documentation.' );
									$( '#ordimportsyncloader' ).hide();
									$( '#ordimportsynctext' ).	hide();
									$( "#ordimportsync" ).show();
								} else {
									document.getElementById( "ordimportsyncbtm" ).style.display = "inline-block";
									document.getElementById( "ordcancelsyncbtm" ).style.display = "inline-block";
									$( '#ordimportsyncloader' ).hide();
									$( '#ordimportsynctext' ).html( '<b>' + msg.join( "<br>" ) + '</b>' );
								}
							} else {
								alert( 'All Orders are imported successfully' );
								$( '#ordimportsyncloader' ).hide();
								$( '#ordimportsynctext' ).	hide();
								$( "#ordimportsync" ).show();
							}
						},
						error: function (s) {
							alert( 'Error' );
							$( '#ordimportsyncloader' ).hide();
							$( '#ordimportsynctext' ).hide();

							document.getElementById( "ordimportsync" ).style.display = "inline-block";
						}
					}
				)
				.fail(
					function() {
						alert( 'Error' );
						$( '#ordimportsyncloader' ).hide();
						$( '#ordimportsynctext' ).hide();
						document.getElementById( "ordimportsync" ).style.display = "inline-block";
					}
				);
			}

			function doImportordersAjax() {
				totalSheet = Object.keys( order_import_obj ).length;
				if ( totalSheet > 0 ) {
					var sheetName = order_import_obj[order_import_sheet]['sheet_name'];
					importOrderData( sheetName );
				} else {
					alert( 'All Orders are synchronize successfully' );
					$( '#ordimportsyncloader' ).hide();
					$( '#ordimportsynctext' ).hide();
					document.getElementById( "ordimportsync" ).style.display = "inline-block";
				}
			}
			function importOrderData( sheetName){
				var wpsswGeneralSettings = $( '#wpssw_general_settings' ).val();
				$.ajax(
					{
						url : admin_ajax_object.ajaxurl,
						type : 'post',
						data :"action=wpssw_order_import&sheetname=" + sheetName + "&wpssw_general_settings=" + wpsswGeneralSettings,
						success : function( response ) {
							if ( parseInt( totalSheet ) === ( order_import_sheet + 1 )) {
								if (String( response ) === 'successful') {
									alert( 'All Orders are synchronize successfully' );
									$( '#ordimportsyncloader' ).hide();
									$( '#ordimportsynctext' ).hide();

									document.getElementById( "ordimportsync" ).style.display = "inline-block";
								} else if (response == 'ordernotexists') {
									alert( 'This order is not exists.' );
									$( '#ordimportsyncloader' ).hide();
									$( '#ordimportsynctext' ).hide();
									document.getElementById( "ordimportsync" ).style.display = "inline-block";
								} else if (response == 'addorderId') {
									alert( 'Please add order id to update order.' );
									$( '#ordimportsyncloader' ).hide();
									$( '#ordimportsynctext' ).hide();
									document.getElementById( "ordimportsync" ).style.display = "inline-block";
								} else if (response == 'orderintrash') {
									alert( 'You can’t edit this order because it is in the Trash. Please restore it and try again.' );
									$( '#ordimportsyncloader' ).hide();
									$( '#ordimportsynctext' ).hide();
									document.getElementById( "ordimportsync" ).style.display = "inline-block";
								} else {
									alert( 'Your Google Sheets API limit has been reached. Please take a look at our FAQ.' );
									$( '#ordimportsyncloader' ).hide();
									$( '#ordimportsynctext' ).hide();
									document.getElementById( "ordimportsync" ).style.display = "inline-block";
								}
							}
						},
						complete: function(){

							if ( totalSheet > order_import_sheet + 1 ) {
								order_import_sheet = order_import_sheet + 1;
								orderCount         = 0;
								nextSheet          = 1;
								var sheetName      = order_import_obj[order_import_sheet]['sheet_name'];
								setTimeout(
									function(){
										importOrderData( sheetName );
									},
									2000
								);
							}

						}
					}
				)
				.fail(
					function() {
						alert( 'Error' );
						$( '#ordimportsyncloader' ).hide();
						$( '#ordimportsynctext' ).hide();
						document.getElementById( "ordimportsync" ).style.display = "inline-block";
					}
				);
			}

			function doCustomerAjax(args) {
				var customernonce = $( '#wpssw_customer_settings' ).val();
				$.ajax(
					{
						url : admin_ajax_object.ajaxurl,
						type : 'post',
						data : 'action=wpssw_get_customer_count&wpssw_customer_settings=' + customernonce,
						success : function( response ) {
							try {
								obj                = JSON.parse( response );
								var totalcustomers = obj.totalcustomers;
								if ( totalcustomers > 0 ) {
									if ( parseInt( totalcustomers ) < 2000 ) {
										if ( parseInt( totalcustomers ) > 50 ) {
											totalcustomers = 50;
										}
									}
									syncCustomerData( totalcustomers );
								} else {
									alert( 'All Customers data are synchronize successfully' );
									displaycustomersync();
								}
							} catch (e) {
								alert( response );
								displaycustomersync();
							}
						}
					}
				)
				.fail(
					function() {
						alert( 'Error' );
						$( '#custsyncloader' ).hide();
						$( '#custsynctext' ).hide();
						document.getElementById( "custsync" ).style.display = "inline-block";
					}
				);
			}
			function displaycustomersync(){
				$( '#custsyncloader' ).hide();
				$( '#custsynctext' ).hide();
				$( '#custsynctext' ).html( 'Synchronizing...' );
				document.getElementById( "custsync" ).style.display = "inline-block";
			}
			function displaycouponsync(){
				$( '#couponsyncloader' ).hide();
				$( '#couponsynctext' ).hide();
				$( '#couponsynctext' ).html( 'Synchronizing...' );
				document.getElementById( "couponsync" ).style.display = "inline-block";
			}
			function doCouponAjax(args) {
				var couponnonce = $( '#wpssw_coupon_settings' ).val();
				$.ajax(
					{
						url : admin_ajax_object.ajaxurl,
						type : 'post',
						data : 'action=wpssw_get_coupon_count&wpssw_coupon_settings=' + couponnonce,
						success : function( response ) {
							try {
								obj              = JSON.parse( response );
								var totalcoupons = obj.totalcoupons;
								if ( totalcoupons > 0 ) {
									if ( parseInt( totalcoupons ) < 2000 ) {
										if ( parseInt( totalcoupons ) > 50 ) {
											totalcoupons = 50;
										}
									}
									syncCouponData( totalcoupons );
								} else {
									alert( 'All Coupons data are synchronize successfully' );
									displaycouponsync();
								}
							} catch (e) {
								alert( response );
								displaycouponsync();
							}
						}
					}
				)
				.fail(
					function() {
						alert( 'Error' );
						$( '#couponsyncloader' ).hide();
						$( '#couponsynctext' ).hide();
						document.getElementById( "couponsync" ).style.display = "inline-block";
					}
				);
			}
			var totalSheet = 0;
			var syncSheet  = 0;
			var orderLimit = 500;
			var orderCount = 0;
			var nextSheet  = 1;
			var obj;
			function doAjax(args) {
				var wpsswGeneralSettings = $( '#wpssw_general_settings' ).val();
				var syncAll              = $( '#sync_all' ).is( ":checked" );
				var syncAllFromdate      = $( '#sync_all_fromdate' ).val();
				var syncAllTodate        = $( '#sync_all_todate' ).val();
				if (Boolean( syncAll ) === false && (String( syncAllFromdate ) === "" || String( syncAllTodate ) === "")) {
					alert( 'From Date and To Date should not be blank.' );
				} else if (syncAllFromdate > syncAllTodate) {
					alert( 'From Date should not be greater than To Date.' );
				} else {
					$( '#syncloader' ).show();
					$( '#synctext' ).show();
					$( '#sync' ).hide();
					$.ajax(
						{
							url : admin_ajax_object.ajaxurl,
							type : 'post',
							data :"action=wpssw_get_orders_count&wpssw_general_settings=" + wpsswGeneralSettings + "&sync_all_fromdate=" + syncAllFromdate + "&sync_all_todate=" + syncAllTodate + "&sync_all=" + syncAll,
							success : function( response ) {
								if ( String( response ) === 'error' ) {
									alert( 'Sorry, your nonce did not verify.' );
									$( '#syncloader' ).hide();
									$( '#synctext' ).hide();
									$( '#synctext' ).html( 'Synchronizing...' );
									document.getElementById( "sync" ).style.display = "inline-block";
								} else if (String( response ) === 'spreadsheetnotexist') {
									alert( 'Please save your settings first and try again.' );
									$( '#syncloader' ).hide();
									$( '#synctext' ).hide();
									$( '#synctext' ).html( 'Synchronizing...' );
									document.getElementById( "sync" ).style.display = "inline-block";
									$( 'html, body' ).animate(
										{
											scrollTop:$( 'html, body' ).get( 0 ).scrollHeight
										},
										2000
									);
									return false;
								} else if (String( response ) === 'sheetnotexist') {
									alert( 'Selected Order status sheet is not present in your spreadsheet so to sync orders first save your settings and try again.' );
									$( '#syncloader' ).hide();
									$( '#synctext' ).hide();
									$( '#synctext' ).html( 'Synchronizing...' );
									document.getElementById( "sync" ).style.display = "inline-block";
									$( 'html, body' ).animate(
										{
											scrollTop:$( 'html, body' ).get( 0 ).scrollHeight
										},
										2000
									);
									return false;
								} else {
									obj        = JSON.parse( response );
									totalSheet = Object.keys( obj ).length;
									if ( totalSheet > 0 ) {
										var sheetName   = obj[syncSheet]['sheet_name'];
										var sheetSlug   = obj[syncSheet]['sheet_slug'];
										var totalOrders = obj[syncSheet]['totalorders'];
										if ( parseInt( totalOrders ) < 2000 ) {
											if ( parseInt( totalOrders ) > 50 ) {
												orderLimit = 50;
											}
										}
										syncData( sheetName, sheetSlug, totalOrders ,syncAll ,syncAllFromdate, syncAllTodate );
									} else {
										alert( 'All Orders are synchronize successfully' );
										$( '#syncloader' ).hide();
										$( '#synctext' ).hide();
										$( '#synctext' ).html( 'Synchronizing...' );
										document.getElementById( "sync" ).style.display = "inline-block";
									}
								}
							}
						}
					)
					.fail(
						function() {
							alert( 'Error' );
							$( '#syncloader' ).hide();
							$( '#synctext' ).hide();
							document.getElementById( "sync" ).style.display = "inline-block";
						}
					);
				}
			}
			function syncData( sheetName, sheetSlug, totalOrders ,syncAll ,syncAllFromdate, syncAllTodate){
				if ( parseInt( totalOrders ) < 2000 ) {
					if ( parseInt( totalOrders ) > 50 ) {
						orderLimit = 50;
					}
					if ( parseInt( totalOrders ) < 50) {
						orderLimit = parseInt( totalOrders );
					}
				} else {
					orderLimit = 500;
				}
				if ( totalOrders > orderCount ) {
					orderCount = orderCount + orderLimit;
				} else if ( totalOrders < orderCount && parseInt( nextSheet ) === 1 && totalSheet != ( syncSheet + 1 ) ) {
					orderCount = orderCount + orderLimit;
					nextSheet  = 0;
				}
				if ( totalOrders > orderLimit && orderCount < totalOrders ) {
					$( '#synctext' ).html( 'Synchronizing : ' + orderCount + ' / ' + totalOrders + ' ' + sheetName );
				} else {
					$( '#synctext' ).html( 'Synchronizing : ' + totalOrders + ' / ' + totalOrders + ' ' + sheetName );
				}
				var sync_nonce_token;
				sync_nonce_token = admin_ajax_object.sync_nonce_token;
				$.ajax(
					{
						url : admin_ajax_object.ajaxurl,
						type : 'post',
						data :"action=wpssw_sync_sheetswise&sheetslug=" + sheetSlug + "&sheetname=" + sheetName + "&orderlimit=" + orderLimit + "&ordercount=" + orderCount + "&sync_all_fromdate=" + syncAllFromdate + "&sync_all_todate=" + syncAllTodate + "&sync_all=" + syncAll + "&sync_nonce_token=" + sync_nonce_token,
						success : function( response ) {
							if ( parseInt( totalSheet ) === ( syncSheet + 1 ) && totalOrders <= orderCount ) {
								if (String( response ) === 'successful') {
									alert( 'All Orders are synchronize successfully' );
									$( '#syncloader' ).hide();
									$( '#synctext' ).hide();
									$( '#synctext' ).html( 'Synchronizing...' );
									document.getElementById( "sync" ).style.display = "inline-block";
								} else {
									alert( 'Your Google Sheets API limit has been reached. Please take a look at our FAQ.' );
									$( '#syncloader' ).hide();
									$( '#synctext' ).hide();
									document.getElementById( "sync" ).style.display = "inline-block";
								}
							}
						},
						complete: function(){
							var sheetName   = obj[syncSheet]['sheet_name'];
							var sheetSlug   = obj[syncSheet]['sheet_slug'];
							var totalOrders = obj[syncSheet]['totalorders'];
							if ( totalOrders > orderCount ) {
								setTimeout(
									function(){
										syncData( sheetName, sheetSlug, totalOrders ,syncAll ,syncAllFromdate, syncAllTodate );
									},
									2000
								);
							} else if ( totalOrders < orderCount && parseInt( nextSheet ) === 1 && totalSheet != ( syncSheet + 1 ) ) {
								setTimeout(
									function(){
										syncData( sheetName, sheetSlug, totalOrders, syncAll, syncAllFromdate, syncAllTodate );
									},
									2000
								);
							} else {
								if ( totalSheet > syncSheet + 1 ) {
									syncSheet       = syncSheet + 1;
									orderCount      = 0;
									nextSheet       = 1;
									var sheetName   = obj[syncSheet]['sheet_name'];
									var sheetSlug   = obj[syncSheet]['sheet_slug'];
									var totalOrders = obj[syncSheet]['totalorders'];
									setTimeout(
										function(){
											syncData( sheetName, sheetSlug, totalOrders ,syncAll ,syncAllFromdate, syncAllTodate );
										},
										2000
									);
								}
							}
						}
					}
				)
				.fail(
					function() {
						alert( 'Error' );
						$( '#syncloader' ).hide();
						$( '#synctext' ).hide();
						document.getElementById( "sync" ).style.display = "inline-block";
					}
				);
			}
			function syncProductData( totalproducts ){
				var productnonce = $( '#wpssw_product_settings' ).val();
				if ( totalproducts > orderCount ) {
					if ( parseInt( totalproducts ) < 2000 ) {
						if ( parseInt( totalproducts ) > 50 ) {
							totalproducts = 50;
						}
					} else {
						totalproducts = 500;
					}
					orderCount = orderCount + orderLimit;
				} else if ( totalproducts < orderCount  ) {
					orderCount = orderCount + orderLimit;
				}
				if ( totalproducts > orderLimit && orderCount < totalproducts ) {
					$( '#prodsynctext' ).html( 'Synchronizing : ' + orderCount + ' / ' + totalproducts );
				} else {
					$( '#prodsynctext' ).html( 'Synchronizing : ' + totalproducts + ' / ' + totalproducts );
				}
				$.ajax(
					{
						url : admin_ajax_object.ajaxurl,
						type : 'post',
						data :"action=wpssw_sync_products&orderlimit=" + orderLimit + "&ordercount=" + orderCount + "&wpssw_product_settings=" + productnonce,
						success : function( response ) {
							if ( totalproducts <= orderCount ) {
								if (response == 'successful') {
									alert( 'All Products are synchronize successfully' );
									$( '#prodsyncloader' ).hide();
									$( '#prodsynctext' ).hide();
									$( '#prodsynctext' ).html( 'Synchronizing...' );
									document.getElementById( "prodsync" ).style.display = "inline-block";
								} else {
									alert( 'Your Google Sheets API limit has been reached. Please take a look at our FAQ.' );
									$( '#prodsyncloader' ).hide();
									$( '#prodsynctext' ).hide();
									document.getElementById( "prodsync" ).style.display = "inline-block";
								}
							}
						},
						complete: function(){
							var totalproducts = obj.totalproducts;
							if ( parseInt( totalproducts ) < 2000 ) {
								if ( parseInt( totalproducts ) > 50 ) {
									totalproducts = 50;
								}
							} else {
								totalproducts = 500;
							}
							if ( totalproducts > orderCount ) {
								setTimeout(
									function(){
										syncProductData( totalproducts );
									},
									2000
								);
							}
						}
					}
				)
				.fail(
					function() {
						alert( 'Error' );
						$( '#prodsyncloader' ).hide();
						$( '#prodsynctext' ).hide();
						document.getElementById( "prodsync" ).style.display = "inline-block";
					}
				);
			}
			function syncCustomerData( totalcustomers ){
				if ( totalcustomers > orderCount ) {
					if ( parseInt( totalcustomers ) < 2000 ) {
						if ( parseInt( totalcustomers ) > 50 ) {
							totalcustomers = 50;
						}
					} else {
						totalcustomers = 500;
					}
					orderCount = orderCount + orderLimit;
				} else if ( totalcustomers < orderCount  ) {
					orderCount = orderCount + orderLimit;
				}
				if ( totalcustomers > orderLimit && orderCount < totalcustomers ) {
					$( '#custsynctext' ).html( 'Synchronizing : ' + orderCount + ' / ' + totalcustomers );
				} else {
					$( '#custsynctext' ).html( 'Synchronizing : ' + totalcustomers + ' / ' + totalcustomers );
				}
				var customernonce = $( '#wpssw_customer_settings' ).val();
				$.ajax(
					{
						url : admin_ajax_object.ajaxurl,
						type : 'post',
						data :"action=wpssw_sync_customers&orderlimit=" + orderLimit + "&ordercount=" + orderCount + "&wpssw_customer_settings=" + customernonce,
						success : function( response ) {
							if ( totalcustomers <= orderCount ) {
								if (response == 'successful') {
									alert( 'All Customers data are synchronize successfully' );
									$( '#custsyncloader' ).hide();
									$( '#custsynctext' ).hide();
									$( '#custsynctext' ).html( 'Synchronizing...' );
									document.getElementById( "custsync" ).style.display = "inline-block";
								} else {
									alert( 'Your Google Sheets API limit has been reached. Please take a look at our FAQ.' );
									$( '#custsyncloader' ).hide();
									$( '#custsynctext' ).hide();
									document.getElementById( "custsync" ).style.display = "inline-block";
								}
							}
						},
						complete: function(){
							var totalcustomers = obj.totalcustomers;
							if ( parseInt( totalcustomers ) < 2000 ) {
								if ( parseInt( totalcustomers ) > 50 ) {
									totalcustomers = 50;
								}
							} else {
								totalcustomers = 500;
							}
							if ( totalcustomers > orderCount ) {
								setTimeout(
									function(){
										syncCustomerData( totalcustomers );
									},
									2000
								);
							}
						}
					}
				)
				.fail(
					function() {
						alert( 'Error' );
						$( '#custsyncloader' ).hide();
						$( '#custsynctext' ).hide();
						document.getElementById( "custsync" ).style.display = "inline-block";
					}
				);
			}
			function syncCouponData( totalcoupons ){
				if ( totalcoupons > orderCount ) {
					if ( parseInt( totalcoupons ) < 2000 ) {
						if ( parseInt( totalcoupons ) > 50 ) {
							totalcoupons = 50;
						}
					} else {
						totalcoupons = 500;
					}
					orderCount = orderCount + orderLimit;
				} else if ( totalcoupons < orderCount  ) {
					orderCount = orderCount + orderLimit;
				}
				if ( totalcoupons > orderLimit && orderCount < totalcoupons ) {
					$( '#couponsynctext' ).html( 'Synchronizing : ' + orderCount + ' / ' + totalcoupons );
				} else {
					$( '#couponsynctext' ).html( 'Synchronizing : ' + totalcoupons + ' / ' + totalcoupons );
				}
				var couponnonce = $( '#wpssw_coupon_settings' ).val();
				$.ajax(
					{
						url : admin_ajax_object.ajaxurl,
						type : 'post',
						data :"action=wpssw_sync_coupons&couponlimit=" + orderLimit + "&couponcount=" + orderCount + "&couponnonce=" + couponnonce,
						success : function( response ) {
							if ( totalcoupons <= orderCount ) {
								if (response == 'successful') {
									alert( 'All Coupons data are synchronize successfully' );
									$( '#couponsyncloader' ).hide();
									$( '#couponsynctext' ).hide();
									$( '#couponsynctext' ).html( 'Synchronizing...' );
									document.getElementById( "couponsync" ).style.display = "inline-block";
								} else {
									alert( 'Your Google Sheets API limit has been reached. Please take a look at our FAQ.' );
									$( '#couponsyncloader' ).hide();
									$( '#couponsynctext' ).hide();
									document.getElementById( "couponsync" ).style.display = "inline-block";
								}
							}
						},
						complete: function(){
							var totalcoupons = obj.totalcoupons;
							if ( parseInt( totalcoupons ) < 2000 ) {
								if ( parseInt( totalcoupons ) > 50 ) {
									totalcoupons = 50;
								}
							} else {
								totalcoupons = 500;
							}
							if ( totalcoupons > orderCount ) {
								setTimeout(
									function(){
										syncCouponData( totalcoupons );
									},
									2000
								);
							}
						}
					}
				)
				.fail(
					function() {
						alert( 'Error' );
						$( '#couponsyncloader' ).hide();
						$( '#couponsynctext' ).hide();
						document.getElementById( "couponsync" ).style.display = "inline-block";
					}
				);
			}
			function doEventAjax(args) {
				var eventnonce = $( '#wpssw_event_settings' ).val();
				$( '#eventsyncloader' ).show();
				$( '#eventsynctest' ).show();
				$( '#sync_event' ).hide();
				$.ajax(
					{
						url : admin_ajax_object.ajaxurl,
						type : 'post',
						data :"action=wpssw_get_events_count&wpssw_event_settings=" + eventnonce,
						success : function( response ) {
							if ( String( response ) === 'error' ) {
								alert( 'Sorry, your nonce did not verify.' );
								$( '#eventsyncloader' ).hide();
								$( '#eventsynctest' ).hide();
								$( '#eventsynctest' ).html( 'Synchronizing...' );
								document.getElementById( "sync_event" ).style.display = "inline-block";
							} else if (String( response ) === 'sheetnotexist') {
								alert( 'Selected Event Category sheet is not present in your spreadsheet so to sync events first save your settings and try again.' );
								$( '#eventsyncloader' ).hide();
								$( '#eventsynctest' ).hide();
								$( '#eventsynctest' ).html( 'Synchronizing...' );
								document.getElementById( "sync_event" ).style.display = "inline-block";
								return false;
							} else {
								obj        = JSON.parse( response );
								totalSheet = Object.keys( obj ).length;
								if ( totalSheet > 0 ) {
									var sheetName   = obj[syncSheet]['sheet_name'];
									var totalOrders = obj[syncSheet]['totalorders'];
									if ( parseInt( totalOrders ) < 2000 ) {
										if ( parseInt( totalOrders ) > 50 ) {
											orderLimit = 50;
										}
									}
									syncEventData( sheetName, totalOrders );
								} else {
									alert( 'All Orders are synchronize successfully' );
									$( '#eventsyncloader' ).hide();
									$( '#eventsynctest' ).hide();
									$( '#eventsynctest' ).html( 'Synchronizing...' );
									document.getElementById( "sync_event" ).style.display = "inline-block";
								}
							}
						}
					}
				)
					.fail(
						function() {
							alert( 'Error' );
							$( '#eventsyncloader' ).hide();
							$( '#eventsynctest' ).hide();
							document.getElementById( "sync_event" ).style.display = "inline-block";
						}
					);
			}
			function syncEventData( sheetName, totalOrders ){
				if ( parseInt( totalOrders ) < 2000 ) {
					if ( parseInt( totalOrders ) > 50 ) {
						orderLimit = 50;
					}
				} else {
					orderLimit = 500;
				}
				if ( totalOrders > orderCount ) {
					orderCount = orderCount + orderLimit;
				} else if ( totalOrders < orderCount && parseInt( nextSheet ) === 1 && totalSheet != ( syncSheet + 1 ) ) {
					orderCount = orderCount + orderLimit;
					nextSheet  = 0;
				}
				if ( totalOrders > orderLimit && orderCount < totalOrders ) {
					$( '#eventsynctest' ).html( 'Synchronizing : ' + orderCount + ' / ' + totalOrders + ' ' + sheetName );
				} else {
					$( '#eventsynctest' ).html( 'Synchronizing : ' + totalOrders + ' / ' + totalOrders + ' ' + sheetName );
				}
				var eventnonce = $( '#wpssw_event_settings' ).val();
				$.ajax(
					{
						url : admin_ajax_object.ajaxurl,
						type : 'post',
						data :"action=wpssw_sync_events&sheetname=" + sheetName + "&orderlimit=" + orderLimit + "&ordercount=" + orderCount + "&wpssw_event_settings=" + eventnonce,
						success : function( response ) {
							if ( parseInt( totalSheet ) === ( syncSheet + 1 ) && totalOrders <= orderCount ) {
								if (String( response ) === 'successful') {
									alert( 'All Orders are synchronize successfully' );
									$( '#eventsyncloader' ).hide();
									$( '#eventsynctest' ).hide();
									$( '#eventsynctest' ).html( 'Synchronizing...' );
									document.getElementById( "sync_event" ).style.display = "inline-block";
								} else {
									alert( 'Your Google Sheets API limit has been reached. Please take a look at our FAQ.' );
									$( '#eventsyncloader' ).hide();
									$( '#eventsynctest' ).hide();
									document.getElementById( "sync_event" ).style.display = "inline-block";
								}
							}
						},
						complete: function(){
							var sheetName   = obj[syncSheet]['sheet_name'];
							var totalOrders = obj[syncSheet]['totalorders'];
							if ( totalOrders > orderCount ) {
								setTimeout(
									function(){
										syncEventData( sheetName, totalOrders );
									},
									2000
								);
							} else if ( totalOrders < orderCount && parseInt( nextSheet ) === 1 && totalSheet != ( syncSheet + 1 ) ) {
								setTimeout(
									function(){
										syncEventData( sheetName, totalOrders );
									},
									2000
								);
							} else {
								if ( totalSheet > syncSheet + 1 ) {
									syncSheet       = syncSheet + 1;
									orderCount      = 0;
									nextSheet       = 1;
									var sheetName   = obj[syncSheet]['sheet_name'];
									var totalOrders = obj[syncSheet]['totalorders'];
									setTimeout(
										function(){
											syncEventData( sheetName, totalOrders );
										},
										2000
									);
								}
							}
						}
					}
				)
				.fail(
					function() {
						alert( 'Error' );
						$( '#eventsyncloader' ).hide();
						$( '#eventsynctest' ).hide();
						document.getElementById( "sync_event" ).style.display = "inline-block";
					}
				);
			}
			function addRegenerateGraph(id,chartId,graphType) {
				var sheetId       = id;
				var chartLoaderId = chartId;
				var sync_nonce_token;
				sync_nonce_token = admin_ajax_object.sync_nonce_token;
				$( '#' + chartLoaderId ).show();
				$.ajax(
					{
						url : admin_ajax_object.ajaxurl,
						type : 'post',
						data :"action=wpssw_regenerate_graph&sheet_id=" + sheetId + "&graph_type=" + graphType + "&sync_nonce_token=" + sync_nonce_token,
						success : function( response ) {
							$( '#' + chartLoaderId ).hide();
							if (String( response ) === 'successful') {
								alert( 'Graph Regenerated successfully' );
							} else if (String( response ) === 'spreadsheetnotexist') {
								alert( 'Please save your settings first and try again.' );
								return false;
							} else if (String( response ) === 'sheetnotexist') {
								alert( 'Selected Graph sheet is not present in your spreadsheet so to regenerate graph first save your settings and try again.' );
								$( 'html, body' ).animate(
									{
										scrollTop:$( 'html, body' ).get( 0 ).scrollHeight
									},
									3000
								);
								return false;
							} else {
								alert( response );
							}
						}
					}
				)
				.fail(
					function() {
						$( '#' + chartLoaderId ).hide();
						alert( 'Error' );
					}
				);
			}
			$( document ).on(
				'click',
				"#clear_spreadsheet",
				function(e) {
					e.preventDefault();
					var wpsswGeneralSettings = $( '#wpssw_general_settings' ).val();
					$.ajax(
						{
							url : admin_ajax_object.ajaxurl,
							type : 'post',
							data :"action=wpssw_clear_all_sheet&wpssw_general_settings=" + wpsswGeneralSettings,

							beforeSend:function(){
								if (confirm( "Are you sure? You want to enable Clear Spreadsheet this is clear all your orders within the spreadsheet and you would be remained only with sheet headers." )) {
									$( '#clearloader' ).attr( 'src',$( '#syncloader' ).attr( 'src' ) );
									$( '#clearloader' ).show();
								} else {
									return false;
								}
							},
							success : function( response ) {
								if (String( response ) === 'successful') {
									alert( 'Spreadsheet Cleared successfully' );
									$( '#clearloader' ).hide();
								} else if (String( response ) === 'sheetnotexist') {
									alert( 'Selected Order status sheet is not present in your spreadsheet so to clear spreadsheet first save your settings and try again.' );
									$( '#clearloader' ).hide();
									$( 'html, body' ).animate(
										{
											scrollTop:$( 'html, body' ).get( 0 ).scrollHeight
										},
										3000
									);
									return false;
								} else {
									alert( response );
									$( '#clearloader' ).hide();
								}
							},
							error: function (s) {
								alert( 'Error' );
								$( '#clearloader' ).hide();
							}
						}
					);
				}
			);
			$( document ).on(
				'click',
				"#clear_productsheet",
				function(e) {
					e.preventDefault();
					$.ajax(
						{
							url : admin_ajax_object.ajaxurl,
							type : 'post',
							data :"action=wpssw_clear_productsheet",
							beforeSend:function(){
								if (confirm( "Are you sure? It will clear all your product data within the spreadsheet and you would have remained only with sheet headers." )) {
									$( '#clearprdloader' ).attr( 'src',$( '#syncloader' ).attr( 'src' ) );
									$( '#clearprdloader' ).show();
								} else {
									return false;
								}
							},
							success : function( response ) {
								if (response == 'successful') {
									alert( 'Spreadsheet Cleared successfully' );
									$( '#clearprdloader' ).hide();
								} else {
									alert( response );
									$( '#clearprdloader' ).hide();
								}
							},
							error: function (s) {
								alert( 'Error' );
								$( '#clearprdloader' ).hide();
							}
						}
					);
				}
			);
			$( document ).on(
				'click',
				"#clear_customersheet",
				function(e) {
					e.preventDefault();
					$.ajax(
						{
							url : admin_ajax_object.ajaxurl,
							type : 'post',
							data :"action=wpssw_clear_custmoersheet",
							beforeSend:function(){
								if (confirm( "Are you sure? You want to enable Clear Spreadsheet this is clear all your customer data within the spreadsheet and you would be remained only with sheet headers." )) {
									$( '#clearcustloader' ).attr( 'src',$( '#syncloader' ).attr( 'src' ) );
									$( '#clearcustloader' ).show();
								} else {
									return false;
								}
							},
							success : function( response ) {
								if (response == 'successful') {
									alert( 'Spreadsheet Cleared successfully' );
									$( '#clearcustloader' ).hide();
								} else {
									alert( response );
									$( '#clearcustloader' ).hide();
								}
							},
							error: function (s) {
								alert( 'Error' );
								$( '#clearcustloader' ).hide();
							}
						}
					);
				}
			);
			$( document ).on(
				'click',
				"#clear_eventsheet",
				function(e) {
					e.preventDefault();
					$.ajax(
						{
							url : admin_ajax_object.ajaxurl,
							type : 'post',
							data :"action=wpssw_clear_eventsheet",
							beforeSend:function(){
								if (confirm( "Are you sure? You want to enable Clear Spreadsheet this is clear all your event data within the spreadsheet and you would be remained only with sheet headers." )) {
									$( '#cleareventloader' ).show();
									$( '#clear_eventsheet' ).hide();
								} else {
									return false;
								}
							},
							success : function( response ) {
								if (response == 'successful') {
									alert( 'Spreadsheet Cleared successfully' );
									$( '#cleareventloader' ).hide();
									$( '#clear_eventsheet' ).show();
								} else if (String( response ) === 'sheetnotexist') {
									alert( 'Selected Event Category sheet is not present in your spreadsheet so to clear spreadsheet first save your settings and try again.' );
									$( '#cleareventloader' ).hide();
									$( '#clear_eventsheet' ).show();
									return false;
								} else {
									alert( response );
									$( '#cleareventloader' ).hide();
									$( '#clear_eventsheet' ).show();
								}
							},
							error: function (s) {
								alert( 'Error' );
								$( '#cleareventloader' ).hide();
								$( '#clear_eventsheet' ).show();
							}
						}
					);
				}
			);
			$( document ).on(
				'click',
				"#clear_couponsheet",
				function(e) {
					e.preventDefault();
					$.ajax(
						{
							url : admin_ajax_object.ajaxurl,
							type : 'post',
							data :"action=wpssw_clear_couponsheet",
							beforeSend:function(){
								if (confirm( "Are you sure? You want to enable Clear Spreadsheet this is clear all your coupon data within the spreadsheet and you would be remained only with sheet headers." )) {
									$( '#clearcouponloader' ).attr( 'src',$( '#syncloader' ).attr( 'src' ) );
									$( '#clearcouponloader' ).show();
								} else {
									return false;
								}
							},
							success : function( response ) {
								if (response == 'successful') {
									alert( 'Spreadsheet Cleared successfully' );
									$( '#clearcouponloader' ).hide();
								} else {
									alert( response );
									$( '#clearcouponloader' ).hide();
								}
							},
							error: function (s) {
								alert( 'Error' );
								$( '#clearcouponloader' ).hide();
							}
						}
					);
				}
			);
		}
	);
	// Check for existing sheets.
	$( document ).ready(
		function(){
			$( '.synctr' ).hide();
			$( '.ord_import_row' ).hide();
			$( '.wpssw_crud_ord_row' ).hide();
			$( '#wpssw-headers-notice' ).hide();
			if ($( "#import_order_checkbox" ).is( ':checked' )) {
				$( '.wpssw_crud_ord_row' ).show();
			} else {
				$( '.wpssw_crud_ord_row' ).hide();
			}
			var prevSheetId = $( '#woocommerce_spreadsheet' ).val();
			var rowData     = String( $( 'input[type=radio][name=header_format]:checked' ).val() );
			if ( String( rowData ) === "productwise") {
				$( '.repeat_checkbox' ).show();
			} else {
				$( '.repeat_checkbox' ).hide();
			}
			if ( prevSheetId != '' ) {
				$( '.synctr' ).show();
				$( '.ord_import_row' ).show();
				$( '#wpssw-headers-notice' ).show();
			}
			$( "#woocommerce_spreadsheet" ).on(
				'change',
				function() {
					var sheetId = $( this ).val();
					if (String( sheetId ) === prevSheetId) {
						return true;
					}
					var sync_nonce_token;
					sync_nonce_token = admin_ajax_object.sync_nonce_token;
					if (sheetId != null && sheetId != '' && sheetId != 'new') {
						$.ajax(
							{
								url : admin_ajax_object.ajaxurl,
								type : 'post',
								data :{action:'wpssw_check_existing_sheet',id:sheetId, sync_nonce_token:sync_nonce_token },
								success : function( response ) {
									if ( String( response ) === 'successful' ) {
										alert( 'Selected spreadsheet will be mismatch match your order data with respect to the sheet headers so please create new spreadsheet or select different spreadsheet.' );
										$( '#woocommerce_spreadsheet' ).val( prevSheetId );
									} else if ( String( response ) ) {
										alert( response );
										$( '#woocommerce_spreadsheet' ).val( prevSheetId );
									} else {
										$( "#header_fields" ).prop( 'disabled', false );
										$( "#productwise" ).removeClass( 'disabled' );
										$( "#orderwise" ).removeClass( 'disabled' );
										$( ".synctr" ).css( 'display', 'none' );
										$( '.ord_import_row' ).css( 'display', 'none' );
										$( '.wpssw_crud_ord_row' ).css( 'display', 'none' );
										$( "#desc_order" ).removeClass( 'disabled' );
										$( "#asc_order" ).removeClass( 'disabled' );
									}
								}
							}
						);
					}
				}
			);
		}
	);
	$( document ).ready(
		function(){
			$( "#authlink" ).on(
				'click',
				function(e){
					$( '#authbtn' ).hide();
					document.getElementById( "authtext" ).style.display = "inline-block";
				}
			);
			$( "#revoke" ).on(
				'click',
				function(e){
					document.getElementById( "authtext" ).style.display     = "none";
					document.getElementById( "client_token" ).style.display = "none";
				}
			);
		}
	);
	$( document ).ready(
		function(){
			var activeTab = getParameterByName( 'tab' );
			if ( activeTab != null) {
				wpsswTab( event, activeTab );
				var classNm = "button." + activeTab;
				$( classNm ).addClass( 'active' );
			} else {
				var classNm = "button.googleapi-settings";
				$( classNm ).addClass( 'active' );
			}
		}
	);
	$( window ).load(
		function() {
			$( "#sortable" ).sortable(
				{
					disabled: false
				}
			);
			$( "#product-sortable" ).sortable(
				{
					disabled: false
				}
			);
			$( "#productcategory-sortable" ).sortable(
				{
					disabled: true
				}
			);
			$( "#woo-product-sortable" ).sortable(
				{
					disabled: false
				}
			);
			$( "#woo-customer-sortable" ).sortable(
				{
					disabled: false
				}
			);
			$( "#woo-coupon-sortable" ).sortable(
				{
					disabled: false
				}
			);
			$( "#woo-event-sortable" ).sortable(
				{
					disabled: false
				}
			);
			if ($( "#prdassheetheaders" ).is( ':checked' )) {
				$( '.td-prd-wpssw-headers' ).show();
				$( '.td-prd-append-after' ).show();
			} else {
				$( '.td-prd-wpssw-headers' ).hide();
				$( '.td-prd-append-after' ).hide();
			}
			if ($( "#product_category_select" ).is( ':checked' )) {
				$( '.td-producat-wpssw' ).show();
			} else {
				$( '.td-producat-wpssw' ).hide();
			}
			if ($( "#color_code" ).is( ':checked' )) {
				$( '#color_selection' ).show();
			} else {
				$( '#color_selection' ).hide();
			}
			if ($( "#product_settings_checkbox" ).is( ':checked' )) {
				$( '.prd_spreadsheet_row' ).show();
			} else {
				$( '.prd_spreadsheet_row' ).hide();
			}
			if ($( "#customer_settings_checkbox" ).is( ':checked' )) {
				$( '.cust_spreadsheet_row' ).show();
			} else {
				$( '.cust_spreadsheet_row' ).hide();
			}
			if ($( "#coupon_settings_checkbox" ).is( ':checked' )) {
				$( '.coupon_spreadsheet_row' ).show();
			} else {
				$( '.coupon_spreadsheet_row' ).hide();
			}
			if ($( "#event_settings_checkbox" ).is( ':checked' )) {
				$( '.event_spreadsheet_row' ).show();
			} else {
				$( '.event_spreadsheet_row' ).hide();
			}
			if ($( "#import_checkbox" ).is( ':checked' )) {
				$( '.wpssw_crud_row' ).show();
			} else {
				$( '.wpssw_crud_row' ).hide();
			}
			if ($( "#import_order_checkbox" ).is( ':checked' )) {
				$( '.wpssw_crud_ord_row' ).show();
			} else {
				$( '.wpssw_crud_ord_row' ).hide();
			}
			var i        = 1;
			var temp     = 0;
			var tblCount = $( "#mainform > table" ).length;
			$( "#mainform table" ).each(
				function() {
					if ( parseInt( tblCount ) === i) {
						$( this ).addClass( "wpssw-section-last" );
					} else {
						if ( tblCount > 5 && parseInt( i ) === 3 ) {
							$( this ).addClass( "wpssw-section-2" );
							temp = 1;
						} else {
							$( this ).addClass( "wpssw-section-" + ( i - temp ) );
						}
					}
					i++;
				}
			);
			$( ".wpssw-section-4 label input[type='checkbox'], .wpssw-section-2 label input[type='checkbox'],.wpssw-section-last label input[type='checkbox']" ).after( "<span class='checkbox-switch'></span>" );
		}
	);
	$( '#licence_submit' ).on(
		'click',
		function (e) {
			e.preventDefault();
			$( '.wpssw-license-result' ).html( '' );
			$( '#licence_submit' ).hide();
			$( '#licenceloader' ).show();
			$( '#licencetext' ).show();
			wpsswLicenseCheck( 'activate' );
		}
	);
	$( '.tm-deactivate-license' ).on(
		'click',
		function ( e ) {
			e.preventDefault();
			wpsswLicenseCheck( 'deactivate' );
		}
	);
	$( '#add_ctm_val' ).on(
		'click',
		function ( e ) {
			e.preventDefault();
			var cstVal = $( "#custom_headers_val" ).val();
			if ( String( cstVal ) === '' ) {
				alert( 'Please enter header name.' );
				$( '#custom_headers_val' ).focus();
				return false;
			}
			var labelId = cstVal.replace( / /g,"_" ).toLowerCase();
			var Val     = $( "#custom_headers_val_dropdown option:selected" ).val();
			var cstVal1 = cstVal + ',(static_header),' + Val;
			$( "#sortable" ).append( '<li class="ui-state-default ui-sortable-handle"><label for="' + labelId + '"><span class="ui-icon ui-icon-caret-2-n-s"></span><span class="wootextfield">' + cstVal + '</span><span class="ui-icon ui-icon-pencil"></span><input type="checkbox" name="header_fields_custom[]" value="' + cstVal + '" class="headers_chk1" hidden="true" checked><input type="checkbox" name="header_fields_static[]" value="' + cstVal + '" hidden="true" checked><input type="checkbox" name="header_fields[]" id="' + labelId + '" class="headers_chk" value="' + cstVal + '" checked><span class="checkbox-switch-new"><input type="checkbox" name="wpssw_static_header_values[]" value="' + cstVal1 + '" hidden="true" checked></span></label></li>' );
			$( '#custom_headers_val' ).val( '' );
		}
	);
	$( document ).ready(
		function(){
			$( '.custom-input-div' ).hide();
			$( '#custom_header_action' ).on(
				'change',
				function() {
					if (this.checked) {
						$( '.custom-input-div' ).fadeIn();
					} else {
						$( '.custom-input-div' ).fadeOut();
					}
				}
			);
			$( '#import_checkbox' ).on(
				'change',
				function() {
					if (this.checked) {
						$( '.wpssw_crud_row' ).fadeIn();
					} else {
						$( '.wpssw_crud_row' ).fadeOut();
						$( '#insert_checkbox' ).prop( 'checked', false ); // Unchecks it.
						$( '#update_checkbox' ).prop( 'checked', false ); // Unchecks it.
						$( '#delete_checkbox' ).prop( 'checked', false ); // Unchecks it.
						$( 'li.insertproduct' ).remove();
						$( 'li.updateproduct' ).remove();
						$( 'li.deleteproduct' ).remove();
					}
				}
			);
			$( '#insert_checkbox' ).on(
				'change',
				function() {
					if (this.checked) {
						var inserttext = 'Insert';
						$( "#woo-product-sortable" ).append( '<li class="ui-state-default ui-sortable-handle insertproduct"><label><span class="ui-icon ui-icon-caret-2-n-s"></span><span class="wootextfield">' + inserttext + '</span><input type="checkbox" name="wooproduct_custom[]" value="' + inserttext + '" class="woo-pro-headers-chk1" checked="" hidden="true"><input type="checkbox" name="wooproduct_header_list[]" value="' + inserttext + '" id="woo-Insert" class="woo-pro-headers-chk" checked=""></label></li>' );
					} else {
						$( 'li.insertproduct' ).remove();
					}
				}
			);
			$( '#update_checkbox' ).on(
				'change',
				function() {
					if (this.checked) {
						var updatetext = 'Update';
						$( "#woo-product-sortable" ).append( '<li class="ui-state-default ui-sortable-handle updateproduct"><label><span class="ui-icon ui-icon-caret-2-n-s"></span><span class="wootextfield">' + updatetext + '</span><input type="checkbox" name="wooproduct_custom[]" value="' + updatetext + '" class="woo-pro-headers-chk1" checked="" hidden="true"><input type="checkbox" name="wooproduct_header_list[]" value="' + updatetext + '" id="woo-Insert" class="woo-pro-headers-chk" checked=""></label></li>' );
					} else {
						$( 'li.updateproduct' ).remove();
					}
				}
			);
			$( '#delete_checkbox' ).on(
				'change',
				function() {
					if (this.checked) {
						var deletetext = 'Delete';
						$( "#woo-product-sortable" ).append( '<li class="ui-state-default ui-sortable-handle deleteproduct"><label><span class="ui-icon ui-icon-caret-2-n-s"></span><span class="wootextfield">' + deletetext + '</span><input type="checkbox" name="wooproduct_custom[]" value="' + deletetext + '" class="woo-pro-headers-chk1" checked="" hidden="true"><input type="checkbox" name="wooproduct_header_list[]" value="' + deletetext + '" id="woo-Insert" class="woo-pro-headers-chk" checked=""></label></li>' );
					} else {
						$( 'li.deleteproduct' ).remove();
					}
				}
			);
			$( '#import_order_checkbox' ).on(
				'change',
				function() {
					if (this.checked) {
						$( '.wpssw_crud_ord_row' ).fadeIn();
					} else {
						$( '.wpssw_crud_ord_row' ).fadeOut();
						$( '#update_order_checkbox' ).prop( 'checked', false ); // Unchecks it.
						$( '#delete_order_checkbox' ).prop( 'checked', false ); // Unchecks it.
						$( 'li.updateorder' ).remove();
						$( 'li.deleteorder' ).remove();
					}
				}
			);
			$( '#update_order_checkbox' ).on(
				'change',
				function() {
					if (this.checked) {
						var updatetext = 'Update';
						$( "#sortable" ).append( '<li class="ui-state-default ui-sortable-handle updateorder"><label><span class="ui-icon ui-icon-caret-2-n-s"></span><span class="wootextfield">' + updatetext + '</span><input type="checkbox" name="header_fields_custom[]" value="' + updatetext + '" class="headers_chk1" hidden="true" checked><input type="checkbox" name="header_fields[]" id="" class="headers_chk" value="' + updatetext + '" checked></label></li>' );
					} else {
						$( 'li.updateorder' ).remove();
					}
				}
			);
			$( '#delete_order_checkbox' ).on(
				'change',
				function() {
					if (this.checked) {
						var deletetext = 'Delete';

						$( "#sortable" ).append( '<li class="ui-state-default ui-sortable-handle deleteorder"><label><span class="ui-icon ui-icon-caret-2-n-s"></span><span class="wootextfield">' + deletetext + '</span><input type="checkbox" name="header_fields_custom[]" value="' + deletetext + '" class="headers_chk1" hidden="true" checked><input type="checkbox" name="header_fields[]" id="" class="headers_chk" value="' + deletetext + '" checked></label></li>' );
					} else {
						$( 'li.deleteorder' ).remove();
					}
				}
			);
			$( '#prdassheetheaders' ).on(
				'change',
				function() {
					if (this.checked) {
						$( "#loaderprdheader" ).fadeIn();
						$.ajax(
							{
								url : admin_ajax_object.ajaxurl,
								type : 'post',
								data :"action=wpssw_get_product_list",
								data :{action:"wpssw_get_product_list"},
								success : function( response ) {
									$( "#loaderprdheader" ).fadeOut();
									$( '.td-prd-wpssw-headers' ).replaceWith( response ).fadeIn();
									$( '.td-prd-append-after' ).fadeIn();
								}
							}
						)
						.fail(
							function() {
								alert( 'Error' );
							}
						);
					} else {
						$( '.td-prd-wpssw-headers' ).fadeOut();
						$( '.td-prd-append-after' ).fadeOut();
					}
				}
			);
			$( '#product_category_select' ).on(
				'change',
				function() {
					if (this.checked) {
						$( "#loaderprdcatheader" ).fadeIn();
						$.ajax(
							{
								url : admin_ajax_object.ajaxurl,
								type : 'post',
								data :"action=wpssw_get_category_list",
								data :{action:"wpssw_get_category_list"},
								success : function( response ) {
									$( "#loaderprdcatheader" ).fadeOut();
									$( '.td-producat-wpssw' ).replaceWith( response ).fadeIn();
								}
							}
						)
						.fail(
							function() {
								$( "#loaderprdcatheader" ).fadeOut();
								alert( 'Error' );
							}
						);
					} else {
						$( '.td-producat-wpssw' ).fadeOut();
					}
				}
			);
		}
	);
	$( document ).ready(
		function(){
			$( '#weekly li' ).click(
				function () {
					if ($( this ).hasClass( 'selected' )) {
						$( this ).removeClass( 'selected' );
					} else {
						$( this ).addClass( 'selected' );
					}

					$( '#weekly_days' ).val( '' );

					$( '#weekly li.selected' ).each(
						function () {
							var val = $( this ).data( 'day' );
							$( '#weekly_days' ).val( $( '#weekly_days' ).val() + val + ',' );
						}
					);

					$( '#weekly_days' ).val( $( '#weekly_days' ).val().slice( 0, -1 ) );

				}
			);

			$( 'input[name="scheduling_run_on"]' ).change(
				function () {
					var val = $( 'input[name="scheduling_run_on"]:checked' ).val();
					showinterval( val );
				}
			);
			$( 'input[name="scheduling_enable"]' ).change(
				function () {
					var val = $( 'input[name="scheduling_enable"]:checked' ).val();
					showschedule( val );
				}
			);

			var val = $( 'input[name="scheduling_enable"]:checked' ).val();
			showschedule( val );
		}
	);
	$( document ).ready(
		function(){
			$( '.sync_all_fromtodate' ).hide();
			$( '#expsyncloader' ).hide();
			$( '#expsynctext' ).hide();
			$( '#spreadsheet_url' ).hide();
			$( '#exportform' ).on(
				'submit',
				function(){
					$( '#spreadsheet_url' ).hide();
					$( '#spreadsheet_xslxurl' ).hide();
					var ordFromDate           = $( '#ordfromdate' ).val();
					var wpssw_export_settings = $( '#wpssw_export_settings' ).val();
					var ordToDate             = $( '#ordtodate' ).val();
					var spreadSheetName       = $( '#expspreadsheetname' ).val();
					var chkArray              = [];
					/* look for all checkboes that have a class 'chk' attached to it and check if it was checked */
					$( ".prdcatheaders_chk:checked" ).each(
						function() {
							chkArray.push( $( this ).val() );
						}
					);
					if (ordFromDate > ordToDate) {
						alert( 'From Date should not be greater than To Date.' );
					} else {
						$( '#expsyncloader' ).show();
						$( '#expsynctext' ).show();
						$( '#exportsubmit' ).attr( 'disabled',true );
						if ($( '#exportall' ).is( ":checked" )) {
							var exportAll = 'yes';
						} else {
							var exportAll = 'no';
						}
						if ($( '#category_select' ).is( ":checked" )) {
							var categorySelect = 'yes';
						} else {
							var categorySelect = 'no';
						}
						$.ajax(
							{
								url : admin_ajax_object.ajaxurl,
								type : 'post',
								data :"action=wpssw_export_order",
								data :{action:"wpssw_export_order",from_date:ordFromDate,to_date:ordToDate,spreadsheetname:spreadSheetName,exportall:exportAll,category_select:categorySelect,category_ids:chkArray,wpssw_export_settings:wpssw_export_settings },
								success : function( response ) {
									if ( String( response ) === 'error' ) {
										$( '#expsyncloader' ).hide();
										$( '#expsynctext' ).hide();
										$( '#exportsubmit' ).attr( 'disabled',false );
										alert( 'Sorry, your nonce did not verify.' );
										return false;
									} else {
										var res = JSON.parse( response );
										if (String( res.result ) === 'successful') {
											var sheetId = 'https://docs.google.com/spreadsheets/d/' + res.spreadsheetid;
											var xlsxurl = "https://docs.google.com/spreadsheets/u/0/d/" + res.spreadsheetid + "/export?exportFormat=xlsx";
											$( '#spreadsheet_url' ).attr( "href", sheetId );
											$( '#spreadsheet_xslxurl' ).attr( "href", xlsxurl );
											$( '#expsyncloader' ).hide();
											$( '#expsynctext' ).hide();
											$( '#exportsubmit' ).attr( 'disabled',false );
											alert( 'Export All Orders Successfully' );
											$( '#spreadsheet_url' ).show();
											$( '#spreadsheet_xslxurl' ).show();
											$( '#spreadsheet_csvurl' ).show();
										} else {
											$( '#expsyncloader' ).hide();
											$( '#expsynctext' ).hide();
											$( '#exportsubmit' ).attr( 'disabled',false );
											alert( 'Your Google Sheets API limit has been reached. Please take a look at our FAQ.' );
										}
									}
								}
							}
						)
						.fail(
							function() {
								$( '#expsyncloader' ).hide();
								$( '#expsynctext' ).hide();
								$( '#exportsubmit' ).attr( 'disabled',false );
								alert( 'Error' );
							}
						);
					}
					return false;
				}
			);
			$( '#exportall' ).on(
				'change',
				function() {
					if (this.checked) {
						$( '#ordtodate' ).attr( 'disabled',true );
						$( '#ordfromdate' ).attr( 'disabled',true );
					} else {
						$( '#ordtodate' ).attr( 'disabled',false );
						$( '#ordfromdate' ).attr( 'disabled',false );
					}
				}
			);
			$( '#sync_all' ).on(
				'change',
				function() {
					if (this.checked) {
						$( '.sync_all_fromtodate' ).fadeOut();
						$( '#sync_all_fromdate' ).removeAttr( 'required' );
						$( '#sync_all_todate' ).removeAttr( 'required' );
					} else {
						$( '.sync_all_fromtodate' ).fadeIn();
						$( '#sync_all_fromdate' ).attr( 'required','required' );
						$( '#sync_all_todate' ).attr( 'required','required' );
					}
				}
			);
		}
	);
	$( document ).ready(
		function(){
			var newRequest = $( '#woocommerce_spreadsheet' ).val();
			if (newRequest != 'new' && newRequest != '' && newRequest != 0 ) {
				var slink = '<a id="view_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' + newRequest + '" class="wpssw-button">View Spreadsheet</a> <a id="clear_spreadsheet" href="" class="wpssw-button">Clear Spreadsheet</a>   <img src="" id="clearloader">';
				$( "#woocommerce_spreadsheet" ).after( slink );
			}
			var product_spreadsheet = $( '#product_spreadsheet' ).val();
			if (product_spreadsheet != 'new' && product_spreadsheet != '' && product_spreadsheet != 0 ) {
				var slink = '<a id="view_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' + product_spreadsheet + '" class="wpssw-button">View Spreadsheet</a> <a id="clear_productsheet" href="" class="wpssw-button">Clear Spreadsheet</a>   <img src="" id="clearprdloader">';
				$( "#product_spreadsheet" ).after( slink );
			}
			var customer_spreadsheet = $( '#customer_spreadsheet' ).val();
			if (customer_spreadsheet != 'new' && customer_spreadsheet != '' && customer_spreadsheet != 0 ) {
				var slink = '<a id="view_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' + customer_spreadsheet + '" class="wpssw-button">View Spreadsheet</a> <a id="clear_customersheet" href="" class="wpssw-button">Clear Spreadsheet</a>   <img src="" id="clearcustloader">';
				$( "#customer_spreadsheet" ).after( slink );
			}
			var coupon_spreadsheet = $( '#coupon_spreadsheet' ).val();
			if (coupon_spreadsheet != 'new' && coupon_spreadsheet != '' && coupon_spreadsheet != 0 ) {
				var slink = '<a id="view_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' + coupon_spreadsheet + '" class="wpssw-button">View Spreadsheet</a> <a id="clear_couponsheet" href="" class="wpssw-button">Clear Spreadsheet</a>   <img src="" id="clearcouponloader">';
				$( "#coupon_spreadsheet" ).after( slink );
			}
			var eventSpreadsheet = $( '#event_spreadsheet' ).val();
			if (eventSpreadsheet != 'new' && eventSpreadsheet != '' && eventSpreadsheet != 0 ) {
				var slink = '<a id="view_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' + eventSpreadsheet + '" class="wpssw-button">View Spreadsheet</a> ';
				$( "#event_spreadsheet" ).after( slink );
			}
		}
	);
})( jQuery );
function showschedule( val ){
	var intervalval = jQuery( 'input[name="scheduling_run_on"]:checked' ).val();
	showinterval( intervalval );
	if ( val == "0" ) {
		jQuery( '#automatic-scheduling' ).slideUp();
	} else if (val == "1") {
		jQuery( '#automatic-scheduling' ).slideDown();
	}
}
function showinterval( val ){
	if ( val == "weekly" ) {
		jQuery( '#weekly' ).slideDown( 200 );
		jQuery( '#scheduling_date' ).slideUp( 175 );
		jQuery( '#schedule_recurrence' ).slideUp( 175 );
	} else if ( val == "onetime" ) {
		jQuery( '#scheduling_date' ).slideDown( 200 );
		jQuery( '#weekly' ).slideUp( 175 );
		jQuery( '#schedule_recurrence' ).slideUp( 175 );
	} else if ( val == "recurrence" ) {
		jQuery( '#schedule_recurrence' ).slideDown( 200 );
		jQuery( '#weekly' ).slideUp( 175 );
		jQuery( '#scheduling_date' ).slideUp( 175 );
	}
}
function wpsswTab(evt, tabName) {
	"use strict";
	var i, tabContent, tabLinks;
	tabContent           = document.getElementsByClassName( "tabcontent" );
	var tabContentlength = tabContent.length;
	for (i = 0; i < tabContentlength; i++) {
		tabContent[i].style.display = "none";
	}
	tabLinks      = document.getElementsByClassName( "tablinks" );
	var tablength = tabLinks.length;
	for (i = 0; i < tablength; i++) {
		tabLinks[i].className = tabLinks[i].className.replace( " active", "" );
	}
	document.getElementById( tabName ).style.display = "block";
	var type = typeof event;
	if (type !== 'undefined') {
		evt.currentTarget.className += " active";
	}
}
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
function wpsswLicenseCheck( action ) {
	"use strict";
	if ( String( jQuery( '#ws_envato' ).val() ) === '' ) {
		jQuery( '.wpssw-license-result' ).html( '<div class="error"><p>Please enter Envato API Token</p></div>' );
		jQuery( '#licenceloader' ).hide();
		jQuery( '#licencetext' ).hide();
		jQuery( '#licence_submit' ).show();
		return false;
	}
		var data = {
			action: 'wpssw_' + action + '_license',
			username: jQuery( '#ws_username' ).val(),
			key: jQuery( '#ws_purchase' ).val(),
			api_key: jQuery( '#ws_envato' ).val(),
			agree_transmit: jQuery( '#agree_transmit:checked' ).val(),
			wpnonce	: jQuery( '#_wpnonce' ).val()
	};
		jQuery.post(
			admin_ajax_object.ajaxurl,
			data,
			function ( response ) {
				var html;
				if ( ! response || parseInt( response ) === - 1 ) {
					html = '<div class="error"><p>Please enter valid Envato API Token</p></div>';
				} else if ( response && response.message && response.result
				&& (String( response.result ) === '-3' || String( response.result ) === '-2'
				|| String( response.result ) === 'wp_error' || String( response.result ) === 'server_error') ) {
					html = response.message;
				} else if ( response && response.message && response.result && (String( response.result ) === '4') ) {
					html = response.message;
				} else {
					html = '';
				}
				jQuery( '.wpssw-license-result' ).html( html );
				jQuery( '#licenceloader' ).hide();
				jQuery( '#licencetext' ).hide();
				jQuery( '#licence_submit' ).show();
			},
			'json'
		)
			.always( function ( response ) {} );
}
function wpsswCopy( id, targetid ) {
	var copyText   = document.getElementById( id );
	var textArea   = document.createElement( "textarea" );
	textArea.value = copyText.textContent;
	document.body.appendChild( textArea );
	textArea.select();
	document.execCommand( "Copy" );
	textArea.remove();
}
