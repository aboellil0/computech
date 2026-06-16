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
define( 'DB_NAME', 'computech' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

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
define( 'AUTH_KEY',         'z.8/-Fu0~tU)OQmdSJUgjYx/$AT3DpYs4ZA$/D.6+,wD {h$7ME+[Td,?9Sraq^7' );
define( 'SECURE_AUTH_KEY',  'HPy@/!*s[|w6ZZpO5>m]IvDdzbpNrhE0a8W9a:QB Kc_~quwqQo:g/Uey$$t%]NA' );
define( 'LOGGED_IN_KEY',    'G^.>*K8Id|grO8bYE1;4X9rJ$y>Cwy_7.?_s~zLPLR?pBr!0jew0Gqc)+PI$|@ !' );
define( 'NONCE_KEY',        '!%29<8KtwVJm$`5A9hYdd&$[RLQz*uOcExiR9ir`Gprfm,9!aGce0G*Su{IX!,=1' );
define( 'AUTH_SALT',        'pH#6fQ]9vl8+0! /YdDzU#eSRmE-]/V%O*>*Ko(f}(dVLGl0 t$]/ZBx}MF?? f`' );
define( 'SECURE_AUTH_SALT', 'c*udTnJ+y%{T,}e@Rs?c(V&fcz[Nzj<`FhcP?i%;FPlosa:A5HXoo[4`Xfkxw.ML' );
define( 'LOGGED_IN_SALT',   'dow/yyR4d%vxd9OPmp5/[{_tmt%Gh-&St*,g!8FlS}{MRP}ptn8^F/niCMi@Y)ok' );
define( 'NONCE_SALT',       'qzp`!.c_d(z=h!{C&IzhneF.AqGv^;%KE]`?L7.Caq,e%~+6:@QyQXRZiENJzg?2' );

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
