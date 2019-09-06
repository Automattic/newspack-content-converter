<?php
/**
 * Abstract class implementing the PatcherInterface, to be extended by all patchers.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher\Patchers;

use NewspackContentConverter\ContentPatcher\Patchers\PatcherInterface;

/**
 * Class PatcherAbstract, containing common functionality.
 *
 * @package NewspackContentConverter\ContentPatcher\Patchers
 */
abstract class PatcherAbstract implements PatcherInterface {

	/**
	 * Validates the results of match searches.
	 *
	 * @param array $matches_html   Regex matches in HTML source.
	 * @param array $matches_blocks Regex matches in blocks content.
	 *
	 * @return bool Do the matches correspond.
	 */
	public function validate_html_and_block_matches( $matches_html, $matches_blocks ) {
		if ( ! is_array( $matches_html ) || ! is_array( $matches_blocks ) ) {
			return false;
		}

		if ( count( $matches_html ) != count( $matches_blocks ) ) {
			return false;
		}

		return true;
	}
}
