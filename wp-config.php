<?php
define( 'WP_CACHE', true );

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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u987541567_VY3KB' );

/** Database username */
define( 'DB_USER', 'u987541567_7j4aR' );

/** Database password */
define( 'DB_PASSWORD', '4FaczF8Zmn' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          '~F>W.{L#vO(&EmSC[OBXQ4)*R1X~3<<*)zZ,NU4;<jb54+jvl(1LNh{X5@WYcLMH' );
define( 'SECURE_AUTH_KEY',   'sj_ku^HzcApJ_Jmy9PG2p%V6ZJ_.<Tgf|UUcBuiZS_#(QeD$uNK_X+8i8(^g+ikF' );
define( 'LOGGED_IN_KEY',     ',C{vOhNG7JMU<$KAV;:rG&>BqRfQ@|VFQ$>bd=hvpC$2U}z&}$Q)>$J;D_Lgy/J8' );
define( 'NONCE_KEY',         ',68V@+/N]LTHIau=2|`5B &kc&BY0ynP`WhA>]8<)6Nih4mdLP2HD7zjVFb=$34t' );
define( 'AUTH_SALT',         'YwHwQHbFo6jAtUp@kIf{srasjC:TBq,SDLX^om1NqK2O<mwnr`ARcVVCClwxZ``r' );
define( 'SECURE_AUTH_SALT',  '?Wbnhho&VmC~WI(FqDGcB:f;BGb=0=m},G@y_V,Z4gTO94o0f]Xyr6;|NM$_lI_S' );
define( 'LOGGED_IN_SALT',    'aoBMFXBL6,%THq.V-|jDYDE%y;xAp/FfTfqw*]V:eJ]iT$T}UO-bd<{>).M3cOF^' );
define( 'NONCE_SALT',        '7y+-lUZim<<B8E#2RHse+QnxM^j)!on~XS)O<4A10~,E]B3]VBq}wv{y*8QZGqPO' );
define( 'WP_CACHE_KEY_SALT', 'Z5xM@=DMGV]/LK@gbGr,Kf8/N-(Q9HcT}tZUq0gRL!wF9R/+O9&|4N>-2Lh0_>Wj' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', '5b3eea89728428a1f3bd0e7f71022c8c' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
