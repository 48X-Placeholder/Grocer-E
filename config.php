<?php

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for Core Site */
if (!defined('DB_NAME')) {
 define('DB_NAME', 'grocery_db');
}

/** The name of the database for Core Site Accounts */
if (!defined('DB_NAME_ACCOUNTS')) {
 define('DB_NAME_ACCOUNTS', 'user_db');
}

/** Database username */
if (!defined('DB_USER')) {
 define('DB_USER', 'root');
}

/** Database password */
if (!defined('DB_PASSWORD')) {
 define('DB_PASSWORD', 'root');
}

/** Database hostname */
if (!defined('DB_HOST')) {
 define('DB_HOST', 'localhost');
}

/** Database charset to use in creating database tables. */
if (!defined('DB_CHARSET')) {
 define('DB_CHARSET', 'utf8');
}

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

/** Generate a random salt value **/
if (!defined('AUTH_SALT')) {
 define('AUTH_SALT', 'fc$iT_g~[incwYQLe9nsJ+o 5YpJsX9f@PNwZiJfWeEGH+%2 #ai#U|Y%w-fVA(<');
}

/** Site URL (Used for URLS) */
if (!defined('SITE_URL')) {
 define('SITE_URL', (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/');
}

?>
