<?php
/**
 * Abstract class implementing the PatcherInterface, to be extended by all patchers.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher\Patchers;

use NewspackContentConverter\ContentPatcher\PatcherInterface;

/**
 * Class PatcherAbstract, containing common functionality.
 *
 * @package NewspackContentConverter\ContentPatcher\Patchers
 */
abstract class PatcherAbstract implements PatcherInterface {

	/**
	 * Array of regex patterns -- keys are self explanatory:
	 *      'match_html_element'
	 *      'match_blocks_element'
	 *      'match_attribute_value' - has an extra group, and matches the targeted HTML element attribute's value
	 *      'replace_blocks_element'
	 *
	 * @var array
	 */
	protected $patterns = [];

	/**
	 * Runs a preg_match_all with PREG_OFFSET_CAPTURE.
	 *
	 * @param string $pattern Pattern.
	 * @param string $subject Subject.
	 *
	 * @return array|null preg_match_all with PREG_OFFSET_CAPTURE matches, or null if no matches.
	 */
	protected function match_all_elements( $pattern, $subject ) {
		$preg_match_all_result = preg_match_all( $pattern, $subject, $matches, PREG_OFFSET_CAPTURE );

		return ( false === $preg_match_all_result || 0 === $preg_match_all_result ) ? null : $matches;
	}

	/**
	 * Validates the results of match searches.
	 *
	 * @param array $matches_html   Regex matches in HTML source.
	 * @param array $matches_blocks Regex matches in blocks content.
	 *
	 * @return bool Do the matches correspond.
	 */
	protected function validate_html_and_block_matches( $matches_html, $matches_blocks ) {
		if ( count( $matches_html ) != count( $matches_blocks ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Runs a preg_match.
	 *
	 * @param string $pattern Pattern.
	 * @param string $subject Subject.
	 *
	 * @return bool|mixed Match result, or false.
	 */
	protected function element_match_attribute_value( $pattern, $subject ) {
		return ( 1 === preg_match( $pattern, $subject, $match ) ) ? $match[1] : false;
	}

	/**
	 * Runs preg_replace.
	 *
	 * @param string $pattern_match Regex pattern.
	 * @param string $replacement   Replacement string.
	 * @param string $subject       Regex subject.
	 *
	 * @return string|string[]|null preg_replace's default return.
	 */
	protected function apply_patch_to_block_element( $pattern_match, $replacement, $subject ) {
		return preg_replace( $pattern_match, $replacement, $subject );

	}

	/**
	 * Replaces the block content element with the patched one.
	 *
	 * @param string $subject            String to apply the replacement to.
	 * @param string $replacement        The replacement string.
	 * @param int    $position           Position of the replacment.
	 * @param string $length_replacement Replacement length.
	 *
	 * @return mixed Default return value of substr_replace.
	 */
	protected function replace_block_element( $subject, $replacement, $position, $length_replacement ) {
		return substr_replace( $subject, $replacement, $position, $length_replacement );
	}
}
