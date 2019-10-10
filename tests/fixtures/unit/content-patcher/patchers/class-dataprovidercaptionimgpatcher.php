<?php
/**
 * Data Provider for tests.
 *
 * @package Newspack
 */

/**
 * Class DataProviderCaptionImgPatcher
 *
 * Data Provider for tests concerning unit/content-patcher/patchers/test-caption-img-patcher.php
 */
class DataProviderCaptionImgPatcher {

	/**
	 * HTML source for lost caption attribute from caption element.
	 *
	 * @return string HTML.
	 */
	public function get_lost_caption_attribute_from_caption_element_html() {
		return <<<CONTENT
[caption caption="capA"]<a href="/imgA2.jpg"><img src="/imgA.jpg"/></a>[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching for lost caption attribute from caption element.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_lost_caption_attribute_from_caption_element_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":43525} -->
<div class="wp-block-image"><figure><a href="/imgA2.jpg"><img src="/imgA.jpg"/></a></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching for lost caption attribute from caption element.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_lost_caption_attribute_from_caption_element_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":43525} -->
<div class="wp-block-image"><figure><a href="/imgA2.jpg"><img src="/imgA.jpg"/></a><figcaption>capA</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for lost caption attribute from caption element e.g. 2.
	 *
	 * @return string HTML
	 */
	public function get_lost_caption_attribute_from_caption_element_html_2() {
		return <<<CONTENT
[caption caption="CapB"]<a href="/imgB.jpg"><img class="size-large wp-image-49226" alt="CapB" src="/imgB2.jpg" width="771" height="455" /></a>[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching for lost caption attribute from caption element e.g. 2.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_lost_caption_attribute_from_caption_element_blocks_before_patching_2() {
		return <<<CONTENT
<!-- wp:image {"id":49226,"align":"center"} -->
<div class="wp-block-image"><figure class="aligncenter"><a href="/imgB.jpg"><img src="/imgB2.jpg" alt="CapB" class="wp-image-49226"/></a></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching for lost caption attribute from caption element e.g. 2.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_lost_caption_attribute_from_caption_element_blocks_patched_expected_2() {
		return <<<CONTENT
<!-- wp:image {"id":49226,"align":"center"} -->
<div class="wp-block-image"><figure class="aligncenter"><a href="/imgB.jpg"><img src="/imgB2.jpg" alt="CapB" class="wp-image-49226"/></a><figcaption>CapB</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for lost caption from multiple sources innertext.
	 *
	 * @return string HTML
	 */
	public function get_lost_caption_from_multiple_sources_innertext() {
		return <<<CONTENT
[caption caption="capA"]<a href="/imgA2.jpg"><img src="/imgA.jpg"/></a>[/caption]
[caption]<a href="/imgB2.jpg"><img src="/imgB.jpg"/></a>capB[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching for lost caption from multiple sources innertext.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_lost_caption_from_multiple_sources_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":49226,"align":"center"} -->
<div class="wp-block-image"><figure class="aligncenter"><a href="/imgA.jpg"><img src="/imgA2.jpg"/></a></figure></div>
<!-- /wp:image -->

<!-- wp:image {"id":49226,"align":"center"} -->
<div class="wp-block-image"><figure class="aligncenter"><a href="/imgB.jpg"><img src="/imgB2.jpg"/></a></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching for lost caption from multiple sources innertext.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_lost_caption_from_multiple_sources_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":49226,"align":"center"} -->
<div class="wp-block-image"><figure class="aligncenter"><a href="/imgA.jpg"><img src="/imgA2.jpg"/></a><figcaption>capA</figcaption></figure></div>
<!-- /wp:image -->

<!-- wp:image {"id":49226,"align":"center"} -->
<div class="wp-block-image"><figure class="aligncenter"><a href="/imgB.jpg"><img src="/imgB2.jpg"/></a><figcaption>capB</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source with multiple images where some will not have their content lost.
	 *
	 * @return string HTML
	 */
	public static function get_some_img_captions_ok_html() {
		return <<<CONTENT
[caption caption="capA"]<a href="/imgA2.jpg"><img src="/imgA.jpg"/></a>[/caption]
[caption caption="capB"]<a href="/imgB2.jpg"><img src="/imgB.jpg"/></a>[/caption]
[caption caption="capC"]<a href="/imgC2.jpg"><img src="/imgC.jpg"/></a>[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching, multiple images where some will not have their content lost.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_some_img_captions_ok_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":111} -->
<div class="wp-block-image"><figure><a href="/imgA2.jpg"><img src="/imgA.jpg"/></a></figure></div>
<!-- /wp:image -->

<!-- wp:image {"id":222} -->
<div class="wp-block-image"><figure><a href="/imgB2.jpg"><img src="/imgB.jpg"/></a><figcaption>capB</figcaption></figure></div>
<!-- /wp:image -->

<!-- wp:image {"id":333} -->
<div class="wp-block-image"><figure><a href="/imgC2.jpg"><img src="/imgC.jpg"/></a></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, multiple images where some will not have their content lost.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_some_img_captions_ok_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":111} -->
<div class="wp-block-image"><figure><a href="/imgA2.jpg"><img src="/imgA.jpg"/></a><figcaption>capA</figcaption></figure></div>
<!-- /wp:image -->

<!-- wp:image {"id":222} -->
<div class="wp-block-image"><figure><a href="/imgB2.jpg"><img src="/imgB.jpg"/></a><figcaption>capB</figcaption></figure></div>
<!-- /wp:image -->

<!-- wp:image {"id":333} -->
<div class="wp-block-image"><figure><a href="/imgC2.jpg"><img src="/imgC.jpg"/></a><figcaption>capC</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for some img captions ok even with extra spaces on left.
	 *
	 * @return string HTML
	 */
	public static function get_some_img_captions_ok_even_with_extra_spaces_on_left_html() {
		return <<<CONTENT
[caption id="attachment_1642" align="alignleft" width="12345"]<img class="size-full wp-image-1642" src="https://host2/IMG_4542.jpg" alt="AltTxt" width="5184" height="3456" /> CapTxt[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching for some img captions ok even with extra spaces on left.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_some_img_captions_ok_even_with_extra_spaces_on_left_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure class="alignleft"><img src="https://host2/IMG_4542.jpg" alt="AltTxt" class="wp-image-1642" height="3456" width="5184"/><figcaption>CapTxt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching for some img captions ok even with extra spaces on left.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_some_img_captions_ok_even_with_extra_spaces_on_left_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":1642,"align":"left","className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure class="alignleft"><img src="https://host2/IMG_4542.jpg" alt="AltTxt" class="wp-image-1642" height="3456" width="5184"/><figcaption>CapTxt</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for lost caption innertext.
	 *
	 * @return string HTML
	 */
	public function get_lost_caption_innertext() {
		return <<<CONTENT
[caption]<a href="/imgA2.jpg"><img src="/imgA.jpg"/></a>capA[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching for lost caption innertext.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public function get_lost_caption_innertext_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":43525} -->
<div class="wp-block-image"><figure><a href="/imgA2.jpg"><img src="/imgA.jpg"/></a></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching for lost caption innertext.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public function get_lost_caption_innertext_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":43525} -->
<div class="wp-block-image"><figure><a href="/imgA2.jpg"><img src="/imgA.jpg"/></a><figcaption>capA</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source with multiple images out of which some don't have the height attribute.
	 *
	 * @return string HTML
	 */
	public static function get_some_skipped_captions_html() {
		return <<<CONTENT
[caption caption="capA"]<a href="/imgA2.jpg"><img src="/imgA.jpg"/></a>[/caption]
[caption]<a href="/imgB2.jpg"><img src="/imgB.jpg"/></a>[/caption]
[caption]<a href="/imgC2.jpg"><img src="/imgC.jpg"/></a>capC[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for an example where some images don't have the height attribute.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_some_skipped_captions_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image -->
<div class="wp-block-image"><figure><a href="/imgA2.jpg"><img src="/imgA.jpg"/></a></figure></div>
<!-- /wp:image -->

<!-- wp:image -->
<div class="wp-block-image"><figure><a href="/imgB2.jpg"><img src="/imgB.jpg"/></a></figure></div>
<!-- /wp:image -->

<!-- wp:image -->
<div class="wp-block-image"><figure><a href="/imgC2.jpg"><img src="/imgC.jpg"/></a></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for an example where some images don't have the height attribute.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_some_skipped_captions_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image -->
<div class="wp-block-image"><figure><a href="/imgA2.jpg"><img src="/imgA.jpg"/></a><figcaption>capA</figcaption></figure></div>
<!-- /wp:image -->

<!-- wp:image -->
<div class="wp-block-image"><figure><a href="/imgB2.jpg"><img src="/imgB.jpg"/></a></figure></div>
<!-- /wp:image -->

<!-- wp:image -->
<div class="wp-block-image"><figure><a href="/imgC2.jpg"><img src="/imgC.jpg"/></a><figcaption>capC</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * HTML source for a comprehensive example, containing multiple elements.
	 *
	 * @return string HTML
	 */
	public static function get_comprehensive_html() {
		return <<<CONTENT
<p>Some content before</p>
<p>First image has caption as caption attribute</p>
[caption caption="capA"]<a href="/imgA2.jpg"><img src="/imgA.jpg"/></a>[/caption]
<p>Next image has caption as caption element inner text</p>
[caption]<a href="/imgB2.jpg"><img src="/imgB.jpg"/></a>capB[/caption]
<p>Next image has no valid caption</p>
[caption]<a href="/imgC2.jpg"><img src="/imgC.jpg"/></a>[/caption]
<p>Next two images has already validly converted caption and should be skipped by the patcher</p>
[caption caption="capD"]<a href="/imgD2.jpg"><img src="/imgD.jpg"/></a>[/caption]
[caption caption="capE"]<a href="/imgE2.jpg"><img src="/imgE.jpg"/></a>capE[/caption]
<p>Next already has caption, and shouldn't be added again</p>
[caption id="attachment_1642" align="alignleft" width="5184"]<img class="size-full wp-image-1642" src="https://host/IMG_4542.jpg" alt="AltTxt" width="456" height="123" /> CapTxt[/caption]
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

<!-- wp:paragraph -->
<p>First image has caption as caption attribute</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<div class="wp-block-image"><figure><a href="/imgA2.jpg"><img src="/imgA.jpg"/></a></figure></div>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>Next image has caption as caption element inner text</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<div class="wp-block-image"><figure><a href="/imgB2.jpg"><img src="/imgB.jpg"/></a></figure></div>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>Next image has no valid caption</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<div class="wp-block-image"><figure><a href="/imgA2.jpg"><img src="/imgA.jpg"/></a></figure></div>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>Next two images has already validly converted caption and should be skipped by the patcher</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<div class="wp-block-image"><figure><a href="/imgD2.jpg"><img src="/imgD.jpg"/></a><figcaption>capD</figcaption></figure></div>
<!-- /wp:image -->

<!-- wp:image -->
<div class="wp-block-image"><figure><a href="/imgE2.jpg"><img src="/imgE.jpg"/></a><figcaption>capE</figcaption></figure></div>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>Next already has caption, and shouldn't be added again</p>
<!-- /wp:paragraph -->
<p>

<!-- wp:image {"id":1642,"align":"left","className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure class="alignleft"><img src="https://host/IMG_4542.jpg" alt="AltTxt" class="wp-image-1642" height="123" width="456"/><figcaption>CapTxt</figcaption></figure></div>
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

<!-- wp:paragraph -->
<p>First image has caption as caption attribute</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<div class="wp-block-image"><figure><a href="/imgA2.jpg"><img src="/imgA.jpg"/></a><figcaption>capA</figcaption></figure></div>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>Next image has caption as caption element inner text</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<div class="wp-block-image"><figure><a href="/imgB2.jpg"><img src="/imgB.jpg"/></a><figcaption>capB</figcaption></figure></div>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>Next image has no valid caption</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<div class="wp-block-image"><figure><a href="/imgA2.jpg"><img src="/imgA.jpg"/></a></figure></div>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>Next two images has already validly converted caption and should be skipped by the patcher</p>
<!-- /wp:paragraph -->

<!-- wp:image -->
<div class="wp-block-image"><figure><a href="/imgD2.jpg"><img src="/imgD.jpg"/></a><figcaption>capD</figcaption></figure></div>
<!-- /wp:image -->

<!-- wp:image -->
<div class="wp-block-image"><figure><a href="/imgE2.jpg"><img src="/imgE.jpg"/></a><figcaption>capE</figcaption></figure></div>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>Next already has caption, and shouldn't be added again</p>
<!-- /wp:paragraph -->
<p>

<!-- wp:image {"id":1642,"align":"left","className":"wp-caption alignleft"} -->
<div class="wp-block-image wp-caption alignleft"><figure class="alignleft"><img src="https://host/IMG_4542.jpg" alt="AltTxt" class="wp-image-1642" height="123" width="456"/><figcaption>CapTxt</figcaption></figure></div>
<!-- /wp:image -->

<!-- wp:paragraph -->
<p>Some content in the end</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * HTML source for inconsistent sources case.
	 *
	 * @return string HTML
	 */
	public static function get_inconsistent_sources_html() {
		return <<<CONTENT
[caption caption="capA"]<a href="/imgA2.jpg"><img src="/imgA.jpg"/></a>[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching for inconsistent sources case.
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
	 * Expected blocks contents after patching for inconsistent sources case.
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
	 * HTML source for HTML is non pertinent.
	 *
	 * @return string HTML
	 */
	public static function get_html_is_non_pertinent_html() {
		return <<<CONTENT
<p>This has nothing to do with the patcher</p>
CONTENT;
	}

	/**
	 * Blocks contents before patching for HTML is non pertinent.
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
	 * Expected blocks contents after patching for HTML is non pertinent.
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
	 * HTML source for autoformatting changes.
	 *
	 * @return string HTML
	 */
	public static function get_autoformatting_changes_html() {
		return <<<CONTENT
[caption id="attachment_48956" align="alignright" width="2937"]<img class="size-full wp-image-48956" src="https://hechingerreport.org/wp-content/uploads/2019/12/CarrNOLA-2-and-FEAT.jpg" alt="" width="2937" height="2946" /> quotes "here" get auto transformed[/caption]
CONTENT;
	}

	/**
	 * Blocks contents before patching for autoformatting changes.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_autoformatting_changes_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:image {"id":48956,"align":"right","className":"wp-caption alignright"} -->
<div class="wp-block-image wp-caption alignright"><figure class="alignright"><img src="https://hechingerreport.org/wp-content/uploads/2019/12/CarrNOLA-2-and-FEAT.jpg" alt="" class="wp-image-48956"/><figcaption>quotes “here” get auto transformed</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching for autoformatting changes.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_autoformatting_changes_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:image {"id":48956,"align":"right","className":"wp-caption alignright"} -->
<div class="wp-block-image wp-caption alignright"><figure class="alignright"><img src="https://hechingerreport.org/wp-content/uploads/2019/12/CarrNOLA-2-and-FEAT.jpg" alt="" class="wp-image-48956"/><figcaption>quotes “here” get auto transformed</figcaption></figure></div>
<!-- /wp:image -->
CONTENT;
	}
}
