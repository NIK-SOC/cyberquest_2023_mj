<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'sitefari' );

/** Database username */
define( 'DB_USER', 'sitefari' );

/** Database password */
define( 'DB_PASSWORD', 'OsEDyxKhyNjGxdW4wovvKMjcclcjoJlG1emeyhzQ' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ' L5P+^EKzM4N!Xv>CEi+*!noRfsluIs6_5XV$%EvS&dziff&@.=@8Q0MU>olm6ua' );
define( 'SECURE_AUTH_KEY',  ' |]:$</HJ0WE6exj)|= KC_T&a9#0<W%]F!#:u2.4|ee3OEPJ}|b7}m&m`>is`jF' );
define( 'LOGGED_IN_KEY',    '_1lzmQ|7,}[]g5/L)e@qWU5}#*lZCIBz>ogq/(EqAtmEB|RE]?^cARoD*an$Z7a#' );
define( 'NONCE_KEY',        '(0?watKWhIkJ!(0`XUm?K&h!v8&*(5eQwYP9 YACH_g<C`Ei2-rr*j66&M0-R/vU' );
define( 'AUTH_SALT',        ',kpoH23q-zYfxQ=salCv>YIE/E|}a)1ahBg(&(vcXRw*ik$H%vho^Z1T#gSM;AmR' );
define( 'SECURE_AUTH_SALT', '|Aa pxD]fp)flARKzoNa)bFF4ZEPCdxinR!b|(c-NmTfs*P-hdp.|f0V28*Aca&J' );
define( 'LOGGED_IN_SALT',   '?AH!?yS,L5W!pB%7<[, 9seV[2?jwi_FGfcIWAD(>xfc(]h+){beVIm}jaKVU(xA' );
define( 'NONCE_SALT',       '^>pa6ruc^x8J0G&-@:7,7AQ,HM_Dok}C4Ujz02XV+m>%~{0CixSA10QDVDd+Bx#L' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */
define('DISABLE_WP_CRON', true);


/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
