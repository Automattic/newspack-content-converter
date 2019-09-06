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
	 * @return string HTML.
	 */
	public static function get_comprehensive_html() {
		return <<<CONTENT
<p>Some content before</p>
<figure id="1234" class="some-nested-content">The following image should be patched since it has the height attribute</p>
<p><img class="size-full wp-image-42124" src="https://mysite.com/CollegeGraphic.jpg" alt="bla-bla" height="1600"></figure>
<p>The second image doesn&#8217;t have any attributes, so it should be skipped by the patcher</p>
<p><img src="https://mysite.com/CollegeGraphic.jpg"></p>
<p>The third image image has a different height attribute, so it should also be patched</p>
<p><img height="1234" src="https://mysite.com/CollegeGraphic.jpg"></p>
<p>The fourth image has both height and width attributes, so both should get patched</p>
<p><img height="1234" width="5678" src="https://mysite.com/CollegeGraphic.jpg"></p>
<p>The fifth image has both height and width attributes, height will be converted properly, so only width should get patched</p>
<p><img height="1234" width="5678" src="https://mysite.com/CollegeGraphic.jpg"></p>
<p>The sixth image has both height and width attributes, width will be converted properly, so only height should get patched</p>
<p><img height="1234" width="5678" src="https://mysite.com/CollegeGraphic.jpg"></p>
<p>Some content in the end</p>
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
<p>The fourth image has both height and width attributes, so both should get patched</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<figure class="wp-block-image"><img src="https://mysite.com/CollegeGraphic.jpg" alt=""/></figure>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>The fifth image has both height and width attributes, height will be converted properly, so only width should get patched</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<figure class="wp-block-image"><img src="https://mysite.com/CollegeGraphic.jpg" alt="" height="1234"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>The sixth image has both height and width attributes, width will be converted properly, so only height should get patched</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<figure class="wp-block-image"><img src="https://mysite.com/CollegeGraphic.jpg" alt="" width="5678"/></figure>
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

<!-- wp:image {"id":42124,"className":"some-nested-content","height":1600} -->
<figure class="wp-block-image some-nested-content is-resized"><img src="https://mysite.com/CollegeGraphic.jpg" alt="bla-bla" class="wp-image-42124" height="1600"/></figure>
<!-- /wp:image -->

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

<!-- wp:image {"height":1234} -->
<figure class="wp-block-image is-resized"><img src="https://mysite.com/CollegeGraphic.jpg" alt="" height="1234"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>The fourth image has both height and width attributes, so both should get patched</p>
<!-- /wp:paragraph -->

<!-- wp:image {"height":1234,"width":5678} -->
<figure class="wp-block-image is-resized"><img src="https://mysite.com/CollegeGraphic.jpg" alt="" height="1234" width="5678"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>The fifth image has both height and width attributes, height will be converted properly, so only width should get patched</p>
<!-- /wp:paragraph -->

<!-- wp:image {"height":1234,"width":5678} -->
<figure class="wp-block-image is-resized"><img src="https://mysite.com/CollegeGraphic.jpg" alt="" height="1234" width="5678"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>The sixth image has both height and width attributes, width will be converted properly, so only height should get patched</p>
<!-- /wp:paragraph -->

<!-- wp:image {"height":1234,"width":5678} -->
<figure class="wp-block-image is-resized"><img src="https://mysite.com/CollegeGraphic.jpg" alt="" width="5678" height="1234"/></figure>
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
<!-- wp:image {"id":1,"height":123} -->
<figure class="wp-block-image is-resized"><img src="https://mysite.com/2.jpg" alt="bla-bla1" class="wp-image-1" height="123"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2,"height":456} -->
<figure class="wp-block-image is-resized"><img src="https://mysite.com/1.jpg" alt="bla-bla2" class="wp-image-2" height="456"/></figure>
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
<!-- wp:image {"id":1,"height":123} -->
<figure class="wp-block-image is-resized"><img src="https://mysite.com/2.jpg" alt="bla-bla1" class="wp-image-1" height="123"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":1} -->
<figure class="wp-block-image"><img src="https://mysite.com/2.jpg" alt="bla-bla0" class="wp-image-1"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2,"height":789} -->
<figure class="wp-block-image is-resized"><img src="https://mysite.com/1.jpg" alt="bla-bla2" class="wp-image-2" height="789"/></figure>
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

<!-- wp:image {"id":1,"height":456} -->
<figure class="wp-block-image is-resized"><img src="https://mysite.com/2.jpg" alt="bla-bla0" class="wp-image-1" height="456"/></figure>
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
<!-- wp:image {"id":1,"height":123} -->
<figure class="wp-block-image is-resized"><img src="https://mysite.com/2.jpg" alt="bla-bla1" class="wp-image-1" height="123"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":1,"height":456} -->
<figure class="wp-block-image is-resized"><img src="https://mysite.com/2.jpg" alt="bla-bla0" class="wp-image-1" height="456"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2,"height":789} -->
<figure class="wp-block-image is-resized"><img src="https://mysite.com/1.jpg" alt="bla-bla2" class="wp-image-2" height="789"/></figure>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for inconsistent sources.
	 *
	 * @return string HTML
	 */
	public static function get_inconsistent_sources_html() {
		return <<<CONTENT
<img class="size-full wp-image-1" src="https://mysite.com/2.jpg" alt="bla-bla1" height="123">
CONTENT;
	}

	/**
	 * Blocks contents before patching, for inconsistent sources.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_inconsistent_sources_before_patching() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p>Some inconsistent blocks content, not supposed to get modified</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for inconsistent sources.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_inconsistent_sources_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p>Some inconsistent blocks content, not supposed to get modified</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * HTML source for non pertinent HTML.
	 *
	 * @return string HTML
	 */
	public static function get_html_is_non_pertinent_html() {
		return <<<CONTENT
<p>This has nothing to do with the patcher</p>
CONTENT;
	}

	/**
	 * Blocks contents before patching, for non pertinent HTML.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_html_is_non_pertinent_before_patching() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p>This has nothing to do with the patcher</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for non pertinent HTML.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_html_is_non_pertinent_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p>This has nothing to do with the patcher</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * HTML source for align left.
	 *
	 * @return string HTML
	 */
	public function get_align_left_html() {
		return <<<CONTENT
<img align="left" src="/img.jpg">
CONTENT;
	}

	/**
	 * Blocks contents before patching, for  align left.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_align_left_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image -->
<figure class="wp-block-image"><img src="/img.jpg"/></figure>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for  align left.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_align_left_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"align":"left"} -->
<figure class="wp-block-image alignleft"><img src="/img.jpg"/></figure>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for align right.
	 *
	 * @return string HTML
	 */
	public function get_align_right_html() {
		return <<<CONTENT
<img align="right" src="/img.jpg">
CONTENT;
	}

	/**
	 * Blocks contents before patching, for align right.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_align_right_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image -->
<figure class="wp-block-image"><img src="/img.jpg"/></figure>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for align right.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_align_right_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"align":"right"} -->
<figure class="wp-block-image alignright"><img src="/img.jpg"/></figure>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for skip patching if unknown align value.
	 *
	 * @return string HTML
	 */
	public function get_skip_patching_unknown_align_value_html() {
		return <<<CONTENT
<img align="insideout" src="/img.jpg">
CONTENT;
	}

	/**
	 * Blocks contents before patching, for skip patching if unknown align value.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_skip_patching_unknown_align_value_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image -->
<figure class="wp-block-image"><img src="/img.jpg"/></figure>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for skip patching if unknown align value.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_skip_patching_unknown_align_value_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image -->
<figure class="wp-block-image"><img src="/img.jpg"/></figure>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for skip patching if align is already in header and figure class.
	 *
	 * @return string HTML
	 */
	public function get_skip_patching_align_already_in_header_and_figure_class_html() {
		return <<<CONTENT
<img align="left" src="/img.jpg">
CONTENT;
	}

	/**
	 * Blocks contents before patching, for skip patching if align is already in header and figure class.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_skip_patching_align_already_in_header_and_figure_class_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image {"align":"right"} -->
<figure class="wp-block-image alignright"><img src="/img.jpg" alt=""/></figure>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for skip patching if align is already in header and figure class.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_skip_patching_align_already_in_header_and_figure_class_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"align":"right"} -->
<figure class="wp-block-image alignright"><img src="/img.jpg" alt=""/></figure>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for skip patching if align is already in figure class.
	 *
	 * @return string HTML
	 */
	public function get_skip_patching_align_already_in_figure_class_html() {
		return <<<CONTENT
<img align="left" src="/img.jpg">
CONTENT;
	}

	/**
	 * Blocks contents before patching, for skip patching if align is already in figure class.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_skip_patching_align_already_in_figure_class_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image {"align":"right"} -->
<div class="wp-block-image"><figure class="alignright"><img src="/img.jpg"/></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for skip patching if align is already in figure class.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_skip_patching_align_already_in_figure_class_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"align":"right"} -->
<div class="wp-block-image"><figure class="alignright"><img src="/img.jpg"/></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for skip patching if align already in header.
	 *
	 * @return string HTML
	 */
	public function get_skip_patching_align_already_in_header_html() {
		return <<<CONTENT
<img align="left" src="/img.jpg">
CONTENT;
	}

	/**
	 * Blocks contents before patching, for skip patching if align already in header.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_skip_patching_align_already_in_header_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image {"align":"right"} -->
<div class="wp-block-image"><figure><img src="/img.jpg"/></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for skip patching if align already in header.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_skip_patching_align_already_in_header_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"align":"right"} -->
<div class="wp-block-image"><figure><img src="/img.jpg"/></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for skip patchgin if inconsistent sources.
	 *
	 * @return string HTML
	 */
	public function get_skip_patchgin_if_inconsistent_sources_html() {
		return <<<CONTENT
<img align="left" src="/img.jpg">
CONTENT;
	}

	/**
	 * Blocks contents before patching, for skip patchgin if inconsistent sources.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_skip_patchgin_if_inconsistent_sources_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image -->
<div class="wp-block-image"><figure class="alignright"><img src="/img.jpg"/></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for skip patchgin if inconsistent sources.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_skip_patchgin_if_inconsistent_sources_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image -->
<div class="wp-block-image"><figure class="alignright"><img src="/img.jpg"/></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for patch align and append to existing block comment attributes.
	 *
	 * @return string HTML
	 */
	public function get_patch_align_and_append_to_existing_block_comment_attributes_html() {
		return <<<CONTENT
<img align="right" src="/img.jpg"/>
CONTENT;
	}

	/**
	 * Blocks contents before patching, for patch align and append to existing block comment attributes.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_patch_align_and_append_to_existing_block_comment_attributes_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":1234} -->
<div class="wp-block-image"><figure><img src="/img.jpg" alt=""/></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for patch align and append to existing block comment attributes.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_patch_align_and_append_to_existing_block_comment_attributes_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":1234,"align":"right"} -->
<div class="wp-block-image"><figure class="alignright"><img src="/img.jpg" alt=""/></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for patch align and append to existing block comment attributes.
	 *
	 * @return string HTML
	 */
	public function get_patch_align_with_preserved_existing_elements_attributes_html() {
		return <<<CONTENT
<img align="right" src="/img.jpg"/>
CONTENT;
	}

	/**
	 * Blocks contents before patching, for patch align and append to existing block comment attributes.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_patch_align_with_preserved_existing_elements_attributes_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":1234} -->
<div class="wp-block-image"><figure param1="val1" param2="val2" class="classA classB"><img src="/img.jpg" alt="" param3="val3"/></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for patch align and append to existing block comment attributes.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_patch_align_with_preserved_existing_elements_attributes_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":1234,"align":"right"} -->
<div class="wp-block-image"><figure param1="val1" param2="val2" class="classA classB alignright"><img src="/img.jpg" alt="" param3="val3"/></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for patch height attribute for image element.
	 *
	 * @return string HTML
	 */
	public function get_should_patch_height_attribute_for_image_element_html() {
		return <<<CONTENT
<img class="size-full wp-image-42124" src="https://mysite.com/CollegeGraphic.jpg" alt="bla-bla" height="1600">
CONTENT;
	}

	/**
	 * Blocks contents before patching, for patch height attribute for image element.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_should_patch_height_attribute_for_image_element_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image -->
<img src="https://mysite.com/CollegeGraphic.jpg" alt="bla-bla" class="wp-image-42124"/>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for patch height attribute for image element.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_should_patch_height_attribute_for_image_element_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"height":1600} -->
<img src="https://mysite.com/CollegeGraphic.jpg" alt="bla-bla" class="wp-image-42124" height="1600"/>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for patch img height attribute to image block header attribute.
	 *
	 * @return string HTML
	 */
	public function get_patch_img_height_attribute_to_image_block_header_attribute_html() {
		return <<<CONTENT
[caption id="attachment_1642" align="alignleft" width="5184"]<img class="size-full wp-image-1642" src="http://img.jpg" alt="Txt" height="3456" /> Txt[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for patch img height attribute to image block header attribute.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_patch_img_height_attribute_to_image_block_header_attribute_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure><img src="http://img.jpg" alt="Txt" class="wp-image-1642" height="3456"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for patch img height attribute to image block header attribute.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_patch_img_height_attribute_to_image_block_header_attribute_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","className":"wp-caption alignleft","height":3456} -->
<div class="wp-block-image wp-caption alignleft"><figure class="is-resized"><img src="http://img.jpg" alt="Txt" class="wp-image-1642" height="3456"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for should not alter height attribute to block if already there.
	 *
	 * @return string HTML
	 */
	public function get_not_alter_height_attribute_to_block_if_already_there_html() {
		return <<<CONTENT
[caption id="attachment_1642" align="alignleft" width="5184"]<img class="size-full wp-image-1642" src="http://img.jpg" alt="Txt" height="3456" /> Txt[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for should not alter height attribute to block if already there.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_not_alter_height_attribute_to_block_if_already_there_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","height":3456,"className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure><img src="http://img.jpg" alt="Txt" class="wp-image-1642" height="3456"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for should not alter height attribute to block if already there.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_not_alter_height_attribute_to_block_if_already_there_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","height":3456,"className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure class="is-resized"><img src="http://img.jpg" alt="Txt" class="wp-image-1642" height="3456"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for should update block height attribute to correct value if already there.
	 *
	 * @return string HTML
	 */
	public function get_update_block_height_attribute_to_correct_value_if_already_there_html() {
		return <<<CONTENT
[caption id="attachment_1642" align="alignleft" width="5184"]<img class="size-full wp-image-1642" src="http://img.jpg" alt="Txt" height="3456" /> Txt[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for should update block height attribute to correct value if already there.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_update_block_height_attribute_to_correct_value_if_already_there_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","height":11,"className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure><img src="http://img.jpg" alt="Txt" class="wp-image-1642" height="3456"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for should update block height attribute to correct value if already there.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_update_block_height_attribute_to_correct_value_if_already_there_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","height":3456,"className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure class="is-resized"><img src="http://img.jpg" alt="Txt" class="wp-image-1642" height="3456"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for update height attribute to block if no other attributes yet present.
	 *
	 * @return string HTML
	 */
	public function get_update_height_attribute_to_block_if_no_other_attributes_yet_present_html() {
		return <<<CONTENT
[caption id="attachment_1642" align="alignleft" width="5184"]<img class="size-full wp-image-1642" src="http://img.jpg" alt="Txt" height="3456" /> Txt[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for update height attribute to block if no other attributes yet present.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_update_height_attribute_to_block_if_no_other_attributes_yet_present_before_patching() {
		return <<<CONTENT
<!-- wp:image -->
<div class="wp-block-image wp-caption alignleft"><figure><img src="http://img.jpg" alt="Txt" class="wp-image-1642" height="3456"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for update height attribute to block if no other attributes yet present.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_update_height_attribute_to_block_if_no_other_attributes_yet_present_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"height":3456} -->
<div class="wp-block-image wp-caption alignleft"><figure class="is-resized"><img src="http://img.jpg" alt="Txt" class="wp-image-1642" height="3456"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for patch img width attribute to image block header attribute.
	 *
	 * @return string HTML
	 */
	public function get_patch_img_width_attribute_to_image_block_header_attribute_html() {
		return <<<CONTENT
[caption id="attachment_1642" align="alignleft" width="5184"]<img class="size-full wp-image-1642" src="http://img.jpg" alt="Txt" width="3456" /> Txt[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for patch img width attribute to image block header attribute.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_patch_img_width_attribute_to_image_block_header_attribute_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure><img src="http://img.jpg" alt="Txt" class="wp-image-1642" width="3456"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for patch img width attribute to image block header attribute.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_patch_img_width_attribute_to_image_block_header_attribute_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","className":"wp-caption alignleft","width":3456} -->
<div class="wp-block-image wp-caption alignleft"><figure class="is-resized"><img src="http://img.jpg" alt="Txt" class="wp-image-1642" width="3456"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for should not alter width attribute to block if already there.
	 *
	 * @return string HTML
	 */
	public function get_should_not_alter_width_attribute_to_block_if_already_there_html() {
		return <<<CONTENT
[caption id="attachment_1642" align="alignleft" width="5184"]<img class="size-full wp-image-1642" src="http://img.jpg" alt="Txt" width="3456" /> Txt[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for should not alter width attribute to block if already there.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_should_not_alter_width_attribute_to_block_if_already_there_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","width":3456,"className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure><img src="http://img.jpg" alt="Txt" class="wp-image-1642" width="3456"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for should not alter width attribute to block if already there.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_should_not_alter_width_attribute_to_block_if_already_there_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","width":3456,"className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure class="is-resized"><img src="http://img.jpg" alt="Txt" class="wp-image-1642" width="3456"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for update block width attribute to correct value if already there.
	 *
	 * @return string HTML
	 */
	public function get_update_block_width_attribute_to_correct_value_if_already_there_html() {
		return <<<CONTENT
[caption id="attachment_1642" align="alignleft" width="5184"]<img class="size-full wp-image-1642" src="http://img.jpg" alt="Txt" width="3456" /> Txt[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for update block width attribute to correct value if already there.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_update_block_width_attribute_to_correct_value_if_already_there_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","width":11,"className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure><img src="http://img.jpg" alt="Txt" class="wp-image-1642" width="3456"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for update block width attribute to correct value if already there.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_update_block_width_attribute_to_correct_value_if_already_there_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","width":3456,"className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure class="is-resized"><img src="http://img.jpg" alt="Txt" class="wp-image-1642" width="3456"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for update width attribute to block if no other attributes yet present.
	 *
	 * @return string HTML
	 */
	public function get_update_width_attribute_to_block_if_no_other_attributes_yet_present_html() {
		return <<<CONTENT
[caption id="attachment_1642" align="alignleft" width="5184"]<img class="size-full wp-image-1642" src="http://img.jpg" alt="Txt" width="3456" /> Txt[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for update width attribute to block if no other attributes yet present.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_update_width_attribute_to_block_if_no_other_attributes_yet_present_before_patching() {
		return <<<CONTENT
<!-- wp:image -->
<div class="wp-block-image wp-caption alignleft"><figure><img src="http://img.jpg" alt="Txt" class="wp-image-1642" width="3456"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for update width attribute to block if no other attributes yet present.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_update_width_attribute_to_block_if_no_other_attributes_yet_present_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"width":3456} -->
<div class="wp-block-image wp-caption alignleft"><figure class="is-resized"><img src="http://img.jpg" alt="Txt" class="wp-image-1642" width="3456"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for should not set isresized class to figure if neither height nor width were patched.
	 *
	 * @return string HTML
	 */
	public function get_should_not_set_isresized_class_to_figure_if_neither_height_nor_width_were_patched_html() {
		return <<<CONTENT
[caption id="attachment_1642" align="alignleft" width="5184"]<img class="size-full wp-image-1642" src="http://img.jpg" alt="Txt" /> Txt[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for should not set isresized class to figure if neither height nor width were patched.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_should_not_set_isresized_class_to_figure_if_neither_height_nor_width_were_patched_before_patching() {
		return <<<CONTENT
<!-- wp:image -->
<div class="wp-block-image wp-caption alignleft"><figure><img src="http://img.jpg" alt="Txt" class="wp-image-1642"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for should not set isresized class to figure if neither height nor width were patched.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_should_not_set_isresized_class_to_figure_if_neither_height_nor_width_were_patched_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image -->
<div class="wp-block-image wp-caption alignleft"><figure><img src="http://img.jpg" alt="Txt" class="wp-image-1642"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for figure element should get is-resized class.
	 *
	 * @return string HTML
	 */
	public function get_figure_element_should_get_isresized_class_html() {
		return <<<CONTENT
[caption id="attachment_1642" align="alignleft" width="5184"]<img class="size-full wp-image-1642" src="http://img.jpg" alt="Txt" width="123"/> Txt[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for figure element should get is-resized class.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_figure_element_should_get_isresized_class_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure><img src="http://img.jpg" alt="Txt" class="wp-image-1642" width="123"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for figure element should get is-resized class.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_figure_element_should_get_isresized_class_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","className":"wp-caption alignleft","width":123} -->
<div class="wp-block-image wp-caption alignleft"><figure class="is-resized"><img src="http://img.jpg" alt="Txt" class="wp-image-1642" width="123"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for figure element should get isresized class appended to other classes.
	 *
	 * @return string HTML
	 */
	public function get_figure_element_should_get_isresized_class_appended_to_other_classes_html() {
		return <<<CONTENT
[caption id="attachment_1642" align="alignleft" width="5184"]<img class="size-full wp-image-1642" src="http://img.jpg" alt="Txt" height="123"/> Txt[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for figure element should get isresized class appended to other classes.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_figure_element_should_get_isresized_class_appended_to_other_classes_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure class="alignleft"><img src="http://img.jpg" alt="Txt" class="wp-image-1642"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for figure element should get isresized class appended to other classes.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_figure_element_should_get_isresized_class_appended_to_other_classes_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","className":"wp-caption alignleft","height":123} -->
<div class="wp-block-image wp-caption"><figure class="alignleft is-resized"><img src="http://img.jpg" alt="Txt" class="wp-image-1642" height="123"/><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for figure element should not get isresized class if already present.
	 *
	 * @return string HTML
	 */
	public function get_figure_element_should_not_get_isresized_class_if_already_present_html() {
		return <<<CONTENT
[caption id="attachment_1642" align="alignleft" width="5184"]<img class="size-full wp-image-1642" src="http://img.jpg" alt="Txt" /> Txt[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for figure element should not get isresized class if already present.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_figure_element_should_not_get_isresized_class_if_already_present_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure class="alignleft is-resized"><img src="http://img.jpg" alt="Txt" class="wp-image-1642" /><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for figure element should not get isresized class if already present.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_figure_element_should_not_get_isresized_class_if_already_present_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption"><figure class="alignleft is-resized"><img src="http://img.jpg" alt="Txt" class="wp-image-1642" /><figcaption>Txt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for should patch width attribute.
	 *
	 * @return string HTML
	 */
	public function get_should_patch_width_attribute_html() {
		return <<<CONTENT
<img class="size-full wp-image-42124" src="https://mysite.com/CollegeGraphic.jpg" alt="bla-bla" width="111">
CONTENT;
	}

	/**
	 * Blocks contents before patching, for should patch width attribute.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_should_patch_width_attribute_before_patching() {
		return <<<CONTENT
<!-- wp:image -->
<img src="https://mysite.com/CollegeGraphic.jpg" alt="bla-bla" class="wp-image-42124"/>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for should patch width attribute.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_should_patch_width_attribute_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"width":111} -->
<img src="https://mysite.com/CollegeGraphic.jpg" alt="bla-bla" class="wp-image-42124" width="111"/>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for should strip double align from div.
	 *
	 * @return string HTML
	 */
	public function get_should_strip_double_align_from_div_html() {
		return <<<CONTENT
<figure id="attachment_1642" aria-describedby="caption-attachment-1642" style="width: 5184px" class="wp-caption alignleft"><img class="size-full wp-image-1642" src="https://elsoberano.org/wp-content/uploads/2019/03/IMG_4542.jpg" alt="Adultos mayores se plegaron a la marcha contras las AFP" width="5184" height="3456" /><figcaption id="caption-attachment-1642" class="wp-caption-text">Adultos mayores se plegaron a la marcha contras las AFP</figcaption></figure>
CONTENT;
	}

	/**
	 * Blocks contents before patching, for should strip double align from div.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_should_strip_double_align_from_div_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure class="alignleft"><img src="https://elsoberano.org/wp-content/uploads/2019/03/IMG_4542.jpg" alt="Adultos mayores se plegaron a la marcha contras las AFP" class="wp-image-1642"/><figcaption>Adultos mayores se plegaron a la marcha contras las AFP</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for should strip double align from div.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_should_strip_double_align_from_div_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","className":"wp-caption alignleft","height":3456,"width":5184} -->
<div class="wp-block-image wp-caption"><figure class="alignleft is-resized"><img src="https://elsoberano.org/wp-content/uploads/2019/03/IMG_4542.jpg" alt="Adultos mayores se plegaron a la marcha contras las AFP" class="wp-image-1642" height="3456" width="5184"/><figcaption>Adultos mayores se plegaron a la marcha contras las AFP</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for should patch only imgs in img blocks.
	 *
	 * @return string HTML
	 */
	public function get_should_patch_only_imgs_in_img_blocks_html() {
		return <<<CONTENT
<img class="wp-image-81945 size-medium" src="https://img2.jpg" alt="alt1" width="222" height="111" />
<ul>
<li>
<img class="wp-image-81950" src="https://ing1.jpg" alt="alt2" width="444" height="333" />
</li>
</ul>
CONTENT;
	}

	/**
	 * Blocks contents before patching, for should patch only imgs in img blocks.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_should_patch_only_imgs_in_img_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":81945} -->
<figure class="wp-block-image"><img src="https://img2.jpg" alt="alt1" class="wp-image-81945"/></figure>
<!-- /wp:image -->

<!-- wp:list -->
<ul><li>
<figure><img class="wp-image-81950" src="https://ing1.jpg" alt="alt2" width="444" height="333"></figure>
</li></ul>
<!-- /wp:list -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for should patch only imgs in img blocks.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_should_patch_only_imgs_in_img_blocks_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":81945,"height":111,"width":222} -->
<figure class="wp-block-image is-resized"><img src="https://img2.jpg" alt="alt1" class="wp-image-81945" height="111" width="222"/></figure>
<!-- /wp:image -->

<!-- wp:list -->
<ul><li>
<figure><img class="wp-image-81950" src="https://ing1.jpg" alt="alt2" width="444" height="333"></figure>
</li></ul>
<!-- /wp:list -->
CONTENT;
	}

	/**
	 * HTML source for should patch only imgs in img blocks example two.
	 *
	 * @return string HTML
	 */
	public function get_should_patch_only_imgs_in_img_blocks_example_two_html() {
		return <<<CONTENT
<img class="wp-image-81945 size-medium" src="https://img2.jpg" alt="alt1" width="222" height="111" />
<img class="wp-image-819453 size-medium" src="https://img23.jpg" alt="alt13" width="2223" height="1113" />
<ul>
<li>
<img class="wp-image-81950" src="https://ing1.jpg" alt="alt2" width="444" height="333" />
</li>
</ul>
CONTENT;
	}

	/**
	 * Blocks contents before patching, for should patch only imgs in img blocks example two.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_should_patch_only_imgs_in_img_blocks_example_two_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":81945} -->
<figure class="wp-block-image"><img src="https://img2.jpg" alt="alt1" class="wp-image-81945"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":819453} -->
<figure class="wp-block-image"><img src="https://img23.jpg" alt="alt13" class="wp-image-819453"/></figure>
<!-- /wp:image -->

<!-- wp:list -->
<ul><li>
<figure><img class="wp-image-81950" src="https://ing1.jpg" alt="alt2" width="444" height="333"></figure>
</li></ul>
<!-- /wp:list -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for should patch only imgs in img blocks example two.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_should_patch_only_imgs_in_img_blocks_example_two_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":81945,"height":111,"width":222} -->
<figure class="wp-block-image is-resized"><img src="https://img2.jpg" alt="alt1" class="wp-image-81945" height="111" width="222"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":819453,"height":1113,"width":2223} -->
<figure class="wp-block-image is-resized"><img src="https://img23.jpg" alt="alt13" class="wp-image-819453" height="1113" width="2223"/></figure>
<!-- /wp:image -->

<!-- wp:list -->
<ul><li>
<figure><img class="wp-image-81950" src="https://ing1.jpg" alt="alt2" width="444" height="333"></figure>
</li></ul>
<!-- /wp:list -->
CONTENT;
	}

}
