<?php
/**
 * A pre-conversion Patcher running WP filters.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher\Patchers;

/**
 * A pre-conversion Patcher running WP filters.
 *
 * @package NewspackContentConverter\ContentPatcher\Patchers
 */
class WpFiltersPatcher extends PreconversionPatcherAbstract {
	/**
	 * See the \NewspackContentConverter\ContentPatcher\Patchers\PreconversionPatcherInterface::patch_html_source for description.
	 *
	 * @param string $html_content HTML source before conversion to blocks.
	 * @param int    $post_id      Post ID.
	 *
	 * @return string Patched HTML source before conversion to blocks.
	 */
	public function patch_html_source( $html_content, $post_id ) {
		// Do not run the `do_shortcode` function which substitutes shortcodes with rendered HTML
		// -- let Gutenberg convert those.
		remove_filter( 'the_content', 'do_shortcode', 11 );
		$html_content = apply_filters( 'the_content', $html_content );

		return $html_content;
	}
}
