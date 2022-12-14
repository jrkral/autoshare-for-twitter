<?php
/**
 * Tests functionality implemented by the Publish_Tweet class.
 *
 * @since 0.1.0
 * @package TenUp\AutoshareForTwitter
 */

namespace TenUp\AutoshareForTwitter\Tests;

use TenUp\AutoshareForTwitter\Core\Publish_Tweet\Publish_Tweet;
use WP_UnitTestCase;

/**
 * Tests for the Publish_Tweet class.
 */
class TestPublish_Tweet extends WP_UnitTestCase {
	/**
	 * Setup method.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->publish_tweet = new Publish_Tweet();
	}

	/**
	 * Tear down method.
	 */
	public function tearDown(): void {
		parent::tearDown();
		$this->remove_added_uploads();
	}

	/**
	 * Tests the get_upload_data_media_id method.
	 *
	 * @return void
	 */
	public function test_get_upload_data_media_id() {
		$post       = $this->factory->post->create_and_get();
		$attachment = $this->factory->attachment->create_upload_object( DIR_TESTDATA . '/images/33772.jpg', $post->ID );
		set_post_thumbnail( $post, $attachment );

		add_filter( 'autoshare_for_twitter_attached_image', '__return_false' );
		$this->assertNull( $this->publish_tweet->get_upload_data_media_id( $post ) );
		remove_filter( 'autoshare_for_twitter_attached_image', '__return_false' );

		$filter_media_upload_id = function() {
			return 999;
		};

		add_filter( 'autoshare_for_twitter_pre_media_upload', $filter_media_upload_id );
		$this->assertEquals( 999, $this->publish_tweet->get_upload_data_media_id( $post ) );
		remove_filter( 'autoshare_for_twitter_pre_media_upload', $filter_media_upload_id );
	}

	/**
	 * Tests the get_largest_acceptable_image method.
	 */
	public function test_get_largest_acceptable_imagel() {
		$attachment             = $this->factory->attachment->create_upload_object( DIR_TESTDATA . '/images/33772.jpg' ); // ~172KB image.
		$set_150kb_max_filesize = function() {
			return 150000;
		};
		add_filter( 'autoshare_for_twitter_max_image_size', $set_150kb_max_filesize );
		$file = $this->publish_tweet->get_largest_acceptable_image(
			get_attached_file( $attachment ),
			wp_get_attachment_metadata( $attachment )['sizes']
		);

		// phpcs:disable WordPress.DateTime.RestrictedFunctions.date_date
		$this->assertEquals( sprintf( '/tmp/wordpress/wp-content/uploads/%s/%s/33772-1536x864.jpg', date( 'Y' ), date( 'm' ) ), $file );
		$attachment = $this->factory->attachment->create_upload_object( DIR_TESTDATA . '/images/2004-07-22-DSC_0008.jpg' ); // ~109kb image.
		$file       = $this->publish_tweet->get_largest_acceptable_image(
			get_attached_file( $attachment ),
			wp_get_attachment_metadata( $attachment )['sizes']
		);
		$this->assertEquals( sprintf( '/tmp/wordpress/wp-content/uploads/%s/%s/2004-07-22-DSC_0008.jpg', date( 'Y' ), date( 'm' ) ), $file );
		remove_filter( 'autoshare_for_twitter_max_image_size', $set_150kb_max_filesize );
		// phpcs:enable WordPress.DateTime.RestrictedFunctions.date_date
		$set_1kb_max_filesize = function() {
			return 1000;
		};
		add_filter( 'autoshare_for_twitter_max_image_size', $set_1kb_max_filesize );
		$file = $this->publish_tweet->get_largest_acceptable_image(
			get_attached_file( $attachment ),
			wp_get_attachment_metadata( $attachment )['sizes']
		);
		$this->assertNull( $file );

		remove_filter( 'autoshare_for_twitter_max_image_size', $set_1kb_max_filesize );
	}
}
