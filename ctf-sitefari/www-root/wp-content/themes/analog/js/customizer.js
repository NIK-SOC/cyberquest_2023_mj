/**
 * File customizer.js.
 *
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 *
 * @package AnaLog
 */

( function( $ ) {

	// Site title and description.
	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			$( '.site-title a' ).text( to );
		} );
	} );
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			$( '.site-description' ).text( to );
			if( to == '' ) {
				$('head').append('<style type="text/css" id="no-hashtag">.site-description:before{content: " " !important;}</style>');
			} else {
				$('#no-hashtag').remove();
				$('head').append('<style type="text/css" id="no-hashtag">.site-description:before{content: "#" !important;}</style>');
			}
		} );
	} );
	
	// font size
	wp.customize( 'analog_fontsize_choices', function( value ) {
		value.bind( function( to ) {
			switch(to) {
				case 'normal': var val = 18; break;
				case 'big': var val = 20; break;
				case 'small': var val = 16; break;
			}
			$('body').css('font-size', val + 'px');
		} );
	} );
	
	// if is shown only logo
	wp.customize( 'custom_logo', function( value ) {
		value.bind( function( to ) {
			if( to < 1 && $('.branding-group').hasClass('show-hide-branding') ) {
				$('.branding-group').removeClass('show-hide-branding');
			}
		} );
	} );
	
	// if is shown only logo
	wp.customize( 'analog_show_only_logo', function( value ) {
		value.bind( function( to ) {
			var logo = wp.customize( 'custom_logo').get();
			if( to == 1 && logo > 0 ) {
				$( '.custom-logo-link' ).css('float', 'none');
				$( '.branding-info').css( {
					'clip' : 'rect(1px,1px,1px,1px)',
					'position' : 'absolute',
					'display' : 'none'
				} );
			} else {
				$( '.custom-logo-link' ).css('float', 'left');
				$( '.branding-info').css( {
					'clip' : 'none',
					'position' : 'initial'
				} );
			}
		} );
	} );

	// heads background color
	wp.customize('analog_background_color', function( value ) {
		value.bind( function( to ) {
			$('.site-branding,.widget-title,.widgettitle,.my-site-cp').css('background-color', to );
		} );
	} );

	// heads main color
	wp.customize('analog_head_main_color', function( value ) {
		value.bind( function( to ) {
			$('.site-branding, .site-branding a, .widget .widget-title, .widget .widget-title a, .widget .widgettitle, .widget .widgettitle a, .my-site-cp, .my-site-cp a').css('color', to );
		} );
	} );

	// heads site title color
	wp.customize('analog_head_site_title_color', function( value ) {
		value.bind( function( to ) {
			$('.site-branding .site-title a').css('color', to );
		} );
	} );

	// heads site description color
	wp.customize('analog_head_site_description_color', function( value ) {
		value.bind( function( to ) {
			$('.site-branding .site-description').css('color', to );
		} );
	} );
	
	// content link color
	wp.customize('analog_content_link_color', function( value ) {
		value.bind( function( to ) {
			$('.entry-content a, .comment-content a').css('color', to );
		} );
	} );
	
	// icon link internal color
	wp.customize('analog_icon_link_internal_color', function( value ) {
		value.bind( function( to ) {
			var rule = '.entry-content a.ico-internal-link:after, .page-content a.ico-internal-link:after, .comment-content a.ico-internal-link:after{color:' + to + ';}';
			$('.ico-color-internal-preview').remove();
			$('head').append('<style type="text/css" id="ico-color-internal-preview" class="ico-color-preview">' + rule + '</style>');
		} );
	} );
    
    // icon link external color
    wp.customize('analog_icon_link_external_color', function( value ) {
		value.bind( function( to ) {
			var rule = '.entry-content a.ico-external-link:after, .page-content a.ico-external-link:after, .comment-content a.ico-external-link:after{color:' + to + ';}';
			$('.ico-color-external-preview').remove();
			$('head').append('<style type="text/css" id="ico-color-external-preview" class="ico-color-preview">' + rule + '</style>');
		} );
	} );
	
} )( jQuery );
