<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package AnaLog
 */

?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer">
		<?php analog_get_my_site_cp(); ?>
		<nav id="footer-navigation" class="sub-navigation">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'menu-2',
				'menu_id'        => 'secondary-menu',
				'depth'			 => 1
			) );
			?>
		</nav><!-- #site-navigation -->
		<?php analog_search_field_footer(); ?>
		<div class="site-info">
			<?php analog_theme_footer_info(); ?>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
