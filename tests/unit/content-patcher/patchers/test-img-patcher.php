<?php
/**
 * Test class for the NewspackContentConverter\ContentPatcher\Patchers\ImgPatcher.
 *
 * @package Newspack
 */

use NewspackContentConverter\ContentPatcher\Patchers\ImgPatcher;

/**
 * Class TestImgPatcher
 */
class TestImgPatcher extends WP_UnitTestCase {

	/**
	 * Override setUp.
	 */
	public function setUp() {
		$this->fixtures_dir = dirname( __FILE__ ) . '/../../../fixtures/unit/content-patcher/patchers/';

		require_once $this->fixtures_dir . 'class-dataproviderimgpatcher.php';

		$this->img_patcher   = new ImgPatcher();
		$this->data_provider = new DataProviderImgPatcher();
	}

	/**
	 * The patcher shoud patch the lost image attribute.
	 */
	public function test_should_patch_height_attribute() {
		$html                   = '<img class="size-full wp-image-42124" src="https://mysite.com/CollegeGraphic.jpg" alt="bla-bla" width="1176" height="1600">';
		$blocks_before_patching = '<img src="https://mysite.com/CollegeGraphic.jpg" alt="bla-bla" class="wp-image-42124"/>';
		$expected               = '<img src="https://mysite.com/CollegeGraphic.jpg" alt="bla-bla" class="wp-image-42124" height="1600" />';

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should patch multiple images.
	 */
	public function test_should_patch_multiple_image_elements() {
		$html                   = $this->data_provider::get_multiple_imgs_html();
		$blocks_before_patching = $this->data_provider::get_multiple_imgs_blocks_before_patching();
		$expected               = $this->data_provider::get_multiple_imgs_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * This is a case where not all images have the height attribute. Patcher should not patch those.
	 */
	public function test_should_skip_patching_images_which_dont_have_the_height_attribute() {
		$html                   = $this->data_provider::get_some_skipped_imgs_html();
		$blocks_before_patching = $this->data_provider::get_some_skipped_imgs_blocks_before_patching();
		$expected               = $this->data_provider::get_some_skipped_imgs_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * This test will become important when Gutenberg fixes conversion of this particular attribute, so we need to make sure
	 * we won't patch it twice.
	 */
	public function test_should_skip_patching_images_that_already_have_the_attribute() {
		$html                   = $this->data_provider::get_some_imgs_ok_html();
		$blocks_before_patching = $this->data_provider::get_some_imgs_ok_blocks_before_patching();
		$expected               = $this->data_provider::get_some_imgs_ok_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Runs a comprehensive example and checks for validity.
	 */
	public function test_should_correctly_patch_a_comprehensive_conversion_example() {
		$html                   = $this->data_provider::get_comprehensive_html();
		$blocks_before_patching = $this->data_provider::get_comprehensive_blocks_before_patching();
		$expected               = $this->data_provider::get_comprehensive_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}
}
