<?php
/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package AnaLog
 */

/**
 * Prints HTML for site branding
 * 
 * @return void
 */
if ( ! function_exists( 'analog_site_branding' ) ) {

	function analog_site_branding() {

		$extended_logo = (bool) get_theme_mod( 'analog_show_only_logo', 0 );
		$branding_classes = array();
		if( true === $extended_logo && has_custom_logo() ) {
			$branding_classes[] = ' show-hide-branding';
		}
		if( has_custom_logo() ) {
			$branding_classes[] = ' has-custom-logo';
		}
		?>
		
		<div class="branding-group<?php echo esc_attr( implode( '', $branding_classes ) ); ?>">
			<?php the_custom_logo();
			if ( is_front_page() && is_home() ) :
				?>
				<h1 class="site-title branding-info"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
				<?php
			else :
				?>
				<p class="site-title branding-info"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
				<?php
			endif;
			$analog_description = get_bloginfo( 'description', 'display' );
			if ( $analog_description || is_customize_preview() ) :
				$class_prefix = '';
				if( empty( $analog_description ) && is_customize_preview() ) {
					$class_prefix = ' no-hashtag';
				}
				?>
				<p class="site-description branding-info<?php echo esc_attr( $class_prefix ); ?>"><?php echo $analog_description; /* WPCS: xss ok. */ ?></p>
			<?php endif; ?>
		</div>
		<?php

	}

}


/**
 * Prints HTML with meta information for the current post-date/time
 * 
 * @return void
 */
if ( ! function_exists( 'analog_posted_on' ) ) :

	function analog_posted_on() {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf( $time_string,
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( DATE_W3C ) ),
			esc_html( get_the_modified_date() )
		);

		$posted_on = sprintf(
			/* translators: %s: post date. */
			esc_html_x( 'Posted on %s', 'post date', 'analog' ),
			'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
		);

		echo '<span class="posted-on">' . $posted_on . '</span>'; // WPCS: XSS OK.

	}
endif;

/**
 * Prints HTML with meta information for the current author
 * 
 * @return void
 */
if ( ! function_exists( 'analog_posted_by' ) ) :

	function analog_posted_by() {
		$byline = sprintf(
			/* translators: %s: post author. */
			esc_html_x( 'by %s', 'post author', 'analog' ),
			'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">'
			. esc_html( get_the_author() ) . '</a>'
			. '</span>'
		);

		echo '<span class="byline"> ' . $byline . '</span>'; // WPCS: XSS OK.

	}
endif;

/**
 * Prints HTML with meta information for the categories, tags and comments
 * 
 * @return void
 */
if ( ! function_exists( 'analog_entry_footer' ) ) :

	function analog_entry_footer() {
		// Hide category and tag text for pages.
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_label = '<span class="cat-label">' . esc_html__('Archived:', 'analog' ) . '</span>';
			$categories_list  = get_the_category_list( esc_html_x( ', ', 'tags item separator', 'analog' ) );
			if ( $categories_list ) {
				/* translators: 2: label, list of categories. */
				printf( '<span class="cat-links">' . esc_html__( '%1$s %2$s', 'analog' ) . '</span>', $categories_label, $categories_list ); // WPCS: XSS OK.
			}

			/* translators: used between list items, there is a space after the comma */
			$tags_label = '<span class="tag-label">' . esc_html__('Tagged:', 'analog' ) . '</span>';
			$tags_list  = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'analog' ) );
			if ( $tags_list ) {
				/* translators: 2: label, list of tags. */
				printf( '<span class="tags-links">' . esc_html__( '%1$s %2$s', 'analog' ) . '</span>', $tags_label, $tags_list ); // WPCS: XSS OK.
			}
		}

		echo '<span class="sub-entry-footer">';

		if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
			echo '<span class="comments-link">';
			comments_popup_link(
				sprintf(
					wp_kses(
						/* translators: %s: post title */
						__( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'analog' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					get_the_title()
				)
			);
			echo '</span>';
		}

		edit_post_link(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__( 'Edit <span class="screen-reader-text">%s</span>', 'analog' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				get_the_title()
			),
			'<span class="edit-link">',
			'</span>'
		);

		echo '</span>';
		
	}
endif;

/**
 * Displays an optional post thumbnail
 *
 * Wraps the post thumbnail in an anchor element on index views, or a div
 * element when on single views
 * 
 * @return void
 */
if ( ! function_exists( 'analog_post_thumbnail' ) ) :

	function analog_post_thumbnail() {
		if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
			return;
		}

		if ( is_singular() ) :
			?>

			<div class="post-thumbnail">
				<?php the_post_thumbnail(); ?>
			</div><!-- .post-thumbnail -->

		<?php else : ?>

		<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php
			the_post_thumbnail( 'post-thumbnail', array(
				'alt' => the_title_attribute( array(
					'echo' => false,
				) ),
			) );
			?>
		</a>

		<?php
		endif; // End is_singular().
	}
endif;

/**
 * Display custom credits in footer
 * 
 * @return void
 */
if( ! function_exists('analog_get_my_site_cp') ) :

	function analog_get_my_site_cp() {

		$site_cp = get_theme_mod('analog_my_site_cp', '{copy}{year} {blogname}');

		if( empty( $site_cp ) ) {
			return;
		}

		$string = str_replace( array(
			'{copy}', '{year}', '{blogname}'
		),
		array( 
			'&copy;', date('Y'), get_bloginfo('name')
		), $site_cp );
		$filtered = analog_html_filter( $string );
		$output = nl2br( $filtered );

		printf( '<div class="my-site-cp"><p>%s</p></div>', $output );

	}

endif;


/**
 * Display search in footer
 *
 * @return void
 */
if( ! function_exists('analog_search_field_footer') ) :

	function analog_search_field_footer() {

		$search_field = get_theme_mod( 'analog_footer_search', 0 );

		if( (bool) $search_field === false ) : ?>
			<div class="search-box"> 
				<?php get_search_form(); ?>
			</div>
		<?php endif;

	}
	
endif;

/**
 * Display classes for header
 * 
 * Detects if custom logo is set and a class is added
 * Detects if header text is displayed and a class is added
 * 
 * @return void
 */
function analog_display_classes_for_header() {

	$has_custom_logo = has_custom_logo() ? ' has-custom-logo' : '';
	$has_header_title = display_header_text() ? ' has-header-title' : '';

	echo esc_attr( $has_custom_logo . $has_header_title );

}

/**
 * Display author info in archive
 * 
 * @return void
 */
if( ! function_exists( 'analog_archive_author_box' ) ) :

	function analog_author_archive_box( $author_id = false, $echo = true ) {
		
		if( ! $author_id ) {
			global $authordata;
			$author_id = isset( $authordata->ID ) ? $authordata->ID : 0;
		}
		
		if( (bool) $echo === true ) {
			echo analog_get_author_box( $author_id );
		} else {
			return analog_get_author_box( $author_id );
		}
		
	}

endif;

/**
 * Display author info in post
 * 
 * @return void
 */
if( ! function_exists( 'analog_author_post_box' ) ) :
 
	function analog_author_post_box( $post = false, $echo = true ) {
		
		if( false === (bool) get_theme_mod( 'analog_author_post_box' ) ) {
			return;
		}
		
		if( ! $post ) {
			global $post;
		}
		$author_id = isset( $post->post_author ) ? $post->post_author : 0;
		
		if ( ! is_single() ) {
			return;
		} else {
			if( $echo === true ) {
				echo analog_get_author_box( $author_id );
			} else{
				return analog_get_author_box( $author_id );
			}
		}
		
	}

endif;

/**
 * Display Theme Credits
 * 
 * @return void
 */
function analog_theme_footer_info() {
	
	$powered = sprintf( esc_html__('Powered by %s', 'analog' ), '<a href="https://wordpress.org/">WordPress</a>' );
	$theme 	 = sprintf( esc_html__('Theme %s by %s', 'analog' ), '<strong>AnaLog</strong>', '<a href="https://www.iljester.com/">Il Jester</a>' );
	$top = sprintf( esc_html__('%sTop%s', 'analog' ), '<a href="#">', ' <span class="barw">&barwedge;</span></a>' );
	
	$footer_info  = "<span class='tf analog-powered'>{$powered}</span>";
	$footer_info .= '<span class="tf sep">//</span>';
	$footer_info .= "<span class='tf analog-theme-credits'>{$theme}</span>";
	$footer_info .= '<span class="tf sep">//</span>';
	$footer_info .= "<span class='tf gototop'>{$top}</span>";
	
	echo $footer_info;
	
}
