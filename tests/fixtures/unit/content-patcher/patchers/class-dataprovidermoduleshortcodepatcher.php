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
class DataProviderModuleShortcodePatcher {

	/**
	 * Get an unpatched left-aligned module shortcode.
	 *
	 * @return string Unpatched block content.
	 */
	public static function get_unpatched_block_left() {
		return <<<CONTENT
<!-- wp:shortcode -->
[module align="left" width="half" type="pull-quote"]"The Board will add this to the ongoing investigation." —New Beginnings attorney Michelle Craig[/module]
<!-- /wp:shortcode -->
CONTENT;
	}

	/**
	 * Get an patched left-aligned module shortcode.
	 *
	 * @return string Patched block content.
	 */
	public static function get_patched_block_left_expected() {
		return <<<CONTENT
<!-- wp:pullquote {"align":"left"} -->
<figure class="wp-block-pullquote alignleft"><blockquote><p>"The Board will add this to the ongoing investigation." —New Beginnings attorney Michelle Craig</p></blockquote></figure>
<!-- /wp:pullquote -->
CONTENT;
	}

	/**
	 * Get an unpatched right-aligned module shortcode.
	 *
	 * @return string Unpatched block content.
	 */
	public static function get_unpatched_block_right() {
		return <<<CONTENT
<!-- wp:shortcode -->
[module align=right width=”full” type=”aside”]”The Board will add this to the ongoing investigation.” —New Beginnings attorney Michelle Craig[/module]
<!-- /wp:shortcode -->
CONTENT;
	}

	/**
	 * Get an patched right-aligned module shortcode.
	 *
	 * @return string Patched block content.
	 */
	public static function get_patched_block_right_expected() {
		return <<<CONTENT
<!-- wp:pullquote {"align":"right"} -->
<figure class="wp-block-pullquote alignright"><blockquote><p>"The Board will add this to the ongoing investigation." —New Beginnings attorney Michelle Craig</p></blockquote></figure>
<!-- /wp:pullquote -->
CONTENT;
	}

	/**
	 * Get unpatched center-aligned module shortcodes.
	 *
	 * @return string Unpatched block content.
	 */
	public static function get_unpatched_blocks_center() {
		return <<<CONTENT
<!-- wp:shortcode -->
[module align="center" width="half" type="pull-quote"]Test content[/module]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[module]Test content[/module]
<!-- /wp:shortcode -->
CONTENT;
	}

	/**
	 * Get an patched center-aligned module shortcode.
	 *
	 * @return string Patched block content.
	 */
	public static function get_patched_blocks_center_expected() {
		return <<<CONTENT
<!-- wp:pullquote -->
<figure class="wp-block-pullquote"><blockquote><p>"The Board will add this to the ongoing investigation." —New Beginnings attorney Michelle Craig</p></blockquote></figure>
<!-- /wp:pullquote -->
CONTENT;
	}

	/**
	 * Get an unpatched module shortcode with unsupported HTML content.
	 *
	 * @return string Unpatched block content.
	 */
	public static function get_unpatched_block_unsupported_tags() {
		return <<<CONTENT
<!-- wp:shortcode -->
[module align=”right” width=”half” type=”aside”]
<h6 class="p1">Previous coverage: <a href="https://thelensnola.org/2019/03/22/f-to-d-grade-changes-at-kennedy-high-school-are-suspicious-former-administrator-says/">F to D grade changes at Kennedy High School are suspicious, former administrator says</a></h6>
[/module]
<!-- /wp:shortcode -->
CONTENT;
	}

	/**
	 * Get an patched module shortcode with unsupported HTML content removed.
	 *
	 * @return string Patched block content.
	 */
	public static function get_patched_block_unsupported_tags_expected() {
		return <<<CONTENT
<!-- wp:pullquote -->
<figure class="wp-block-pullquote"><blockquote><p>Previous coverage: <a href="https://thelensnola.org/2019/03/22/f-to-d-grade-changes-at-kennedy-high-school-are-suspicious-former-administrator-says/">F to D grade changes at Kennedy High School are suspicious, former administrator says</a></p></blockquote></figure>
<!-- /wp:pullquote -->
CONTENT;
	}

	/**
	 * Get an unpatched non-relevant content.
	 *
	 * @return string Unpatched block content.
	 */
	public static function get_unpatched_blocks_non_pertinent() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p>This is a paragraph.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[nonpertinent shortcode att="test"]This is some content[/nonpertinent]
<!-- /wp:shortcode -->

<!-- wp:paragraph -->
<p>This is a paragraph after.</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * Get patched non-relevant content.
	 *
	 * @return string Patched block content.
	 */
	public static function get_patched_blocks_non_pertinent_expected() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p>This is a paragraph.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[nonpertinent shortcode att="test"]This is some content[/nonpertinent]
<!-- /wp:shortcode -->

<!-- wp:paragraph -->
<p>This is a paragraph after.</p>
<!-- /wp:paragraph -->
CONTENT;
	}
}
