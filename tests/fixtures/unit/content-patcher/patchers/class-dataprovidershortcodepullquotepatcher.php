<?php
/**
 * Data Provider for tests.
 *
 * @package Newspack
 */

/**
 * Class DataProviderShortcodePullquotePatcher
 *
 * Data Provider for tests concerning unit/content-patcher/patchers/test-shortcodepullquote-patcher.php
 */
class DataProviderShortcodePullquotePatcher {

	/**
	 * Get an unpatched pullquote shortcode.
	 *
	 * @return string Unpatched block content.
	 */
	public static function get_unpatched_block() {
		return <<<CONTENT
<!-- wp:shortcode -->
[pullquote author="Mary Filardo, president of the 21st Century School Fund" description="" style="new-pullquote"]“It’s just one more nail in the coffin of small towns that are already struggling. The county hospital closed, and the mom-and-pop shops are gone because Walmart opened. When you lose the schools, you lose the community.” [/pullquote]
<!-- /wp:shortcode -->
CONTENT;
	}

	/**
	 * Get a patched pullquote shortcode.
	 *
	 * @return string Patched block content.
	 */
	public static function get_patched_block_expected() {
		return <<<CONTENT
<!-- wp:pullquote {"align":"left"} -->
<figure class="wp-block-pullquote alignleft"><blockquote><p>“It’s just one more nail in the coffin of small towns that are already struggling. The county hospital closed, and the mom-and-pop shops are gone because Walmart opened. When you lose the schools, you lose the community.”</p><cite>Mary Filardo, president of the 21st Century School Fund</cite></blockquote></figure>
<!-- /wp:pullquote -->
CONTENT;
	}

	/**
	 * Get an unpatched pullquote shortcode with no defined author.
	 *
	 * @return string Unpatched block content.
	 */
	public static function get_unpatched_block_no_author() {
		return <<<CONTENT
<!-- wp:shortcode -->
[pullquote author="" description="" style="new-pullquote"]The nation’s school districts spend about $46 billion less per year on facility upkeep than is needed to maintain “healthy and safe” learning environments, according to the 21st Century School Fund.[/pullquote]
<!-- /wp:shortcode -->
CONTENT;
	}

	/**
	 * Get a patched pullquote shortcode with no defined author.
	 *
	 * @return string Patched block content.
	 */
	public static function get_patched_block_no_author_expected() {
		return <<<CONTENT
<!-- wp:pullquote {"align":"left"} -->
<figure class="wp-block-pullquote alignleft"><blockquote><p>The nation’s school districts spend about $46 billion less per year on facility upkeep than is needed to maintain “healthy and safe” learning environments, according to the 21st Century School Fund.</p></blockquote></figure>
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
