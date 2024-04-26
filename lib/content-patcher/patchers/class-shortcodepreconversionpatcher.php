<?php
/**
 * A pre-conversion Patcher for shortcode elements.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher\Patchers;

use NewspackContentConverter\ContentPatcher\Patchers\PreconversionPatcherAbstract;

/**
 * Pre-conversion Patcher class for the shortcode elements.
 *
 * @package NewspackContentConverter\ContentPatcher\Patchers
 */
class ShortcodePreconversionPatcher extends PreconversionPatcherAbstract {
	/**
	 * See the \NewspackContentConverter\ContentPatcher\Patchers\PreconversionPatcherInterface::patch_html_source for description.
	 *
	 * @param string $html HTML source before conversion to blocks.
	 * @param int    $post_id Post ID.
	 *
	 * @return string Patched HTML source before conversion to blocks.
	 */
	public function patch_html_source( $html, $post_id ) {
		$html_patched = $this->break_shortcodes_to_new_line( $html );

		return $html_patched;
	}

	/**
	 * Detects certain shortcode elements, and if they're inline with preceeding text, adds a line break so that the shortcode
	 * is at the beginning of the line. This is necessary for Gutenberg to convert these shortcodes properly.
	 *
	 * @param string $html HTML source before conversion to blocks.
	 *
	 * @return string Patched HTML source before conversion to blocks.
	 */
	private function break_shortcodes_to_new_line( $html ) {
		$pattern = '|
		\[          # beginning of shortcode
		gallery     # literal
		[^\]]*      # any character except the closing bracket
		\]          # end of shortcode
		|xim';

		preg_match_all( $pattern, $html, $matches, PREG_OFFSET_CAPTURE );
		if ( ! isset( $matches[0] ) || empty( $matches[0] ) ) {
			return $html;
		}

		// Reverse the matches array, so that we can go through matches from backwards, since we'll be inserting (new line) characters.
		$matches[0] = array_reverse( $matches[0] );

		foreach ( $matches[0] as $match_with_offset ) {
			$match  = $match_with_offset[0];
			$offset = $match_with_offset[1];

			$is_at_the_beginning = 0 === $offset;
			if ( $is_at_the_beginning ) {
				continue;
			}

			$preceeding_char                 = substr( $html, $offset - 1, 1 );
			$is_preceeding_char_a_line_break = "\n" == $preceeding_char || "\r" == $preceeding_char;
			if ( $is_preceeding_char_a_line_break ) {
				continue;
			}

			// Insert line break before the shortcode if it's not there, and it's not at the beginning of content.
			$html = substr_replace( $html, "\n", $offset, 0 );
		}

		return $html;
	}
}
