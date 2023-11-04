<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package AnaLog
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function analog_body_classes( $classes ) {

	// if is set background image
	$background_image = get_background_image();
	if( filter_var( $background_image, FILTER_VALIDATE_URL ) !== false ) {
		$classes[] = 'has-background-image';
	}

	// Add a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Add a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}
    
    // Add class for background texture
    if( (bool) get_theme_mod('analog_no_texture') === false ) {
        $classes[] = 'has-bg-texture';
    }
    
    // add hyphenator
	if( true === (bool) get_theme_mod('analog_hyphenation') ) {
		$classes[] = 'force-text-hyphens';
	}

	return $classes;
}
add_filter( 'body_class', 'analog_body_classes' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 * 
 * @return void
 */
function analog_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'analog_pingback_header' );

/**
 * Get author info
 * 
 * @param int $author_id the author id
 * 
 * @return string html
 */
function analog_get_author_box( $author_id = null ) {
	
	if( absint( $author_id ) <= 0 ) {
		return;
	}
	
	// Author name
	$display_name = get_the_author_meta( 'display_name', $author_id );

	// Use nickname if display name is empty
	if ( empty( $display_name ) ) {
		$display_name = get_the_author_meta( 'nickname', $author_id );
	}

	// Author description
	$user_description = get_the_author_meta( 'user_description', $author_id );

	// Author URL
	$user_website = get_the_author_meta('url', $author_id);

	// Post for this author
	$user_posts = get_author_posts_url( get_the_author_meta( 'ID' , $author_id ) );

	// About title
	if ( ! empty( $display_name ) ) {
		$author_details = sprintf( __( '%sAbout %s%s', 'analog' ), '<p class="author-name"><span>', $display_name, '</span></p>' );
	}

	// Details
	if ( ! empty( $user_description ) ) {
		// Author avatar and bio
		$author_details .= '<p class="author-details">' . get_avatar( get_the_author_meta('user_email') , 90 ) . $user_description . '</p>';
	}

	// All posts by author
	if( ! is_archive() ) {
		$author_details .= sprintf( __( '%sView all posts%s', 'analog' ), '<p class="author-links"><a class="author-posts" href="'. esc_url( $user_posts ) .'">', '</a>' );  
	} else {
		$author_details .= '<p class="author-links">';
	}

	// Check if author has a website in their profile
	if ( ! empty( $user_website ) ) {
		// Display author website link
		if( ! is_archive() ) {
			$author_details .= '<span class="sep">::</span>';
		}
		$author_details .= '<a class="author-web-site" href="' . esc_url( $user_website ) .'" rel="nofollow">Website</a>';
		$author_details .= '<span class="sep">::</span>';
		$author_details .= sprintf( __('%sSubscribe Feeds%s', 'analog' ), '<a href="' . esc_url( get_author_feed_link( $author_id ) ) . '">', '</a>' );
		$author_details .= '</p>';
	} else {
		if( ! is_archive() ) {
			$author_details .= '<span class="sep">::</span>';
		}
		$author_details .= sprintf( __('%sSubscribe Feeds%s', 'analog' ), '<a href="' . esc_url( get_author_feed_link( $author_id ) ) . '">', '</a>' );
		// if there is no author website then just close the paragraph
		$author_details .= '</p>';
	}
	
	return '<div class="post-box-author" >' . $author_details . '</div>';;
	
}

/**
 * Retrieve link for feeds
 */
function analog_get_feeds_link() {

	$item = get_queried_object();
	
	if( is_category() || is_tag() || is_tax() ) {
		$link = get_term_feed_link( $item->term_id, $item->taxonomy );
	}
	elseif( is_post_type_archive() ) {
		$link = get_post_type_archive_feed_link( $item->name );
	}
	
	return $link;
}


/**
 * Remove prefix from archive title
 * @see: https://developer.wordpress.org/reference/functions/get_the_archive_title/
 * 
 * @param string $title the archive's title
 * @return string
 */
function analog_archive_title( $title ) {
    if ( is_category() ) {
        $title = single_cat_title( '', false );
    } elseif ( is_tag() ) {
        $title = single_tag_title( '', false );
    } elseif ( is_author() ) {
        $title = '<span class="vcard">' . get_the_author() . '</span>';
    } elseif ( is_post_type_archive() ) {
        $title = post_type_archive_title( '', false );
    } elseif ( is_tax() ) {
        $title = single_term_title( '', false );
	}
  
    return $title;
}

/**
 * Filter archive description 
 * 
 * @param string $description the archive description
 * @return string
 */
function analog_archive_description( $description ) {
	
	add_filter( 'get_the_archive_title', 'analog_archive_title' );
	$item = get_the_archive_title();
	remove_filter('get_the_archive_title', 'analog_archive_title' );
	
	$content = $description;
	$feeds = analog_get_feeds_link();
	
	$description = sprintf( __( '%sAbout %s%s', 'analog' ), '<p class="item-name">', $item, '</p>' );
	$description .= $content;
	$description .= sprintf( __( '%sSubscribe Feeds%s', 'analog' ), '<p><a class="item-feeds" href="' . esc_url( $feeds ) .'" rel="nofollow">', '</a></p>' );
    
    return $description;
    
}
add_filter( 'get_the_archive_description', 'analog_archive_description' );
