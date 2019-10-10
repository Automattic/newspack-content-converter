<?php
/**
 * Data Provider for tests.
 *
 * @package Newspack
 */

/**
 * Class DataProviderParagraphPatcher
 *
 * Data Provider for tests concerning unit/content-patcher/patchers/test-paragraph-patcher.php
 */
class DataProviderParagraphPatcher {

	/**
	 * HTML source for a comprehensive example, containing multiple elements.
	 *
	 * @return string HTML
	 */
	public static function get_comprehensive_html() {
		return <<<CONTENT
<p>Some content before</p>
<p dir="ltr">AAA</p>
<p>The second paragraph doesn&#8217;t have any attributes, so it should be skipped by the patcher</p>
<p>BBB</p>
<p>The third paragraph has a different dir attribute, so it should also be patched</p>
<p dir="rtl">CCC</p>
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
<p>AAA</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>The second paragraph doesn't have any attributes, so it should be skipped by the patcher</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>BBB</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>The third paragraph has a different dir attribute, so it should also be patched</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>CCC</p>
<!-- /wp:paragraph -->

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
<p dir="ltr">AAA</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>The second paragraph doesn't have any attributes, so it should be skipped by the patcher</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>BBB</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>The third paragraph has a different dir attribute, so it should also be patched</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p dir="rtl">CCC</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Some content in the end</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * HTML source with multiple paragraphs.
	 *
	 * @return string HTML
	 */
	public static function get_multiple_paragraphs_html() {
		return <<<CONTENT
<p dir="ltr">AAA</p>
<p dir="rtl">BBB</p>
<p dir="ltr">CCC</p>
CONTENT;
	}

	/**
	 * Blocks contents before patching, for an example containing multiple paragraphs.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_multiple_paragraphs_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p>AAA</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>BBB</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>CCC</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for an example containing multiple paragraphs.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_multiple_paragraphs_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p dir="ltr">AAA</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p dir="rtl">BBB</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p dir="ltr">CCC</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * HTML source with multiple paragraphs out of which some don't have the dir attribute.
	 *
	 * @return string HTML
	 */
	public static function get_some_skipped_paragraphs_html() {
		return <<<CONTENT
<p dir="ltr">AAA</p>
<p>BBB</p>
<p dir="rtl">CCC</p>
CONTENT;
	}

	/**
	 * Blocks contents before patching, for an example where some paragraphs don't have the dir attribute.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_some_skipped_paragraphs_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p>AAA</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>BBB</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>CCC</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for an example where some paragraphs don't have the dir attribute.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_some_skipped_paragraphs_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p dir="ltr">AAA</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>BBB</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p dir="rtl">CCC</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * HTML source with multiple paragraphs where some will not have their content lost.
	 *
	 * @return string HTML
	 */
	public static function get_some_paragraphs_ok_html() {
		return <<<CONTENT
<p dir="ltr">AAA</p>
<p dir="ltr">BBB</p>
<p dir="rtl">CCC</p>
CONTENT;
	}

	/**
	 * Blocks contents before patching, multiple paragraphs where some will not have their content lost.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_some_paragraphs_ok_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p>AAA</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p dir="ltr">BBB</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>CCC</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, multiple paragraphs where some will not have their content lost.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_some_paragraphs_ok_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p dir="ltr">AAA</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p dir="ltr">BBB</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p dir="rtl">CCC</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * HTML source for inconsistent sources.
	 *
	 * @return string HTML
	 */
	public static function get_inconsistent_sources_html() {
		return <<<CONTENT
<img src="/img.jpg"/>
CONTENT;
	}

	/**
	 * Blocks contents before patching for inconsistent sources.
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
	 * Expected blocks contents after patching for inconsistent sources.
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
	 * Blocks contents before patching for non pertinent HTML.
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
	 * Expected blocks contents after patching for non pertinent HTML.
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
}
