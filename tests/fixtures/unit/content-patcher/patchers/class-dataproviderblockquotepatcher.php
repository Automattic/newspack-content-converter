<?php
/**
 * Data Provider for tests.
 *
 * @package Newspack
 */

/**
 * Class DataProviderBlockquotePatcher
 *
 * Data Provider for tests concerning unit/content-patcher/patchers/test-blockquote-patcher.php
 */
class DataProviderBlockquotePatcher {

	/**
	 * HTML source for a comprehensive example, containing multiple elements.
	 *
	 * @return string HTML
	 */
	public static function get_comprehensive_html() {
		return <<<CONTENT
<p>Some content before</p>
<blockquote data-lang="es">AAA</blockquote>
<p>The second blockquote doesn&#8217;t have any attributes, so it should be skipped by the patcher</p>
<blockquote>BBB</blockquote>
<p>The third blockquotes has a different data-lang attribute, so it should also be patched</p>
<blockquote data-lang="ee">CCC</blockquote>
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

<!-- wp:quote -->
<blockquote>AAA</blockquote>
<!-- /wp:quote -->

<!-- wp:paragraph -->
<p>The second blockquote doesn't have any attributes, so it should be skipped by the patcher</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote>BBB</blockquote>
<!-- /wp:quote -->

<!-- wp:paragraph -->
<p>The third blockquotes has a different data-lang attribute, so it should also be patched</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote>CCC</blockquote>
<!-- /wp:quote -->

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

<!-- wp:quote -->
<blockquote data-lang="es">AAA</blockquote>
<!-- /wp:quote -->

<!-- wp:paragraph -->
<p>The second blockquote doesn't have any attributes, so it should be skipped by the patcher</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote>BBB</blockquote>
<!-- /wp:quote -->

<!-- wp:paragraph -->
<p>The third blockquotes has a different data-lang attribute, so it should also be patched</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote data-lang="ee">CCC</blockquote>
<!-- /wp:quote -->

<!-- wp:paragraph -->
<p>Some content in the end</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * HTML source with multiple blockquotes.
	 *
	 * @return string HTML
	 */
	public static function get_multiple_blockquotes_html() {
		return <<<CONTENT
<blockquote data-lang="es">AAA</blockquote>
<blockquote data-lang="se">BBB</blockquote>
<blockquote data-lang="ee">CCC</blockquote>
CONTENT;
	}

	/**
	 * Blocks contents before patching, for an example containing multiple blockquote.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_multiple_blockquotes_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:quote -->
<blockquote>AAA</blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote>BBB</blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote>CCC</blockquote>
<!-- /wp:quote -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for an example containing multiple blockquotes.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_multiple_blockquotes_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:quote -->
<blockquote data-lang="es">AAA</blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote data-lang="se">BBB</blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote data-lang="ee">CCC</blockquote>
<!-- /wp:quote -->
CONTENT;
	}

	/**
	 * HTML source with multiple blockquotes out of which some don't have the data-lang attribute.
	 *
	 * @return string HTML
	 */
	public static function get_some_skipped_blockquotes_html() {
		return <<<CONTENT
<blockquote data-lang="es">AAA</blockquote>
<blockquote>BBB</blockquote>
<blockquote data-lang="ee">CCC</blockquote>
CONTENT;
	}

	/**
	 * Blocks contents before patching, for an example where some blockquotes don't have the height attribute.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_some_skipped_blockquotes_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:quote -->
<blockquote>AAA</blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote>BBB</blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote>CCC</blockquote>
<!-- /wp:quote -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for an example where some blockquotes don't have the data-lang attribute.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_some_skipped_blockquotes_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:quote -->
<blockquote data-lang="es">AAA</blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote>BBB</blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote data-lang="ee">CCC</blockquote>
<!-- /wp:quote -->
CONTENT;
	}

	/**
	 * HTML source with multiple blockquotes where some will not have their content lost.
	 *
	 * @return string HTML
	 */
	public static function get_some_blockquotes_ok_html() {
		return <<<CONTENT
<blockquote data-lang="es">AAA</blockquote>
<blockquote data-lang="se">BBB</blockquote>
<blockquote data-lang="ee">CCC</blockquote>
CONTENT;
	}

	/**
	 * Blocks contents before patching, multiple blockquotes where some will not have their content lost.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_some_blockquotes_ok_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:quote -->
<blockquote>AAA</blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote data-lang="se">BBB</blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote>CCC</blockquote>
<!-- /wp:quote -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, multiple blockquotes where some will not have their content lost.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_some_blockquotes_ok_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:quote -->
<blockquote data-lang="es">AAA</blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote data-lang="se">BBB</blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote data-lang="ee">CCC</blockquote>
<!-- /wp:quote -->
CONTENT;
	}

	/**
	 * HTML source for inconsistent sources.
	 *
	 * @return string HTML
	 */
	public static function get_inconsistent_sources_html() {
		return <<<CONTENT
<blockquote data-lang="es">AAA</blockquote>
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
