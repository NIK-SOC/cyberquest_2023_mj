/*!
 * Menu nav
 * 
 * @package AnaLog
 */

jQuery(function($) {

    var tgl = 200;

    $('button.menu-toggle').click( function( e ) {
        e.preventDefault();

        var menu_id = $('button.menu-toggle').attr('data-menu');
        $('.main-navigation ul#' + menu_id).slideToggle( tgl, function() {
				if( $(this).is(':hidden') ) {
					$(this).removeAttr('style');
				}
		 } );

        if( $('.main-navigation').hasClass('toggled') ) {
            $('.main-navigation').removeClass('toggled');
            $('.sub-menu').hide(tgl).removeAttr('style');
            $( this ).attr('aria-expanded', 'false' );
        } else {
            $('.main-navigation').addClass('toggled');
            $( this ).attr('aria-expanded', 'true' );
        }
    });

    $('.menu-item-has-children > a').attr('href', '#');

    $(document).on('click', '.menu-item-has-children > a', function(e) {
        e.preventDefault();
        var menuToggle = $(this).parent().children('.sub-menu');
        menuToggle.slideToggle( tgl, function() {
            $(this).find('ul').hide(tgl).removeAttr('style');
        } );
    });

    $('.site-branding, .custom-header, #content, #colophon').on('click', function() {
        $('.sub-menu').hide(tgl).removeAttr('style');
    });

});
