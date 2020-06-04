<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp-table-pay' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'rootuser' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '})klpjWT v[v(n)N{qcBj}Sup4H(>:DPfS)TBxmYM~9qA]qz~Q&n9DRIo$5]@T)Y' );
define( 'SECURE_AUTH_KEY',  'G^=^{4bqb!)vqsMz^Om[VxfM4pN[r#B$V~u,H%o_P[:ka!BO-_7.[+.mcKq_p7$;' );
define( 'LOGGED_IN_KEY',    'E6{&uj(C[$-y;q7fbc3W&QCwZepCNO,xr2zPX6#>@+m8t_T$8(+CyvQPqD`OfK0c' );
define( 'NONCE_KEY',        'JsO8APM1Wf46H.o:k(--nn%87@8yT|y,i>y64:&2l },gG,_>^vHKg5u{ khlWkZ' );
define( 'AUTH_SALT',        '?CfA$+ ~@Awfu#TuulSE:CI&#n3sO%Bl~f,AxK8|1=([8]omm~!a87KCc%%{cafa' );
define( 'SECURE_AUTH_SALT', 'Se*9<2_:b}pm!G0v?;#:Ixe>Di]DMs8TAyJ@zY2lDK>bYu0~K|}T)Ii_~y`t#Hb#' );
define( 'LOGGED_IN_SALT',   'mJ7#5S7+I,P5PS(p)QE(nW-Jc!T,XGs0wgEr28(V5C^Z]U[Su6qR8V<5G3 Q%M&E' );
define( 'NONCE_SALT',       'n*}E2*>e8j_]9nPZhbff-:?)6xiiY#pa&HAIo4u93J)F*||+]q>{l<;>?,-XA?)i' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

define('FS_METHOD','direct');