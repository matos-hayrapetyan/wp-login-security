<?php
/**
 * Plugin Name: WP Login Security

 * Plugin URI:  https://github.com/matos-hayrapetyan/wp-login-security
 * Description: Lorem Ipsum
 * Version:     1.0.0
 * Author:      Matevos Hayrapetyan
 * Author URI:  https://github.com/matos-hayrapetyan
 * Text Domain: wp-login-test
 */

// Useful global constants.
define( 'WP_LOGIN_TEST_VERSION', '1.0.0' );
define( 'WP_LOGIN_TEST_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_LOGIN_TEST_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_LOGIN_TEST_COM', WP_LOGIN_TEST_PATH . 'com/' );
define( 'WP_LOGIN_TEST_FILE', __FILE__ );
define( 'WP_LOGIN_TEST_BASE', plugin_basename( __FILE__ ) );

// Require Composer autoloader if it exists.
if ( file_exists( WP_LOGIN_TEST_PATH . '/vendor/autoload.php' ) ) {
	require_once WP_LOGIN_TEST_PATH . 'vendor/autoload.php';
}

$wplogintest = \WPLoginSecurity\WPLoginSecurity::get_instance();
$wplogintest->init();