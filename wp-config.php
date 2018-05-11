<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'evodb_ver1');

/** MySQL database username */
define('DB_USER', 'csmith');

/** MySQL database password */
define('DB_PASSWORD', 'Chip-i-ty123');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'mJ5DwcmnboRKpXeUvtEMPg4GIRTyP4aMUVkwzmt2hEEGxcKuqDCZpI4h8tzm204U');
define('SECURE_AUTH_KEY',  'XOUFdwra0CAxwLIOMsw0Mdp9TruhZthW4BBamg19iZNnmhy5QNOaMts0yuEq4HBi');
define('LOGGED_IN_KEY',    '4k6YuCtS2VP1i7RRxCTg2NZXQ0oKvUkobNIaRTaqS9NH260DPPMHbDT4jSNn7v17');
define('NONCE_KEY',        'r89SH6qCF4FzBUC0LU79i8sy31Y2MzTwcj8OaPokPJXtxngSSnuIUIwJKXy29ZlE');
define('AUTH_SALT',        'TYkYhsFDnimCHxFQ4U7Lm16SWn3mznUyNTSYHH26kBfXUNLuU7lpmcJ0Cw0cLTck');
define('SECURE_AUTH_SALT', 'eCrzJhOMBSel29C9b4IzWf75aWNNLAAQ1S3Lf164Vhuw386F9Skc552ouHScJSjx');
define('LOGGED_IN_SALT',   'AmpIUcLhVPCjqlNIwFGQTXJ9oB3unrbuwmT9LopXvUM4ME8jUEOCMFQ5lR2FUcjk');
define('NONCE_SALT',       'KzSwttPvh0xOuh6bInMl50hvfJSMPtAUe7iNRtyPIUzA2DRDTJ0OX3FsyeW6W0oF');

/**
 * Other customizations.
 */
define('FS_METHOD','direct');define('FS_CHMOD_DIR',0755);define('FS_CHMOD_FILE',0644);
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');

/**
 * Turn off automatic updates since these are managed upstream.
 */
define('AUTOMATIC_UPDATER_DISABLED', true);


/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
