<?php
/**
 * Data Provider for tests.
 *
 * @package Newspack
 */

/**
 * Class DataProviderImgPatcher
 *
 * Data Provider for tests concerning unit/content-patcher/patchers/test-img-patcher.php
 */
class DataProviderImgPatcher {

	/**
	 * HTML source for a comprehensive example, containing multiple elements.
	 *
	 * @return string HTML
	 */
	public static function get_comprehensive_html() {
		return <<<CONTENT
<p>Some content before</p>
<figure id="1234" class="some-nested-content">The following image should be patched since it has the height attribute</p>
<p><img class="size-full wp-image-42124" src="https://mysite.com/CollegeGraphic.jpg" alt="bla-bla" width="1176" height="1600"></figure>
<p>Some content in between</p>
<p>The second image doesn&#8217;t have any attributes, so it should be skipped by the patcher</p>
<p><img src="https://mysite.com/CollegeGraphic.jpg">Some content in between</p>
<p>The third image image has a different height attribute, so it should also be patched</p>
<p><img height="1234" src="https://mysite.com/CollegeGraphic.jpg">Some content in the end</p>
CONTENT;
	}

	/**
	 * Blocks contents before patching, for a comprehensive example, containing multiple elements.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_comprehensive_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p>Some content before</p>
<!-- /wp:paragraph -->

<!-- wp:image {"id":42124,"className":"some-nested-content"} -->
<figure class="wp-block-image some-nested-content"><img src="https://mysite.com/CollegeGraphic.jpg" alt="bla-bla" class="wp-image-42124"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>Some content in between</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>The second image doesn’t have any attributes, so it should be skipped by the patcher</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<figure class="wp-block-image"><img src="https://mysite.com/CollegeGraphic.jpg" alt=""/></figure>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>Some content in between</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>The third image image has a different height attribute, so it should also be patched</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<figure class="wp-block-image"><img src="https://mysite.com/CollegeGraphic.jpg" alt=""/></figure>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>Some content in the end</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for a comprehensive example, containing multiple elements.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_comprehensive_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p>Some content before</p>
<!-- /wp:paragraph -->

<!-- wp:image {"id":42124,"className":"some-nested-content"} -->
<figure class="wp-block-image some-nested-content"><img src="https://mysite.com/CollegeGraphic.jpg" alt="bla-bla" class="wp-image-42124" height="1600" /></figure>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>Some content in between</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>The second image doesn’t have any attributes, so it should be skipped by the patcher</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<figure class="wp-block-image"><img src="https://mysite.com/CollegeGraphic.jpg" alt=""/></figure>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>Some content in between</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>The third image image has a different height attribute, so it should also be patched</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<figure class="wp-block-image"><img src="https://mysite.com/CollegeGraphic.jpg" alt="" height="1234" /></figure>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>Some content in the end</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * HTML source with multiple images.
	 *
	 * @return string HTML
	 */
	public static function get_multiple_imgs_html() {
		return <<<CONTENT
			<img class="size-full wp-image-1" src="https://mysite.com/2.jpg" alt="bla-bla1" height="123">
			<img class="size-full wp-image-2" src="https://mysite.com/1.jpg" alt="bla-bla2" height="456">
CONTENT;
	}

	/**
	 * Blocks contents before patching, for an example containing multiple images.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_multiple_imgs_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":1} -->
<figure class="wp-block-image"><img src="https://mysite.com/2.jpg" alt="bla-bla1" class="wp-image-1"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2} -->
<figure class="wp-block-image"><img src="https://mysite.com/1.jpg" alt="bla-bla2" class="wp-image-2"/></figure>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for an example containing multiple images.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_multiple_imgs_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":1} -->
<figure class="wp-block-image"><img src="https://mysite.com/2.jpg" alt="bla-bla1" class="wp-image-1" height="123" /></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2} -->
<figure class="wp-block-image"><img src="https://mysite.com/1.jpg" alt="bla-bla2" class="wp-image-2" height="456" /></figure>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source with multiple images out of which some don't have the height attribute.
	 *
	 * @return string HTML
	 */
	public static function get_some_skipped_imgs_html() {
		return <<<CONTENT
			<img class="size-full wp-image-1" src="https://mysite.com/2.jpg" alt="bla-bla1" height="123">
			<img class="size-full wp-image-1" src="https://mysite.com/2.jpg" alt="bla-bla0">
			<img class="size-full wp-image-2" src="https://mysite.com/1.jpg" alt="bla-bla2" height="789">
CONTENT;
	}

	/**
	 * Blocks contents before patching, for an example where some images don't have the height attribute.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_some_skipped_imgs_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":1} -->
<figure class="wp-block-image"><img src="https://mysite.com/2.jpg" alt="bla-bla1" class="wp-image-1"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":1} -->
<figure class="wp-block-image"><img src="https://mysite.com/2.jpg" alt="bla-bla0" class="wp-image-1"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2} -->
<figure class="wp-block-image"><img src="https://mysite.com/1.jpg" alt="bla-bla2" class="wp-image-2"/></figure>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for an example where some images don't have the height attribute.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_some_skipped_imgs_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":1} -->
<figure class="wp-block-image"><img src="https://mysite.com/2.jpg" alt="bla-bla1" class="wp-image-1" height="123" /></figure>
<!-- /wp:image -->

<!-- wp:image {"id":1} -->
<figure class="wp-block-image"><img src="https://mysite.com/2.jpg" alt="bla-bla0" class="wp-image-1"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2} -->
<figure class="wp-block-image"><img src="https://mysite.com/1.jpg" alt="bla-bla2" class="wp-image-2" height="789" /></figure>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source with multiple images where some will not have their content lost.
	 *
	 * @return string HTML
	 */
	public static function get_some_imgs_ok_html() {
		return <<<CONTENT
			<img class="size-full wp-image-1" src="https://mysite.com/2.jpg" alt="bla-bla1" height="123">
			<img class="size-full wp-image-1" src="https://mysite.com/2.jpg" alt="bla-bla0" height="456">
			<img class="size-full wp-image-2" src="https://mysite.com/1.jpg" alt="bla-bla2" height="789">
CONTENT;
	}

	/**
	 * Blocks contents before patching, multiple images where some will not have their content lost.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_some_imgs_ok_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":1} -->
<figure class="wp-block-image"><img src="https://mysite.com/2.jpg" alt="bla-bla1" class="wp-image-1"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":1} -->
<figure class="wp-block-image"><img src="https://mysite.com/2.jpg" alt="bla-bla0" class="wp-image-1" height="456" /></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2} -->
<figure class="wp-block-image"><img src="https://mysite.com/1.jpg" alt="bla-bla2" class="wp-image-2"/></figure>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, multiple images where some will not have their content lost.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_some_imgs_ok_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":1} -->
<figure class="wp-block-image"><img src="https://mysite.com/2.jpg" alt="bla-bla1" class="wp-image-1" height="123" /></figure>
<!-- /wp:image -->

<!-- wp:image {"id":1} -->
<figure class="wp-block-image"><img src="https://mysite.com/2.jpg" alt="bla-bla0" class="wp-image-1" height="456" /></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2} -->
<figure class="wp-block-image"><img src="https://mysite.com/1.jpg" alt="bla-bla2" class="wp-image-2" height="789" /></figure>
<!-- /wp:image -->
CONTENT;
	}

}
