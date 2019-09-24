<?php
/**
 * Handles loading of JS and CSS.
 *
 * @since 1.0.0
 * @package TenUp\AutoTweet
 */

namespace TenUp\AutoTweet\Admin\Assets;

use function TenUp\AutoTweet\Utils\get_autotweet_meta;
use function TenUp\AutoTweet\Utils\opted_into_autotweet;
use const TenUp\AutoTweet\Core\Post_Meta\ENABLE_AUTOTWEET_KEY;
use const TenUp\AutoTweet\Core\Post_Meta\TWEET_BODY_KEY;
use function TenUp\AutoTweet\REST\post_autotweet_meta_rest_route;

/**
 * The handle used in registering plugin assets.
 */
const SCRIPT_HANDLE = 'autotweet';

/**
 * Adds WP hook callbacks.
 *
 * @since 1.0.0
 */
function add_hook_callbacks() {
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\maybe_enqueue_classic_editor_assets' );
	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_editor_assets' );
}

/**
 * Enqueues assets for supported post type editors where the block editor is not active.
 *
 * @since 1.0.0
 * @param string $hook The current admin page.
 */
function maybe_enqueue_classic_editor_assets( $hook ) {
	if ( ! in_array( $hook, [ 'post-new.php', 'post.php' ], true ) ) {
		return;
	}

	if ( ! opted_into_autotweet( get_the_ID() ) ) {
		return;
	}

	$current_screen = get_current_screen();
	if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
		return;
	}

	$api_fetch_handle = 'wp-api-fetch';
	if ( ! wp_script_is( $api_fetch_handle, 'registered' ) ) {
		wp_register_script(
			$api_fetch_handle,
			trailingslashit( TUAT_URL ) . 'dist/api-fetch.js',
			[],
			'3.4.0',
			true
		);

		wp_add_inline_script(
			$api_fetch_handle,
			sprintf(
				'wp.apiFetch.use( wp.apiFetch.createNonceMiddleware( "%s" ) );',
				( wp_installing() && ! is_multisite() ) ? '' : wp_create_nonce( 'wp_rest' )
			),
			'after'
		);

		wp_add_inline_script(
			$api_fetch_handle,
			sprintf(
				'wp.apiFetch.use( wp.apiFetch.createRootURLMiddleware( "%s" ) );',
				esc_url_raw( get_rest_url() )
			),
			'after'
		);
	}

	$handle = 'admin_autotweet';
	wp_enqueue_script(
		$handle,
		trailingslashit( TUAT_URL ) . 'assets/js/admin-auto_tweet.js',
		[ 'jquery', 'wp-api-fetch' ],
		TUAT_VERSION,
		true
	);

	wp_enqueue_style(
		$handle,
		trailingslashit( TUAT_URL ) . 'assets/css/admin-auto_tweet.css',
		[],
		TUAT_VERSION
	);

	localize_data( $handle );
}

/**
 * Enqueues block editor assets.
 *
 * @since 1.0.0
 */
function enqueue_editor_assets() {
	if ( ! opted_into_autotweet( get_the_ID() ) ) {
		return;
	}

	wp_enqueue_script(
		SCRIPT_HANDLE,
		trailingslashit( TUAT_URL ) . 'dist/autotweet.js',
		[ 'wp-plugins', 'wp-edit-post' ],
		TUAT_VERSION,
		true
	);

	localize_data();
}

/**
 * Passes data to Javascript.
 *
 * @since 1.0.0
 * @param string $handle Handle of the JS script intended to consume the data.
 */
function localize_data( $handle = SCRIPT_HANDLE ) {
	$post_id = intval( get_the_ID() );

	if ( empty( $post_id ) ) {
		$post_id = intval(
			filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT )  // Filter removes all characters except digits.
		);
	}

	$localization = [
		'enabled'            => get_autotweet_meta( $post_id, ENABLE_AUTOTWEET_KEY ),
		'enableAutotweetKey' => ENABLE_AUTOTWEET_KEY,
		'errorText'          => __( 'Error', 'autotweet' ),
		'nonce'              => wp_create_nonce( 'wp_rest' ),
		'restUrl'            => rest_url( post_autotweet_meta_rest_route( $post_id ) ),
		'tweetBodyKey'       => TWEET_BODY_KEY,
		'unknownErrorText'   => __( 'An unknown error occurred', 'autotweet' ),
	];

	wp_localize_script( $handle, 'adminAutotweet', $localization );
}
