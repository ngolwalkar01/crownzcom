<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'proztptt_crownz' );

/** Database username */
define( 'DB_USER', 'proztptt_crownz' );

/** Database password */
define( 'DB_PASSWORD', 'QzC{x[QBQp-h' );

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
define( 'AUTH_KEY',         '__bbKS@n,mKs,P_[`(l3Xt{I4I[)%k7nS.?arW5YrB$=jRpH$#,B~G=HP;(H{RBA' );
define( 'SECURE_AUTH_KEY',  ' AQ{%=u<R(s}OrNP+QwZ4?}6DrlPv??QP}&;F0[}*]H.N1phn,9!|@n&5utOgwI<' );
define( 'LOGGED_IN_KEY',    'imevK%D1BYWE#Xc.40nhs]{+|5cwEJRCbQhxd:d3`OM8%s|Dl.KR>WHuH9:Df+Bg' );
define( 'NONCE_KEY',        'NTL_zF-w]GA:4LcPCR2lFSjQ:=`|%f~N2enKigv)3QseyFQIY#IxdN*1}d<R<BF=' );
define( 'AUTH_SALT',        '_*/Qv{h2NYq&V*#8 S{`r;{;zrwNYg2/{J`=IYt-@/8^9ZU#Nqy ]JXZ-N@turY_' );
define( 'SECURE_AUTH_SALT', 'E]rhXrF*?Q#l*j7*B3,jS!X)=qa-/s9gm48Zov8t gWqobqu1,XFB>^-/_h52h7X' );
define( 'LOGGED_IN_SALT',   'Go92SG2Bsk,Ex7^*-mD9Kb8G8y;p[VP4OOnq5&:#6HvT,OrtSuxz|X$hR:tkJel=' );
define( 'NONCE_SALT',       'IdSANfG76JL#D@Axmk,U#6 >cpT6vBL~JjL`<[k(IIA4]2eUvPQ4^zw$js86Yt+l' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
