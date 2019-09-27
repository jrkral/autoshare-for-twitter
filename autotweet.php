<?php
/**
 * Plugin Name: Autotweet
 * Description: Adds the ability to automatically publish a status update to Twitter.
 * Version:     0.1.0
 * Author:      10up
 * Author URI:  https://10up.com
 * License:     GPLv2 or later
 *
 * @package TenUp\AutoTweet
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'AUTOTWEET', __FILE__ );
define( 'TUAT_VERSION', '0.1.0' );
define( 'TUAT_URL', plugin_dir_url( __FILE__ ) );
define( 'TUAT_PATH', plugin_dir_path( __FILE__ ) );
define( 'TUAT_INC', TUAT_PATH . 'includes/' );

/**
 * Composer check.
 */
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}


// Include the main functionality
require_once plugin_dir_path( __FILE__ ) . 'includes/core.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/utils.php';

/**
 * Play nice with others.
 */
do_action( 'tenup_autotweet_loaded' );