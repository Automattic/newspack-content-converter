<?php
/**
 * Patcher for the image elements.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher\Patchers;

use NewspackContentConverter\ContentPatcher\PatcherInterface;
use NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract;

/**
 * Patcher class for the image elements.
 *
 * @package NewspackContentConverter\ContentPatcher\Patchers
 */
class ImgPatcher extends PatcherAbstract implements PatcherInterface {

	/**
	 * See the \NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract::$patterns for description.
	 *
	 * @var array
	 */
	protected $patterns = [
		'match_html_element'     => '/<img.*?>/xim',
		'match_blocks_element'   => '/<img.*?>/im',
		'match_attribute_value'  => '/
			.*?             # anything at the beginning of the string
			height          # then locate the height attribute
			\s*             # followed by zero or more spaces
			=               # the equals char
			\s*             # once again, with possible zero or more spaces
			["\']           # the attribute value starts with one double or a single quote
			([^"\']+)       # capture (using parenthesis) the value -- one or more chars except (and up to) double or single quote
                            # (we don\'t care about the rest of the string, since we\'ve captured the attribute by now)
		/xim',
		'replace_blocks_element' => '|\/>|',
	];

	/**
	 * See the \NewspackContentConverter\ContentPatcher\PatcherInterface::patch_blocks_contents for description.
	 *
	 * @param string $source_html   HTML source, original content being converted.
	 * @param string $source_blocks Block content as result of Gutenberg "conversion to blocks".
	 *
	 * @return string|null
	 */
	public function patch_blocks_contents( $source_html, $source_blocks ) {

		$matches_html = $this->match_all_elements( $this->patterns['match_html_element'], $source_html );
		if ( ! $matches_html ) {
			// TODO: log 'no elements matched in HTML'.
			return false;
		}

		$matches_blocks = $this->match_all_elements( $this->patterns['match_blocks_element'], $source_blocks );
		if ( ! $this->validate_html_and_block_matches( $matches_html[0], $matches_blocks[0] ) ) {
			// TODO: log 'HTML and block matches do not correspond'.
			return false;
		}

		// Applying array_reverse() on matched results, because when iterating over them, the patcher might apply several patches,
		// and the easiest way to preserve the positions of all the strings which are being replaced, is to just patch (replace)
		// from end to start.
		$matches_html[0]   = array_reverse( $matches_html[0] );
		$matches_blocks[0] = array_reverse( $matches_blocks[0] );

		foreach ( $matches_html[0] as $key => $match_html ) {

			$html_element            = $match_html[0];
			$position_html_element   = $match_html[1];
			$blocks_element          = $matches_blocks[0][ $key ][0];
			$position_blocks_element = $matches_blocks[0][ $key ][1];

			// Check that this attribute exists in the HTML.
			$attribute_value = $this->element_match_attribute_value( $this->patterns['match_attribute_value'], $html_element );
			if ( ! $attribute_value ) {
				continue;
			}

			// Check that this attribute doesn't already exist in the blocks contents.
			if ( $this->element_match_attribute_value( $this->patterns['match_attribute_value'], $blocks_element ) ) {
				continue;
			}

			$attribute_patch       = sprintf( ' height="%s" />', $attribute_value );
			$patched_block_element = $this->apply_patch_to_block_element( $this->patterns['replace_blocks_element'], $attribute_patch, $blocks_element );

			$source_blocks = $this->replace_block_element( $source_blocks, $patched_block_element, $position_blocks_element, strlen( $blocks_element ) );
		}

		return $source_blocks;
	}
}
