(function( $ ) {
	$( document ).ready(function() {
		/* Show/Hide options in plugin settings*/
		$( 'input[name="lnkdn_share"]' ).change(function() {
			if ( $( this ).is( ":checked" ) ) {
				$( '.lnkdn-share-options' ).show();
			} else {
				$( '.lnkdn-share-options' ).hide();
			}
		});

		$( 'input[name="lnkdn_follow"]' ).change(function() {
			if ( $( this ).is( ":checked" ) ) {
				$( '.lnkdn-follow-options' ).show();
			} else {
				$( '.lnkdn-follow-options' ).hide();
			}
		});

		/* Show/Hide options in admin-page for widgets */
		$( 'div[id*="lnkdn_main"] p' ).each(function(){
			if ( $( this ).hasClass( 'lnkdn-hide-option' ) ) {
				$( this ).hide();
			}
		});

		$( '.widgets-holder-wrap' ).on( 'change', 'select[name*="lnkdn_select_widget"]', function() {
			var value = $( this ).val();
			var parent = $( this ).parent().parent();
			parent.find( 'p:not(.lnkdn_all):not(.lnkdn_' +value+ ')' ).hide();
			parent.find( 'p.lnkdn_all, p.lnkdn_' +value ).show();
			if ( parent.find( 'select[name*="lnkdn_display_mode"]' ).parent().is( ":visible" ) ) {
				if ( 'icon' == parent.find( 'select[name*="lnkdn_display_mode"]' ).val() ) {
					parent.find( 'select[name*="lnkdn_behavior"]' ).parent().show();
				}
			}

			if ( 'company_profile' == value ) {
				if( parent.find( '.lnkdn_company_profile_help' ).not( ":visible" ) ) {
					parent.find( '.lnkdn_company_profile_help' ).removeAttr( 'style' );
				} 
			} else {
				parent.find( '.lnkdn_company_profile_help' ). attr( 'style', 'visibility:hidden' );
			}

			if ( 'company_insider' == value ) {
				if( parent.find( '.lnkdn_company_insider_help' ).not( ":visible" ) ) {
					parent.find( '.lnkdn_company_insider_help' ).removeAttr( 'style' );
				}
			} else {
				parent.find( '.lnkdn_company_insider_help' ).attr( 'style', 'visibility:hidden' );
			}

			if ( 'jymbii' == value ) {
				if( parent.find( '.lnkdn_jymbii_help' ).not( ":visible" ) ) {
					parent.find( '.lnkdn_jymbii_help' ).removeAttr( 'style' );
				}
			} else {
				parent.find( '.lnkdn_jymbii_help' ).attr( 'style', 'visibility:hidden' );
			}
			
		});
		
		$( '.widgets-holder-wrap' ).on( 'change', 'select[name*="lnkdn_display_mode"]', function() {
			if ( 'inline' == $( this ).val() ) {
				$( '.lnkdn_inline' ).hide();
			} else {
				$( '.lnkdn_inline' ).show();
			}
		});

		$( '.widgets-holder-wrap' ).on( 'change', 'select[name*="lnkdn_display_jobs_mode"]', function() {
			if ( 'all_jobs' == $( this ).val() ) {
				$( '.lnkdn_all_jobs' ).hide();
			} else {
				$( '.lnkdn_all_jobs' ).show();
			}
		});
	});
}) ( jQuery );

