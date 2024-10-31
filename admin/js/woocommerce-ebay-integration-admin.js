(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	 var ajaxUrl   = ced_ebay_admin_obj.ajax_url;
	 var ajaxNonce = ced_ebay_admin_obj.ajax_nonce;
	 var user_id   = ced_ebay_admin_obj.user_id;
	 var siteUrl   = ced_ebay_admin_obj.site_url;
	 var ebay_loader_overlay = '<div class="ced_ebay_overlay"><div class="ced_ebay_overlay__inner"><div class="ced_ebay_overlay__content"><div class="ced_ebay_page-loader-indicator ced_ebay_overlay_loader"><svg class="ced_amazon_overlay_spinner" width="65px" height="65px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg"><circle class="path" fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30"></circle></svg></div><div class="ced_ebay_page-loader-info"><p class="ced_ebay_page-loader-info-text" id="ced_ebay_progress_text">Loading...</p><p class="ced_ebay_page-loader-info-text" style="font-size:19px;" id="ced_ebay_countdown_timer"></p></div></div></div></div>';

	 $(function() {
		jQuery(document).on('change', '.ced-ebay-filter-products-criteria', function(){
			var productFilterCriteria = jQuery(document).find('.ced-ebay-filter-products-criteria option:selected').val();
			if(productFilterCriteria == 'product_name'){
				var productFilterCriteriaPlaceholder = 'Filter by Product Name';
			} else if(productFilterCriteria == 'ebay_listing_id'){
				var productFilterCriteriaPlaceholder = 'Filter by eBay Item ID';
			} else if(productFilterCriteria == 'product_sku'){
				var productFilterCriteriaPlaceholder = 'Filter by Product SKU';
			}

			jQuery('.ced-ebay-filter-products').selectWoo({
				allowClear: true,
				placeholder: productFilterCriteriaPlaceholder,
				dropdownPosition: 'below',
			  dropdownAutoWidth : true,
			  language: {
				  inputTooShort: function (args) {

					  return "Please enter 3 or more words.";
				  },
				  noResults: function () {
					  return "Not Found.";
				  },
				  searching: function () {
					  return "Searching...";
				  }
			  },
			  minimumInputLength: 3,
			  ajax : {
				  url: ajaxUrl,
				  delay: 250,
				  data: function (term) {
					return {
						search_term: term.term,
						ajax_nonce: ajaxNonce,
						action: 'ced_ebay_filter_products',
						filter_criteria: productFilterCriteria,
						user_id: user_id
					};
				},
				processResults: function (data) {
					return {
						results: $.map(data, function(obj) {
							if(obj.id!=0){
							return { id: obj.post_id, text: obj.post_title };
						}
											})
					};

				}
			  }
			});
		})

		jQuery('.ced-ebay-filter-products').selectWoo(
			{
			allowClear: true,
			  placeholder: "Filter by Product Name",
			  dropdownPosition: 'below',
			  dropdownAutoWidth : true,
			  language: {
				  inputTooShort: function (args) {

					  return "Please enter 3 or more words.";
				  },
				  noResults: function () {
					  return "Not Found.";
				  },
				  searching: function () {
					  return "Searching...";
				  }
			  },
			  minimumInputLength: 3,
			  ajax : {
				  url: ajaxUrl,
				  delay: 250,
				  data: function (term) {
					return {
						search_term: term.term,
						ajax_nonce: ajaxNonce,
						action: 'ced_ebay_filter_products',
						filter_criteria: 'product_name',
						user_id: user_id
					};
				},
				processResults: function (data) {
					return {
						results: $.map(data, function(obj) {
							if(obj.id!=0){
							return { id: obj.post_id, text: obj.post_title };
						}
											})
					};

				}
			  }
			}
		  );


			$('.ced-searchbox').keyup(function(e){
				var search_value = $(this).val();
				if(search_value.length <= 3) {
					return;
				}
				e.preventDefault();
				var data = {
					paged: parseInt( $('input[name=paged]').val() ) || '1',
					order: $('input[name=order]').val() || 'asc',
					orderby: $('input[name=orderby]').val() || 'title',
					s : search_value,
				};

				setParams('s', search_value);
				setParams('paged', data['paged']);
				setParams('order', data['order']);
				setParams('orderby', data['orderby']);
				list.update( data );

			});

			const list = {
				update: function( data ) {
					$(document).find('.ced-ebay-products').append(ebay_loader_overlay);
					$.ajax({
						url: ajaxUrl,
						type: 'post',
						data: $.extend(
							{
								ajax_nonce : ajaxNonce,

								action: 'ced_ebay_ajax_fetch_custom_list',
							},
							data
						),

						success: function( response ) {
							jQuery('.ced-ebay-products .ced_ebay_overlay').remove();
							var response = $.parseJSON( response );
							// Add the requested rows
							if ( response.rows.length )
								$('#the-list').html( response.rows );
							// Update column headers for sorting
							if ( response.column_headers.length )
								$('thead tr, tfoot tr').html( response.column_headers );
							// Update pagination for navigation
							if ( response.pagination.bottom.length )
								$('.tablenav.top .tablenav-pages').html( $(response.pagination.top).html() );
							if ( response.pagination.top.length )
								$('.tablenav.bottom .tablenav-pages').html( $(response.pagination.bottom).html() );

								ced_popover_update_data();

								$('#ced-wp-table-list .wp-list-table.ced-products').removeClass('ced-table-loader');
							// Init back our event handlers
							list.init();
						}
					});
				}
			}
				  });



	 jQuery(document).on("click", "#ced_ebay_marketing_do_login", function(event){
		event.preventDefault();
		var user_id_parameter = jQuery(this).attr('data-user-id');
		var loginMode = jQuery(this).attr('data-login-mode');
		var ebay_site_id = jQuery('#ced_ebay_login_select_site option:selected').val();
		if(ebay_site_id == -1){
			alert('Please select your eBay Account Region, from the dropdown, before login!');
			return;
		}
		if(ebay_site_id === undefined || ebay_site_id === null){
			ebay_site_id = jQuery(this).attr('data-site-id');
		}
		jQuery('#wpbody-content').append(ebay_loader_overlay);
		jQuery('#ced_ebay_progress_text').html('Please wait while we connect your eBay Account');
		jQuery.ajax({
			url: ajaxUrl,
			type: 'post',
			data: {
				ajax_nonce: ajaxNonce,
				action: 'ced_ebay_oauth_authorization',
				site_id: ebay_site_id,
				login_mode : loginMode,
			},
			success: function(response) {
				var response = jQuery.parseJSON( response );
				jQuery( '.ced_ebay_loader' ).hide();
				if(response != ''){
					window.location.replace(response);
				} else {
					alert('Failed to fetch Authorization URL! Please contact support.');
					return;
				}

			}
		})
	});

	jQuery( document ).on(
		"click",
		".ced_ebay_site_add_shipping_template_button",
		function(){
			jQuery( document ).find( "#ced-ebay-popup.overlay" ).css( 'visibility','visible' );
			jQuery( document ).find( "#ced-ebay-popup.overlay" ).css( 'opacity',1 );
		}
	);
	window.addEventListener(
		'keydown',
		function (event) {
			if (event.key === 'Escape') {
				  jQuery( document ).find( "#ced-ebay-popup.overlay" ).css( 'visibility','hidden' );

				  jQuery( document ).find( "#ced-ebay-popup.overlay" ).css( 'opacity',0 );
			}
		}
	);
	jQuery( document ).on(
		"click",
		".ced_ebay_site_shipping_template_close",
		function(){
			jQuery( document ).find( '.notice-empty-field-description-template' ).remove();
			jQuery( document ).find( "#ced-ebay-popup.overlay" ).hide();
			var windowUrl        = siteUrl + '/wp-admin/admin.php?page=ced_ebay&section=accounts-view&part=shipping&user_id=' + user_id;
			window.location.href = windowUrl;
		}
	);

		$( document ).on(
		"click",
		".ced_ebay_site_add_shipping_template_button",
		function(){

			$( document ).find( ".ced_ebay_site_new_shipping_template_wrapper" ).show();

		}
	);

		jQuery( document ).on(
		'click',
		'.ced_ebay_add_domestic_shipping_service_row',
		function(){
			var repeatable = jQuery( this ).parents( 'tr' ).clone();
			jQuery( repeatable ).insertAfter( jQuery( this ).parents( 'tr' ) );
			jQuery( this ).parent( 'td' ).remove();
			jQuery( repeatable ).find( 'input[type=text]' ).val( "" );
		}
	);

	jQuery( document ).on(
		'click',
		'.ced_ebay_add_intl_shipping_service_row',
		function(){
			var key        = jQuery( this ).attr( 'data-add' );
			key            = parseInt( key, 10 ) + 1;
			var repeatable = jQuery( this ).parents( 'tr' ).clone();
			jQuery( repeatable ).insertAfter( jQuery( this ).parents( 'tr' ) );
			jQuery( this ).parent( 'td' ).remove();
			jQuery( repeatable ).find( 'input[type=text]' ).val( "" );
			jQuery( repeatable ).find( '.select_location' ).attr( 'name' , 'shippingdetails[internationalShippingService][locations][' + key + '][]' );
			key = key.toString();
			jQuery( repeatable ).find( '.ced_ebay_add_intl_shipping_service_row' ).attr( 'data-add', key );
		}
	);
	$( document ).on(
		'change',
		'.ced_ebay_select_category',
		function(){

			var store_category_id           = $( this ).attr( 'data-storeCategoryID' );
			var ebay_store_id               = $( this ).attr( 'data-ebayStoreId' );
			var selected_ebay_category_id   = $( this ).val();
			var selected_ebay_category_name = $( this ).find( "option:selected" ).text();
			var level                       = $( this ).attr( 'data-level' );

			if ( level != '8' ) {
				$( '.ced_ebay_loader' ).show();
				$.ajax(
					{
						url : ajaxUrl,
						data : {
							ajax_nonce : ajaxNonce,
							action : 'ced_ebay_fetch_next_level_category',
							level : level,
							name : selected_ebay_category_name,
							id : selected_ebay_category_id,
							store_id : store_category_id,
							ebay_store_id : ebay_store_id,
							type: 'primary'

						},
						type : 'POST',
						success: function(response)
					{
							response = jQuery.parseJSON( response );
							$( '.ced_ebay_loader' ).hide();
							if ( response != 'No-Sublevel' ) {
								for (var i = 1; i < 8; i++) {
									$( '#ced_ebay_categories_' + store_category_id ).find( '.ced_ebay_level' + (parseInt( level ) + i) + '_category' ).closest( "td" ).remove();
								}
								if (response != 0) {
									$( '#ced_ebay_categories_' + store_category_id ).append( response );
								}
							} else {
								$( '#ced_ebay_categories_' + store_category_id ).find( '.ced_ebay_level' + (parseInt( level ) + 1) + '_category' ).remove();
							}
						}
					}
				);
			}

		}
	);


	$( document ).on(
		'change',
		'.ced_ebay_select_secondary_category',
		function(){

			var store_category_id           = $( this ).attr( 'data-storeCategoryID-secondary' );
			var ebay_store_id               = $( this ).attr( 'data-ebayStoreId-secondary' );
			var selected_ebay_category_id   = $( this ).val();
			var selected_ebay_category_name = $( this ).find( "option:selected" ).text();
			var level                       = $( this ).attr( 'data-level-secondary' );

			if ( level != '8' ) {
				$( '.ced_ebay_loader' ).show();
				$.ajax(
					{
						url : ajaxUrl,
						data : {
							ajax_nonce : ajaxNonce,
							action : 'ced_ebay_fetch_next_level_category',
							level : level,
							name : selected_ebay_category_name,
							id : selected_ebay_category_id,
							store_id : store_category_id,
							ebay_store_id : ebay_store_id,
							type: 'secondary'
						},
						type : 'POST',
						success: function(response)
					{
							response = jQuery.parseJSON( response );
							$( '.ced_ebay_loader' ).hide();
							if ( response != 'No-Sublevel' ) {
								for (var i = 1; i < 8; i++) {
									$( '#ced_ebay_secondary_categories_' + store_category_id ).find( '.ced_ebay_level' + (parseInt( level ) + i) + '_secondary_category' ).closest( "td" ).remove();

								}
								if (response != 0) {
									$( '#ced_ebay_secondary_categories_' + store_category_id ).append( response );

								}
							} else {
								$( '#ced_ebay_secondary_categories_' + store_category_id ).find( '.ced_ebay_level' + (parseInt( level ) + 1) + '_secondary_category' ).remove();

							}
						}
					}
				);
			}

		}
	);

	jQuery(document).on('click', '#ced_ebay_remove_all_profiles_btn', function(e){
		e.preventDefault();
		Swal.fire({
			text: "Are you sure you want to Remove all the profiles? This action is irreversible. Your category mappings will also be removed.",
			icon: "warning",
			showCancelButton: true,
  			confirmButtonColor: '#3085d6',
  			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, do it!'
		}).then((willRemoveProfiles) => {
			if(willRemoveProfiles.isConfirmed){
				jQuery('.ced_ebay_loader').show();
				jQuery.ajax({
					type:'post',
					url: ajaxUrl,
					data: {
						userid: user_id,
						ajax_nonce: ajaxNonce,
						action: 'ced_ebay_remove_all_profiles'
					},
					success: function(response){
						jQuery('.ced_ebay_loader').hide();
						Swal.fire({
							title:response.title,
							text: response.message,
							icon: response.status,
					  }).then(()=>{
						window.location.reload();
					  });
					}
				})
			}
	  });
	});

	jQuery(document).on('click', '#ced_ebay_reset_item_aspects_btn', function(e){
		e.preventDefault();
		Swal.fire({
			text: "Are you sure you want to Remove the category item specifcs? This action will remove all the eBay category item specifics and will fetch them again when you will edit the profile.",
			icon: "warning",
			showCancelButton: true,
  			confirmButtonColor: '#3085d6',
  			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, do it!'
		}).then((willRemoveProfiles) => {
			if(willRemoveProfiles.isConfirmed){
				jQuery('.ced_ebay_loader').show();
				jQuery.ajax({
					type:'post',
					url: ajaxUrl,
					data: {
						userid: user_id,
						ajax_nonce: ajaxNonce,
						action: 'ced_ebay_reset_category_item_specifics'
					},
					success: function(response){
						jQuery('.ced_ebay_loader').hide();
						Swal.fire({
							title:response.title,
							text: response.message,
							icon: response.status,
					  }).then(()=>{
						window.location.reload();
					  });
					}
				})
			}
	  });
	});


	jQuery(document).on('click', '#ced_ebay_remove_term_from_profile_btn', function(e){
		e.preventDefault();
		var termID = jQuery(this).attr('data-term-id');
		var profileID = jQuery(this).attr('data-profile-id');
		Swal.fire({
			text: "Are you sure you want to remove the WooCommerce category from this profile?",
			icon: "warning",
			showCancelButton: true,
  			confirmButtonColor: '#3085d6',
  			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, do it!'
		}).then((willRemoveProfiles) => {
			if(willRemoveProfiles.isConfirmed){
				jQuery('.ced_ebay_loader').show();
				jQuery.ajax({
					type:'post',
					url: ajaxUrl,
					data: {
						userid: user_id,
						term_id: termID,
						profile_id : profileID,
						ajax_nonce: ajaxNonce,
						action: 'ced_ebay_remove_term_from_profile'
					},
					success: function(response){
						jQuery('.ced_ebay_loader').hide();
						Swal.fire({
							title:response.title,
							text: response.message,
							icon: response.status,
					  });
					}
				})
			}
	  });
	});




	jQuery(document).on('click', '#ced_ebay_disconnect_account_btn', function(e){
		e.preventDefault();
		Swal.fire({
			text: "Are you sure you want to disconnect your account? Disconnecting your account will stop all the automation. Your configuration will not be deleted.",
			icon: "warning",
			showCancelButton: true,
  			confirmButtonColor: '#3085d6',
  			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, do it!'
		}).then((willRemoveAccount) => {
			if(willRemoveAccount.isConfirmed){
				jQuery('.ced_ebay_loader').show();
				jQuery.ajax({
					type:'post',
					url: ajaxUrl,
					data: {
						userid: user_id,
						ajax_nonce: ajaxNonce,
						action: 'ced_ebay_remove_account_from_integration'
					},
					success: function(response){
						jQuery('.ced_ebay_loader').hide();
						Swal.fire({
							title:response.title,
							text: response.message,
							icon: response.status,
					  }).then(()=>{
						window.location.reload();
					  });
					}
				})
			}
	  });
	});

	$( document ).on(
		'click',
		'#ced_ebay_category_refresh_button',
		function(e){
			jQuery('#wpbody-content').append(ebay_loader_overlay);
			var levels_arr = [1,2,3,4,5,6,7,8];
			performCategoryRefresh( levels_arr );
		}
	);

	function performCategoryRefresh(levels_arr){

		if (levels_arr == '') {
			var notice = "";
			notice    += "<div class='notice notice-error'><p>Categories not found.</p></div>";
			$( ".success-admin-notices" ).html( notice );
		}
				$.ajax(
			{
				url: ajaxUrl,
				data: {
					ajax_nonce : ajaxNonce,
					action : 'ced_ebay_category_refresh_button',
					userid:user_id,
					levels: levels_arr
				},
				type: 'POST',
				success: function(response){
					response = jQuery.parseJSON( response );
					var category_level = response.level;
					if(response.status == 'error'){
						jQuery('#ced_ebay_progress_text').html(response.message);
					} else {
						notice             = "Imported " + category_level + " out of 8 Category Files Successfully";
						jQuery('#ced_ebay_progress_text').html(notice);
						var remaining_levels_arr = levels_arr.splice( 1 );
						if (remaining_levels_arr != '') {
							performCategoryRefresh( remaining_levels_arr );
						} else {
							jQuery('#ced_ebay_progress_text').html('All Category Files Imported Successfully');
							window.setTimeout( function(){window.location.reload()}, 2000 );
	
						}
					}
				
				}
			}
		)

	}


	$( document ).on(
		'change',
		'.ced_ebay_select_store_category_checkbox',
		function(){
			var store_category_id = $( this ).attr( 'data-categoryID' );
			if ( $( this ).is( ':checked' ) ) {
				$( '#ced_ebay_categories_' + store_category_id ).show( 'slow' );
				$( '#ced_ebay_secondary_categories_' + store_category_id ).show( 'slow' );
				$( '#ced_ebay_store_custom_categories_' + store_category_id ).show( 'slow' );
				$( '#ced_ebay_store_custom_categories_' + store_category_id ).attr('style', 'display:table-row' );
				$( '#ced_ebay_store_secondary_categories_' + store_category_id ).show( 'slow' );
				$( '#ced_ebay_store_secondary_categories_' + store_category_id ).attr('style', 'display:table-row' );
				$( '#ced_ebay_secondary_categories_' + store_category_id ).attr('style', 'display:table-row' );


			} else {
				$( '#ced_ebay_categories_' + store_category_id ).hide( 'slow' );
				$( '#ced_ebay_store_custom_categories_' + store_category_id ).hide( 'slow' );
				$( '#ced_ebay_store_secondary_categories_' + store_category_id ).hide( 'slow' );
				$( '#ced_ebay_secondary_categories_' + store_category_id ).hide( 'slow' );

			}
		}
	);

	$( document ).on(
		'click',
		'#ced_ebay_save_category_button',
		function(){

			var  ebay_category_array  = [];
			var  ebay_secondary_category_array  = [];
			var  store_category_array = [];
			var  ebay_category_name   = [];
			var  ebay_secondary_category_name   = [];
			var ebay_store_custom_category_array = [];
			var ebay_store_secondary_category_array = [];
			var ebay_store_id         = $( this ).attr( 'data-ebayStoreID' );
			jQuery( '.ced_ebay_select_store_category_checkbox' ).each(
				function(key) {

					if ( jQuery( this ).is( ':checked' ) ) {
						var store_category_id = $( this ).attr( 'data-categoryid' );
						var ebay_store_custom_category = $('#ced_ebay_store_custom_categories_'+ store_category_id).find( "option:selected" ).val();
						var ebay_store_secondary_category = $('#ced_ebay_store_secondary_categories_'+ store_category_id).find( "option:selected" ).val();
						var cat_level         = $( '#ced_ebay_categories_' + store_category_id ).find( "td:last" ).attr( 'data-catlevel' );
						var cat_level_secondary         = $( '#ced_ebay_secondary_categories_' + store_category_id ).find( "td:last" ).attr( 'data-catlevel-secondary' );
						var selected_ebay_category_id = $( '#ced_ebay_categories_' + store_category_id ).find( '.ced_ebay_level' + cat_level + '_category' ).val();
						var selected_ebay_secondary_category_id = $( '#ced_ebay_secondary_categories_' + store_category_id ).find( '.ced_ebay_level' + cat_level_secondary + '_secondary_category' ).val();

						if ( selected_ebay_category_id == '' || selected_ebay_category_id == null ) {
							selected_ebay_category_id = $( '#ced_ebay_categories_' + store_category_id ).find( '.ced_ebay_level' + (parseInt( cat_level ) - 1) + '_category' ).val();
						}

						if ( selected_ebay_secondary_category_id == '' || selected_ebay_secondary_category_id == null ) {
							selected_ebay_secondary_category_id = $( '#ced_ebay_secondary_categories_' + store_category_id ).find( '.ced_ebay_level' + (parseInt( cat_level_secondary ) - 1) + '_secondary_category' ).val();
						}
						var category_name = '';
						var secondary_category_name = '';
						$( '#ced_ebay_categories_' + store_category_id ).find( 'select' ).each(
							function(key1){
								category_name += $( this ).find( "option:selected" ).text() + ' --> ';
							}
						);
						$( '#ced_ebay_secondary_categories_' + store_category_id ).find( 'select' ).each(
							function(key1){
								secondary_category_name += $( this ).find( "option:selected" ).text() + ' --> ';
							}
						);
						var name_len = 0;
						if ( selected_ebay_category_id != '' && selected_ebay_category_id != null ) {
							ebay_category_array.push( selected_ebay_category_id );
							store_category_array.push( store_category_id );
							ebay_store_custom_category_array.push(ebay_store_custom_category);
							ebay_store_secondary_category_array.push(ebay_store_secondary_category);
							name_len      = category_name.length;
							category_name = category_name.substring( 0, name_len - 5 );
							category_name = category_name.trim();
							name_len      = category_name.length;
							if ( category_name.lastIndexOf( '--Select--' ) > 0 ) {
								category_name = category_name.trim();
								category_name = category_name.replace( '--Select--', '' );
								name_len      = category_name.length;
								category_name = category_name.substring( 0, name_len - 5 );
							}
							name_len = category_name.length;

							ebay_category_name.push( category_name );
						}
						var secondary_name_len = 0;
						if ( selected_ebay_secondary_category_id != '' && selected_ebay_secondary_category_id != null ) {
							ebay_secondary_category_array.push( selected_ebay_secondary_category_id );
							secondary_name_len      = secondary_category_name.length;
							secondary_category_name = secondary_category_name.substring( 0, secondary_name_len - 5 );
							secondary_category_name = secondary_category_name.trim();
							secondary_name_len      = secondary_category_name.length;
							if ( secondary_category_name.lastIndexOf( '--Select--' ) > 0 ) {
								secondary_category_name = secondary_category_name.trim();
								secondary_category_name = secondary_category_name.replace( '--Select--', '' );
								secondary_name_len      = secondary_category_name.length;
								secondary_category_name = secondary_category_name.substring( 0, secondary_name_len - 5 );
							}
							secondary_name_len = secondary_category_name.length;

							ebay_secondary_category_name.push( secondary_category_name );
						}
					}
				}
			);
			jQuery('#wpbody-content').append(ebay_loader_overlay);
			jQuery("#ced_ebay_progress_text").html("Creating eBay listing profiles...<br><br><p style='font-size:16px;'>If you are getting error while trying to create profile or you are stuck at infinte loading screen please contact support using the live chat button on the bottom right!</p>");
			$.ajax(
				{
					url : ajaxUrl,
					data : {
						ajax_nonce : ajaxNonce,
						action : 'ced_ebay_map_categories_to_store',
						ebay_category_array : ebay_category_array,
						ebay_secondary_category_array : ebay_secondary_category_array,
						store_category_array : store_category_array,
						ebay_store_custom_category_array : ebay_store_custom_category_array,
						ebay_store_secondary_category_array: ebay_store_secondary_category_array,
						ebay_category_name : ebay_category_name,
						ebay_secondary_category_name : ebay_secondary_category_name,
						ebay_store_id : ebay_store_id,
					},
					type : 'POST',
					success: function(response)
				{
					jQuery('#wpbody-content .ced_ebay_overlay').remove();
					var html="<div class='notice notice-success'><p>Profile Created Successfully</p></div>";
						 $("#profile_create_message").html(html);
						window.setTimeout(function(){window.location.reload();}, 3000)

					}
				}
			);

		}
	);


	$( document ). on(
		'click',
		'#ced_ebay_profile_bulk_operation',
		function(e){
			e.preventDefault();
			var operation = $( ".bulk-action-selector" ).val();
			if (operation <= 0 ) {
				var notice = "";
				notice    += "<div class='notice notice-error'><p>Please Select Operation To Be Performed</p></div>";
				$( ".success-admin-notices" ).append( notice );
			} else {
				var operation   = $( ".bulk-action-selector" ).val();
				var profile_ids = new Array();
				$( '.ebay_profile_ids:checked' ).each(
					function(){
						profile_ids.push( $( this ).val() );
					}
				);
				performProfileBulkAction( profile_ids, operation );
			}

		}
	);

	function performProfileBulkAction(profile_ids, operation)
	{
		if (profile_ids == "") {
			var notice = "";
			notice    += "<div class='notice notice-error is-dismissiable'><p>No Profiles Selected</p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>";
			$( ".success-admin-notices" ).append( notice );
		}
		$( '.ced_ebay_loader' ).show();
		$.ajax({
			url : ajaxUrl,
			data : {
				ajax_nonce : ajaxNonce,
				action : 'ced_ebay_process_profile_bulk_action',
				operation_to_be_performed : operation,
				profile_ids : profile_ids,
				user_id:user_id
				},
				type : 'POST',
				success: function(response){
				response = jQuery.parseJSON(response);
				jQuery( '.ced_ebay_loader' ).hide();
				Swal.fire({
						  title:response.title,
						  text: response.message,
						  icon: response.status,
					}).then(() => {window.location.reload();});
				}
		});
	}

	$( document ).on(
		'click',
		'#ced_ebay_bulk_operation',
		function(e){
			e.preventDefault();
			var operation = $( ".ced_ebay_select_ebay_product_action" ).val();
			if (operation <= 0 ) {
				var notice = "";
				notice    += "<div class='notice notice-error'><p>Please Select Operation To Be Performed</p></div>";
				$( ".success-admin-notices" ).append( notice );
			} else {
				if (operation == 'create_ads') {
					$( '.ced-ebay-bulk-create-ads-modal-wrapper' ).show();
					$( '.ced-ebay-bulk-create-ads-trigger' ).click(
						function() {
							$( '.ced-ebay-bulk-create-ads-modal-wrapper' ).hide();
						}
					);
					return;

				}
				var operation        = $( ".ced_ebay_select_ebay_product_action" ).val();
				var ebay_products_id = new Array();
				$( '.ebay_products_id:checked' ).each(
					function(){
						ebay_products_id.push( $( this ).val() );
					}
				);
				performBulkAction( ebay_products_id,operation );
			}

		}
	);

	function performBulkAction(ebay_products_id,operation)
		{
		if (ebay_products_id == "") {
			var notice = "";
			notice    += "<div class='notice notice-error is-dismissiable' style='margin-left:0px;margin-top:15px;'><p>No Products Selected</p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>";
			$( ".ced-ebay-products-view-notice" ).append( notice );
			return;
		}
		jQuery("#wpbody-content").append(ebay_loader_overlay);
		jQuery("#ced_ebay_progress_text").html("Processing Action...<br><br><p style='font-size:16px;'>If you are getting error while trying to upload/update products please contact support using the live chat button on the bottom right!</p>");
		$.ajax(
			{
				url : ajaxUrl,
				data : {
					ajax_nonce : ajaxNonce,
					action : 'ced_ebay_process_bulk_action',
					operation_to_be_performed : operation,
					id : ebay_products_id,
					userid:user_id
				},
				type : 'POST',
				success: function(response)
				{
					jQuery("#wpbody-content .ced_ebay_overlay").remove();
					console.log(response);
					var response  = jQuery.parseJSON( response );
					if (response.status == 200) {
						var id               = response.prodid;
						var Response_message = jQuery.trim( response.message );
						var product_title    = jQuery.trim(response.title);
						var notice           = "";

							notice              += "<div class='notice notice-success' style='margin-left:0px;margin-top:15px;'><p><b>"+response.title + "</b> >> " + response.message + ". Please refresh the page!</p></div>";

						$( ".ced-ebay-products-view-notice" ).append( notice );
						if (Response_message == 'Product Deleted Successfully') {
							$( "#" + id + "" ).html( '<b class="not_completed">Not Uploaded</b>' );
							$( "." + id + "" ).remove();
						} else {
							$( "#" + id + "" ).html( '<b class="success_upload_on_ebay">Uploaded</b>' );
						}

						var remainig_products_id = ebay_products_id.splice( 1 );
						if (remainig_products_id == "") {
							return;
						} else {
							performBulkAction( remainig_products_id,operation );
						}

					} else if (response.status == 400) {
						var notice = "";
						notice    += "<div class='notice notice-error' style='margin-left:0px;margin-top:15px;'><p><b>"+response.title + "</b> >> " + response.message + "</p></div>";
						$( ".ced-ebay-products-view-notice" ).append( notice );
						var remainig_products_id = ebay_products_id.splice( 1 );
						if (remainig_products_id == "") {
							return;
						} else {
							performBulkAction( remainig_products_id,operation );
						}

					}
				}
			}
		);
	}

	jQuery( document ).on(
		'click',
		'.ced_ebay_import_to_store',
		function(){
			jQuery( '.ced_ebay_loader' ).show();
			var itemId = jQuery( this ).attr( 'data-itemid' );
			var $this  = jQuery( this );

			jQuery.ajax(
				{
					url: ajaxUrl,
					type: 'post',
					data: {
						ajax_nonce : ajaxNonce,
						action: 'ced_ebay_import_to_store',
						itemId: itemId,
						userid: user_id
					},
					success: function(response){
						jQuery( '.ced_ebay_loader' ).hide();
						if (response == "Inactive Shop") {
							var notice = "";
							notice    += "<div class='notice notice-error'><p>Currently Shop is not Active . Please activate your Shop in order to perform operations.</p></div>";
							$( ".success-admin-notices" ).append( notice );
							return;
						}
						if (response.status == "Import Successful") {
							var notice = "";
							notice    += "<div class='notice notice-success'><p>" + response.title + " Imported Successfully!</p></div>";
							$( ".success-admin-notices" ).append( notice );
							// window.setTimeout( function(){window.location.reload()}, 1000 );
						} else {
							var notice = "";
							notice    += "<div class='notice notice-success'><p>" + response.title + " Imported Successfully!</p></div>";
							$( ".success-admin-notices" ).append( notice );
							// window.setTimeout( function(){window.location.reload()}, 1000 );
						}

					},
					fail : function( response ){
						jQuery( '.ced_ebay_loader' ).hide();
					}
				}
			);

		}
	);

	$( document ).on(
		'click',
		'#ced_ebay_bulk_import',
		function(e){
			e.preventDefault();
			var operation = $( ".bulk-action-selector" ).val();
			if (operation <= 0 ) {
				var notice = "";
				notice    += "<div class='notice notice-error'><p>Please Select Operation To Be Performed</p></div>";
				$( ".success-admin-notices" ).append( notice );
			} else {
				var operation        = $( ".bulk-action-selector" ).val();
				var ebay_products_id = new Array();
				$( '.ebay_products_id:checked' ).each(
					function(){
						ebay_products_id.push( $( this ).val() );
					}
				);
				performBulkImportAction( ebay_products_id,operation );
			}

		}
	);

	function performBulkImportAction(ebay_products_id,operation)
		{
		if (ebay_products_id == "") {
			var notice = "";
			notice    += "<div class='notice notice-error is-dismissiable'><p>No Products Selected</p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>";
			$( "#wpbody" ).prepend( notice );
			return;
		}
		$( '.ced_ebay_loader' ).show();
		$.ajax(
			{
				url : ajaxUrl,
				data : {
					ajax_nonce : ajaxNonce,
					action : 'ced_ebay_bulk_import_to_store',
					operation_to_be_performed : operation,
					products_id : ebay_products_id,
					userid:user_id
				},
				type : 'POST',
				success: function(response)
				{
					$( '.ced_ebay_loader' ).hide();
					if (response.status == "Inactive Shop") {
						var notice = "";
						notice    += "<div class='notice notice-error is-dismissible'><p>Currently Shop is not Active . Please activate your Shop in order to perform operations.</p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>";
						$( "#wpbody" ).prepend( notice );
						return;
					} else if (response.status == 'Bulk Import Successful') {
						var notice = "";
						notice    += '<div class="notice notice-success is-dismissible"><p>' + response.title + ' Imported Successfully</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
						$( "#wpbody" ).prepend( notice );
						// jQuery( document ).find( ".importStatus column-importStatus" ).append(notice);
						var remainig_products_id = ebay_products_id.splice( 1 );
						if (remainig_products_id == "") {
							return;
						} else {
							performBulkImportAction( remainig_products_id,operation );
						}

					} else {
						var notice = "";
						notice    += "<div class='notice notice-success is-dismissible inline'><p>" + response.title + " Imported Successfully</p></div>";
						// jQuery( document ).find( ".importStatus column-importStatus" ).append(notice);
						$( ".navigation" ).append( notice );
						var remainig_products_id = ebay_products_id.splice( 1 );
						console.log( remainig_products_id );
						if (remainig_products_id == "") {
							return;
							location.reload();
						} else {
							performBulkImportAction( remainig_products_id,operation );
						}

					}

				}
				}
		);
	}

	jQuery(
		function() {
			jQuery( "#accordion" ).accordion(
				{
					heightStyle: "content"
				}
			);
		}
	);

	jQuery( document ).on(
		"click",
		".notice-dismiss",
		function(event){
			event.preventDefault();
			jQuery( document ).find( ".notice" ).fadeTo(
				100,
				0,
				function(){
					jQuery( document ).find( ".notice" ).slideUp(
						100,
						function(){
							jQuery( document ).find( ".notice" ).remove();
						}
					);
				}
			);
		}
	);

	jQuery( document ).on(
		"click",
		".toggle-faq-js",
		function(){
			$( this ).toggleClass( 'ced_ebay_faq_collection_question--visable' );
			$( this ).next( '.faq-answer-js' ).slideToggle( 150 );
		}
	);

	$(document).on('click','#ced_ebay_remove_category_mapping',function(e){
		e.preventDefault();
		var term_id = $(this).attr('data-termid');
		var mapping_type = $(this).attr('data-mappingType');
		var user_id = $(this).attr('data-userId');
		$( '.ced_ebay_loader' ).show();
		jQuery.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				ajax_nonce: ajaxNonce,
				action: 'ced_ebay_remove_category_mapping',
				term_id: term_id,
				user_id:user_id,
				mapping_type:mapping_type,
			},
			success: function(response){
				$( '.ced_ebay_loader' ).hide();
				alert('Mapping removed successfully!');
			}

					});
	});



})( jQuery );
