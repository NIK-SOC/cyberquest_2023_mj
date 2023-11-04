<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package AnaLog
 */

get_header();
?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main">

		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<?php
				the_archive_title( '<h1 class="page-title">', '</h1>' );
				
				if( !is_author() ) {
					the_archive_description( '<div class="archive-description">', '</div>' );
                } else {
                    analog_author_archive_box();
                }
				?>
			</header><!-- .page-header -->

			<?php
			/* Start the Loop */
			while ( have_posts() ) :
				the_post();

				/*
				 * Include the Post-Type-specific template for the content.
				 * If you want to override this in a child theme, then include a file
				 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
				 */
				get_template_part( 'template-parts/content', get_post_type() );

			endwhile;

			the_posts_pagination( array(
				'prev_text' => '&larr; <span class="screen-reader-text">' . __( 'Previous Page', 'analog' ) . '</span>',
				'next_text' => '<span class="screen-reader-text">' . __( 'Next Page', 'analog' ) . '</span> &rarr;',
				'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'analog' ) . ' </span>',
			) );

		else :

			get_template_part( 'template-parts/content', 'none' );

		endif;
		?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_sidebar();
get_footer();
