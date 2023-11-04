<?php
/**
 * AnaLog Theme Customizer
 * 
 * @package AnaLog
 */

/**
 * Available fonts
 * 
 * @return array
 */
function analog_fonts() {
	
	$fonts = array(
		'ibm_plex_mono'	 => 'IBM Plex Mono',
		'inconsolata'	 => 'Inconsolata',
		'roboto_mono'	 => 'Roboto Mono',
		'source_code_pro'=> 'Source Code Pro',
		'cousine'		 => 'Cousine',
		'ubuntu_mono'	 => 'Ubuntu Mono',
		'courier_prime'	 => 'Courier Prime',
		'space_mono'	 => 'Space Mono',
		'anonymous_pro'	 => 'Anonymous Pro',
		'dm_mono'		 => 'DM Mono',
		'fira_code'	     => 'Fira Code',
		'noto-serif'	 => 'Noto Serif',
		'noto-sans'		 => 'Noto Sans',
		'open-sans'		 => 'Open Sans',
        'roboto'		 => 'Roboto',
		'roboto-slab'	 => 'Roboto Slab',
		'roboto-serif'   => 'Roboto Serif',
		'share-tech'     => 'Share Tech',
		'system_ui' 	 => 'System UI' 
	);
	return $fonts;

}

/**
 * Font styles
 * 
 * @return array
 */
function analog_font_styles() {
	
		$font_sw = array( 
		'300' => __('Light', 'analog' ),
		'300i'=> __('Light Italic', 'analog' ), 
		'400' => __('Normal', 'analog' ), 
		'400i'=> __('Normal Italic', 'analog'), 
		'500' => __('Medium', 'analog' ), 
		'500i'=> __('Medium Italic', 'analog' ),
		'700' => __('Bold', 'analog'), 
		'700i'=> __('Bold Italic', 'analog')
	);
	
	return $font_sw;
}

/**
 * Create an array of colors
 * 
 * @return array
 */
function analog_colors() {

	$analog_colors = array(
		'analog_background_color' => array(
			'label' => __('Heads background color', 'analog'),
			'default' => '#1878cc',
			'css' => 'background-color',
			'target' => '.site-branding,.widget .widget-title,.widget .widgettitle,.my-site-cp'
		),
		'analog_head_main_color' => array(
			'label' => __('Heads main text color', 'analog'),
			'default' => '#ffffff',
			'css' => 'color',
			'target' => '.site-branding, .site-branding a, .widget .widget-title, .widget .widget-title a, .widget .widgettitle, .widget .widgettitle a, .my-site-cp, .my-site-cp a'
		),
		'analog_head_site_title_color' => array(
			'label' => __('Site title color', 'analog'),
			'default' => '#ffffff',
			'css' => 'color',
			'target' => '.site-branding .site-title a'
		),
		'analog_head_site_description_color' => array(
			'label' => __('Site description color', 'analog'),
			'default' => '#e2e2e2',
			'css' => 'color',
			'target' => '.site-branding .site-description'
		),
		'analog_content_link_color' => array(
			'label' => __('Content link color', 'analog'),
			'default' => '#0084a5',
			'css' => 'color',
			'target' => '.entry-content a:not(.more-link), .comment-content a'
		),	
		'analog_icon_link_internal_color' => array(
			'label' => __('Icon internal link color', 'analog'),
			'default' => '#a0a0a0',
			'css' => 'color',
			'target' => '.entry-content a.ico-internal-link:after, .page-content a.ico-internal-link:after, .comment-content a.ico-internal-link:after'
		),
        'analog_icon_link_external_color' => array(
			'label' => __('Icon external link color', 'analog'),
			'default' => '#FF0000',
			'css' => 'color',
			'target' => '.entry-content a.ico-external-link:after, .page-content a.ico-external-link:after, .comment-content a.ico-external-link:after'
		)
	);

	return $analog_colors;

}

/**
 * Default targets to which not to apply the link icon
 * 
 * @return string
 */
function analog_icon_links_target_not() {
	
	return '.wp-block-button a, .post-page-numbers, .wp-block-archives a, .wp-block-categories a, .wp-block-latest-comments a, .wp-block-latest-posts a, figure.alignleft a, .figure.alignright a, figure.alignnone a, figure.aligncenter a, .more-link, .post-box-author .author-links a';
	
}

/**
 * Class notice
 * 
 * @return void
 */
if( class_exists( 'WP_Customize_Control' ) ) {
	class Analog_Notice extends WP_Customize_Control {
	   public $type = 'analog_notice';
	   public function render_content() {
	   ?>
	   <div class="analog-notice-custom-control">
		  <?php if( !empty( $this->label ) ) { ?>
			 <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		  <?php } ?>
		  <?php if( !empty( $this->description ) ) { ?>
			 <span class="customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span>
		  <?php } ?>
	   </div>
	   <?php
	   }
	}
}

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function analog_customize_register( $wp_customize ) {
	
	/**
	 * Custom sections
	 */
	$wp_customize->add_section( 'analog_content', array(
		'title' => __('Content', 'analog'),
		'priority'   => 100
	) );
	
	$wp_customize->add_section('analog_typography', array(
		'title' => __( 'Typography', 'analog' ),
		'priority' => 50
	));
	
	$wp_customize->add_section( 'analog_header', array(
		'title' => __('Header', 'analog'),
		'priority'   => 99
	) );
	
	$wp_customize->add_section( 'analog_footer', array(
		'title' => __('Footer', 'analog'),
		'priority'   => 110
	) );
	
	/**
	 * Create partial refresh
	 */
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'background_image' )->transport = 'refresh';
	$wp_customize->get_control( 'custom_logo' )->description = sprintf( __('The image is required to have a minimum height of %s. A smaller height could create a grain of the logo.', 'analog' ), '<strong>120 pixel</strong>' );

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial( 'blogname', array(
			'selector'        => '.site-title a',
			'render_callback' => 'analog_customize_partial_blogname',
		) );
		$wp_customize->selective_refresh->add_partial( 'blogdescription', array(
			'selector'        => '.site-description',
			'render_callback' => 'analog_customize_partial_blogdescription',
		) );
		$wp_customize->selective_refresh->add_partial( 'analog_show_only_logo', array(
			'selector'        => '.site-branding',
			'render_callback' => 'analog_customize_partial_show_only_custom_logo',
		) );
	}
	
	/**
	 * Show only logo setting
	 */
	$wp_customize->add_setting('analog_show_only_logo', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'postMessage',
		'capability'        => 'edit_theme_options',
	));

	$wp_customize->add_control('analog_show_only_logo', array(
		'label'      => __('Show only logo', 'analog'),
		'description' => __('Title and description will be hidden.', 'analog'),
		'section'    => 'title_tagline',
		'settings'   => 'analog_show_only_logo',
		'type'       => 'checkbox'
	));

	/**
	 * Fixed header setting
	 */
	$wp_customize->add_setting('analog_fixed_branding', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
		'capability'        => 'edit_theme_options',
	));

	$wp_customize->add_control('analog_fixed_branding', array(
		'label'      => __('Fixed Header', 'analog'),
		'description' => __('Keep header (branding and menu) fixed when you scroll the page.', 'analog'),
		'section'    => 'analog_header',
		'settings'   => 'analog_fixed_branding',
		'type'       => 'checkbox'
	));
	
	/**
	 * Colors setting
	 */
	$analog_colors = analog_colors();
	
	foreach( $analog_colors as $k => $v ) {
	
		$wp_customize->add_setting( $k, 
			array(
				'default'    => sanitize_hex_color( $v['default'] ),
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'  => 'postMessage',
				'capability' => 'edit_theme_options', 
			) 
		);      

		$wp_customize->add_control( new WP_Customize_Color_Control( 
			$wp_customize,
				$k, 
				array(
				'label'      => esc_html( $v['label'] ), 
				'settings'   => $k, 
				'priority'   => 10, 
				'section'    => 'colors', 
			) 
		) );
		
	}
    
    /**
     * No texture setting
     */
    $wp_customize->add_setting('analog_no_texture', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
		'capability'        => 'edit_theme_options',
	));
    
    $wp_customize->add_control('analog_no_texture', array(
		'label'      => __('Background solid color', 'analog'),
		'description' => __('Remove texture from the background.', 'analog'),
		'section'    => 'colors',
		'settings'   => 'analog_no_texture',
		'type'       => 'checkbox',
        'priority'   => 1
	));
	
	/**
	 * Hyphenation
	 */
	$wp_customize->add_setting( 'analog_note_hyphenation',
	   array(
		  'default' => '',
		  'transport' => 'refresh',
		  'sanitize_callback' => 'wp_filter_nohtml_kses'
	   )
	);
	$wp_customize->add_control( new Analog_Notice( $wp_customize, 'analog_note_hyphenation',
	   array(
		  'label' => __( 'Text Hyphenation', 'analog' ),
		  'description' => __('Force text\'s hyphenation. The text will be justified and divided into syllables like in a book.', 'analog'),
		  'section' => 'analog_content'
	   )
	) );
	
	$wp_customize->add_setting('analog_hyphenation', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
		'capability'        => 'edit_theme_options',
	));

	$wp_customize->add_control('analog_hyphenation', array(
		'label'      => __('Hyphenate text', 'analog'),
		'section'    => 'analog_content',
		'settings'   => 'analog_hyphenation',
		'type'       => 'checkbox'
	));
	
	
	/**
	 * The author post box setting
	 */
	$wp_customize->add_setting( 'analog_note_box_author',
	   array(
		  'default' => '',
		  'transport' => 'refresh',
		  'sanitize_callback' => 'wp_filter_nohtml_kses'
	   )
	);
	$wp_customize->add_control( new Analog_Notice( $wp_customize, 'analog_note_box_author',
	   array(
		  'label' => __( 'Author Post Box', 'analog' ),
		  'description' => __('Display author box in post bottom.', 'analog'),
		  'section' => 'analog_content'
	   )
	) );
	
	 $wp_customize->add_setting('analog_author_post_box', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
		'capability'        => 'edit_theme_options',
	));

	$wp_customize->add_control('analog_author_post_box', array(
		'label'      => __('Show Author Box', 'analog'),
		'section'    => 'analog_content',
		'settings'   => 'analog_author_post_box',
		'type'       => 'checkbox'
	));
	
	/**
	 * Icon link setting
	 */
	$wp_customize->add_setting( 'analog_note_ico',
	   array(
		  'default' => '',
		  'transport' => 'refresh',
		  'sanitize_callback' => 'wp_filter_nohtml_kses'
	   )
	);
	$wp_customize->add_control( new Analog_Notice( $wp_customize, 'analog_note_ico',
	   array(
		  'label' => __( 'Ico Link', 'analog' ),
		  'description' => __('Will be shown a ico link in every link in content post or page.', 'analog'),
		  'section' => 'analog_content'
	   )
	) );
	 
	$wp_customize->add_setting('analog_ico_link_enable', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
		'capability'        => 'edit_theme_options',
	));

	$wp_customize->add_control('analog_ico_link_enable', array(
		'label'      => __('Enable', 'analog'),
		'description' => __('You can choose a different color for the internal and external links in the colors section.', 'analog'),
		'section'    => 'analog_content',
		'settings'   => 'analog_ico_link_enable',
		'type'       => 'checkbox'
	));
	
	$wp_customize->add_setting('analog_ico_link_ext', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
		'capability'        => 'edit_theme_options',
	));

	$wp_customize->add_control('analog_ico_link_ext', array(
		'label'      => __('Only external links', 'analog'),
		'description' => __('The icon will be shown only for external links.', 'analog'),
		'section'    => 'analog_content',
		'settings'   => 'analog_ico_link_ext',
		'type'       => 'checkbox'
	));

	$wp_customize->add_setting('analog_ico_link_not', array(
		'default'           => analog_icon_links_target_not(),
		'sanitize_callback' => 'sanitize_textarea_field',
		'transport'         => 'refresh',
		'capability'        => 'edit_theme_options',
	));

	$wp_customize->add_control('analog_ico_link_not', array(
		'label'      => __('Exclude target ico link', 'analog'),
		'description' => __('Insert, separate by comma, classes or IDs, to exclude ico link from certain URLs. Use reset button to restore defaults.', 'analog'),
		'section'    => 'analog_content',
		'settings'   => 'analog_ico_link_not',
		'type'       => 'textarea'
	));
	
	$wp_customize->add_setting('analog_ico_link_reset_target_not', array(
		'default'           => '1',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'postMessage',
		'capability'        => 'edit_theme_options',
	));

	$wp_customize->add_control('analog_ico_link_reset_target_not', array(
		'type' => 'button',
		'settings' => array(),
		'priority' => 10,
		'section' => 'analog_content',
		'input_attrs' => array(
			'value' => __( 'Reset Values', 'analog' ),
			'class' => 'button', 
    	),
	));

	/**
	 * Footer info setting
	 */
	$wp_customize->add_setting('analog_my_site_cp', array(
		'default'           => '{copy}{year} {blogname}',
		'sanitize_callback' => 'analog_html_filter',
		'transport'         => 'refresh',
		'capability'        => 'edit_theme_options',
	));

	$wp_customize->add_control('analog_my_site_cp', array(
		'label'      => __('Site Info', 'analog'),
		'description' => __('Allowed tags: strong, em. Press "Enter" to wrap up. Use {copy} for &copy;, {year} for current year, {blogname} for blog name.', 'analog'),
		'section'    => 'analog_footer',
		'settings'   => 'analog_my_site_cp',
		'type'       => 'textarea'
	));

	/**
	 * Footer search setting
	 */
	$wp_customize->add_setting('analog_footer_search', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
		'capability'        => 'edit_theme_options',
	));

	$wp_customize->add_control('analog_footer_search', array(
		'label'      => __('Search Field', 'analog'),
		'description' => __('Hide search field in the footer.', 'analog'),
		'section'    => 'analog_footer',
		'settings'   => 'analog_footer_search',
		'type'       => 'checkbox'
	));
	
	/**
	 * Typography setting
	 */
	$wp_customize->add_setting('analog_typography_choices', array(
		'default'           => 'ibm_plex_mono',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
		'capability'        => 'edit_theme_options',
	));

	$wp_customize->add_control('analog_typography_choices', array(
		'label'      => __('Font', 'analog'),
		'description' => __('Choose your font (default: IBM Plex Mono). Note: System Ui is the font used in the user\'s operating system/browser.', 'analog'),
		'section'    => 'analog_typography',
		'settings'   => 'analog_typography_choices',
		'type'       => 'select',
		'choices'    => analog_fonts()
	));
	
	$wp_customize->add_setting('analog_fontsize_choices', array(
		'default'           => 'normal',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'postMessage',
		'capability'        => 'edit_theme_options',
	));

	$wp_customize->add_control('analog_fontsize_choices', array(
		'label'      => __('Font Size', 'analog'),
		'description' => __('Change the font size according to your needs.', 'analog'),
		'section'    => 'analog_typography',
		'settings'   => 'analog_fontsize_choices',
		'type'       => 'select',
		'choices'    => array(
			'small' => __('Small', 'analog'),
			'normal' => __('Normal', 'analog'),
			'big' => __('Big', 'analog')
		)
	));
	
	$wp_customize->add_setting( 'analog_note_typography',
	   array(
		  'default' => '',
		  'transport' => 'refresh',
		  'sanitize_callback' => 'wp_filter_nohtml_kses'
	   )
	);
	$wp_customize->add_control( new Analog_Notice( $wp_customize, 'analog_note_typography',
	   array(
		  'label' => __( 'Font Styles', 'analog' ),
		  'description'  => __( 'Before using any of the styles below, check that the font supports it. Default: Normal, Normal Italic, Bold, Bold Italic.', 'analog' ),
		  'section' => 'analog_typography'
	   )
	) );
	
	$font_sw = analog_font_styles();
	foreach( $font_sw as $sw => $label ) {
		$defaults = array( '400', '400i', '700', '700i' );
		$value = 0;
		if( in_array( $sw, $defaults ) ) {
			$value = 1;
		}
		$wp_customize->add_setting('analog_sw_' . $sw, array(
			'default'           => $value,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
			'capability'        => 'edit_theme_options',
		));

		$wp_customize->add_control('analog_sw_' . $sw, array(
			'label'      => $label,
			'section'    => 'analog_typography',
			'settings'   => 'analog_sw_' . $sw,
			'type'       => 'checkbox'
		));
	}
	
}
add_action( 'customize_register', 'analog_customize_register' );

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function analog_customize_partial_show_only_custom_logo() {
	analog_site_branding();
}

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function analog_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function analog_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function analog_customize_preview_js() {
	wp_enqueue_script( 'analog-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20151215', true );
}
add_action( 'customize_preview_init', 'analog_customize_preview_js' );

/**
 * Set customizer scripts in head
 * 
 * @return void
 */
function analog_header_customizer_scripts() {

	?>

	<script type="text/javascript" id="analog-header-customizer-scripts">

	function analogSelectiveStripTags( string ) {

		var x = string.split( " " );
		var n = x.length;
		var t = Array();
		var regex = /<(.*?)>/gm;
		for( i = 0; i < n; i++ ) {
			
			if( x[i].search(regex) > -1 && x[i].search(/<\/?strong>/gm) > -1 ) {
					t[i] = 0;
			} 
			else if( x[i].search(regex) > -1 && x[i].search(/<\/?em>/gm) > -1 ) {
					t[i] = 0;
			}
			else {
					if( x[i].search(regex) > -1 ) {
						t[i] = 1;
					} else {
						t[i] = 0;
					}
			}
		}

		var xt = t.join(" ");

		if( xt.search('1') > -1 ) {
			return 1;
		} else {
			return 0;
		}

	}

	jQuery(function($) {
		
		wp.customize('custom_logo', function( setting ) {
			setting.bind( function( value ) {
				if( value < 1 ) {
					$('#_customize-input-analog_show_only_logo').prop('checked', false );
				}
			} );	
		});
		
		wp.customize.control( 'analog_ico_link_reset_target_not', function( control ) {
			control.container.find( '.button' ).on( 'click', function() {
				var target_not = '<?php echo sanitize_text_field( analog_icon_links_target_not() ); ?>';
				$('#_customize-input-analog_ico_link_not').val(target_not);
				// active save button to change status
				$('#_customize-input-analog_ico_link_not').trigger('change');
			} );
		} );
		
		wp.customize( 'analog_show_only_logo', function( setting ) {
			setting.bind( function( value ) {
				var code 	  = 'not_logo';
				var logo	  = wp.customize('custom_logo').get();
				if ( value == 1 && logo < 1 ) {
					setting.notifications.add( code, new wp.customize.Notification(
						code,
						{
							type: 'warning',
							message: '<?php echo esc_html_e( "You cannot show only logo without a logo!", "analog" ); ?>'
						}
					) );
				} else {
					setting.notifications.remove( code );
				}
			} );

		} );

		wp.customize( 'analog_my_site_cp', function( setting ) {
			setting.bind( function( value ) {
				var code 	  = 'tag_unallowed';

				if ( analogSelectiveStripTags(value) == 1 ) {

					setting.notifications.add( code, new wp.customize.Notification(
						code,
						{
							type: 'error',
							message: '<?php echo esc_html_e( "You are trying to use an html tag not allowed!", "analog" ); ?>'
						}
					) );
				} else {
					setting.notifications.remove( code );
				}
			} );

		} );

	} );
	
	</script><?php
}
add_action('customize_controls_print_scripts', 'analog_header_customizer_scripts');

/**
 * Set scripts in customizer footer
 * 
 * @return void
 */
function analog_footer_customizer_scripts() {
	
	?><script type="text/javascript" id="xsimply-footer-customizer-scripts">
	jQuery(function($) {
		var is_custom_logo = wp.customize('custom_logo').get();
		if( is_custom_logo < 1 ) {
			$('#_customize-input-analog_show_only_logo').prop('checked', false );
		}
	});
	</script><?php
}
add_action('customize_controls_print_footer_scripts', 'analog_footer_customizer_scripts', 999 );

/**
 * Add some css rules for customizer
 * 
 * @return void
 */
function analog_customizer_styles() {
	
	?>
	<style type="text/css">
		.customize-control-analog_notice {
			margin: 0;
		}
	</style><?php
	
}
add_action('customize_controls_print_styles', 'analog_customizer_styles');
