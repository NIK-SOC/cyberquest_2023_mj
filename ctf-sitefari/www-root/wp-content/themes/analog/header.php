<?php
/**
 * The header part
 *
 * @package AnaLog
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'analog' ); ?></a>

	<header id="masthead" class="site-header onscroll-header">
		
		<div class="site-branding">
			<?php analog_site_branding(); ?>
		</div><!-- .site-branding -->

		<nav id="header-navigation" class="main-navigation">
			<button class="menu-toggle" aria-controls="primary-menu" data-menu="primary-menu" aria-expanded="false">
				<span class="button-menu"><?php esc_html_e('Menu', 'analog' ); ?></span>
			</button>
			<?php
			wp_nav_menu( array(
				'theme_location' => 'menu-1',
				'menu_id'        => 'primary-menu'
			) );
			?>
		</nav><!-- #site-navigation -->
	</header><!-- #masthead -->

	<div id="content" class="site-content">
