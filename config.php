<?php

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for Core Site */
define("DB_NAME", "grocery_db");

/** The name of the database for Core Site Accounts */
define("DB_NAME_ACCOUNTS", "user_db");

/** Database username */
define("DB_USER", "root");

/** Database password */
define("DB_PASSWORD", "root");

/** Database hostname */
define("DB_HOST", "localhost");

/** Database charset to use in creating database tables. */
define("DB_CHARSET", "utf8");

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
define("AUTH_SALT", 'fc$iT_g~[incwYQLe9nsJ+o 5YpJsX9f@PNwZiJfWeEGH+%2 #ai#U|Y%w-fVA(<');

/* */
// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
 die("Connection failed: " . $conn->connect_error);
}
?>
