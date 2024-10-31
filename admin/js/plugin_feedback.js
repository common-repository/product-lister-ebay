jQuery( document ).ready(
	function($) {
		$( '.deactivate a' ).each(
			function(i, ele) {
				if ($( ele ).attr( 'href' ).indexOf( 'product-lister-ebay' ) > -1) {
					$( '#ced-ebay-feedback-modal' ).find( 'a' ).attr( 'href', $( ele ).attr( 'href' ) );

					$( ele ).on(
						'click',
						function(e) {
							e.preventDefault();
							console.log( 'hi' );
							if ( ! $( '#ced-ebay-feedback-modal' ).length) {
								window.location.href = $( ele ).attr( 'href' );
								return;
							}

							$( '#ced-ebay-feedback-response' ).html( '' );
							$( '#ced-ebay-feedback-modal' ).css( 'display', 'block' );
						}
					);

					$( '#ced-ebay-feedback-modal .ced-ebay-close' ).on(
						'click',
						function() {
							$( '#ced-ebay-feedback-modal' ).css( 'display', 'none' );
						}
					);

					$( 'input[name="ced-ebay-feedback"]' ).on(
						'change',
						function(e) {
							if ($( this ).val() == 4) {
								$( '#ced-ebay-feedback-other' ).show();
							} else {
								$( '#ced-ebay-feedback-other' ).hide();
							}
						}
					);

					$( '#ced-ebay-submit-feedback-button' ).on(
						'click',
						function(e) {
							e.preventDefault();

							$( '#ced-ebay-feedback-response' ).html( '' );

							if ( ! $( 'input[name="ced-ebay-feedback"]:checked' ).length) {
								$( '#ced-ebay-feedback-response' ).html( '<div style="color:#cc0033;font-weight:800">Please select your feedback.</div>' );
							} else {
								$( this ).val( 'Loading...' );
								$.post(
									ajaxurl,
									{
										action: 'ced_ebay_submit_feedback',
										feedback: $( 'input[name="ced-ebay-feedback"]:checked' ).val(),
										others: $( '#ced-ebay-feedback-other' ).val(),
										ajax_nonce: ced_ebay_admin_obj.ajax_nonce,
									},
									function(response) {
										window.location = $( ele ).attr( 'href' );
									}
								).always(
									function() {
										window.location = $( ele ).attr( 'href' );
									}
								);
							}
						}
					);
				}
			}
		);
	}
);
