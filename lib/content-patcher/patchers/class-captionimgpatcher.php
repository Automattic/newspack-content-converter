<?php
/**
 * Patcher for the image elements.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher\Patchers;

use NewspackContentConverter\ContentPatcher\Patchers\PatcherInterface;
use NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract;
use NewspackContentConverter\ContentPatcher\ElementManipulators\SquareBracketsElementManipulator;
use NewspackContentConverter\ContentPatcher\ElementManipulators\WpBlockManipulator;
use NewspackContentConverter\ContentPatcher\ElementManipulators\HtmlElementManipulator;

/**
 * Patcher class for the [caption]<img/>[/caption] -- caption surrounding image elements.
 *
 * @package NewspackContentConverter\ContentPatcher\Patchers
 */
class CaptionImgPatcher extends PatcherAbstract implements PatcherInterface {

	/**
	 * SquareBracketsElementManipulator service.
	 *
	 * @var SquareBracketsElementManipulator
	 */
	private $square_brackets_element_manipulator;

	/**
	 * WpBlockManipulator service.
	 *
	 * @var WpBlockManipulator
	 */
	private $wp_block_manipulator;

	/**
	 * HtmlElementManipulator service.
	 *
	 * @var HtmlElementManipulator
	 */
	private $html_element_manipulator;

	/**
	 * CaptionImgPatcher constructor.
	 */
	public function __construct() {
		$this->square_brackets_element_manipulator = new SquareBracketsElementManipulator();
		$this->wp_block_manipulator                = new WpBlockManipulator();
		$this->html_element_manipulator            = new HtmlElementManipulator();
	}

	/**
	 * See the \NewspackContentConverter\ContentPatcher\Patchers\PatcherInterface::patch_blocks_contents for description.
	 *
	 * @param string $source_html   HTML source, original content being converted.
	 * @param string $source_blocks Block content as result of Gutenberg "conversion to blocks".
	 *
	 * @return string|false
	 */
	public function patch_blocks_contents( $source_html, $source_blocks ) {

		$matches_html = $this->square_brackets_element_manipulator->match_elements_with_closing_tags( 'caption', $source_html );
		if ( ! $matches_html ) {
			// TODO: DEBUG LOG 'no elements matched in HTML'.
			return $source_blocks;
		}

		$matches_blocks = $this->wp_block_manipulator->match_wp_block( 'wp:image', $source_blocks );
		if ( is_null( $matches_blocks ) ) {
			return $source_blocks;
		}
		if ( ! $this->validate_html_and_block_matches( $matches_html[0], $matches_blocks[0] ) ) {
			// TODO: DEBUG LOG 'HTML and block matches do not correspond'.
			return $source_blocks;
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

			// Patch `caption` attribute or `caption` as inner text value to <figcaption>.
			// The attribute value could be located either as an HTML element attribute, or as the caption element's inner text.
			// Here we're searching for both.
			$attribute_value = $this->html_element_manipulator->get_attribute_value( 'caption', $html_element )
				? $this->html_element_manipulator->get_attribute_value( 'caption', $html_element )
				: $this->get_caption_element_inner_text_value( $html_element );
			if ( $attribute_value ) {
				$patched_block_element = $this->patch_caption_elements_caption_attribute( $blocks_element, $attribute_value );
				$source_blocks         = substr_replace( $source_blocks, $patched_block_element, $position_blocks_element, strlen( $blocks_element ) );
			}
		}

		return $source_blocks;
	}

	/**
	 * Patches the caption in the block element.
	 *
	 * @param string $block_element Block element.
	 * @param string $caption The caption string.
	 *
	 * @return string|false Updated block element, or false.
	 */
	private function patch_caption_elements_caption_attribute( $block_element, $caption ) {

		// The found caption is to be patched as a new <figcaption> element inside the <figure> element, like this:
		// - before patching: <figure></figure>
		// - after patching:  <figure><figcaption>$caption</figcaption></figure>
		// Find and patch the <figure> element from the $blocks_element.
		$figure_element_matches = $this->html_element_manipulator->match_elements_with_closing_tags( 'figure', $block_element );
		if ( ! $figure_element_matches ) {
			// TODO: DEBUG LOG 'no <figure> found in block element'.
			return false;
		}
		if ( ! isset( $figure_element_matches[0][0][0] ) ) {
			// TODO: DEBUG LOG 'no <figure> match found in block element'.
			return false;
		}

		$figure_element     = $figure_element_matches[0][0][0];
		$figure_element_pos = $figure_element_matches[0][0][1];
		$search_figure      = '</figure>';
		$replace_figure     = sprintf( '<figcaption>%s</figcaption></figure>', $caption );
		$pos_figure         = strpos( $figure_element, $search_figure );

		// Check if <figure> already contains the '$caption</figcaption>' -- not to patch twice.
		// To check whether caption already exists in the element, applying a cleanup of characters for easier comparison (eg. the
		// HTML double quotes get transformed to fancy quotes in blocks, so we need to equalize these before comoparing them).
		$pattern_clean_for_comparison  = '/[^a-zA-Z0-9\<\>\!=\/]/';
		$caption_for_comparison        = preg_replace( $pattern_clean_for_comparison, '', $caption );
		$figure_element_for_comparison = preg_replace( $pattern_clean_for_comparison, '', $figure_element );
		if ( false !== strpos( $figure_element_for_comparison, $caption_for_comparison . '</figcaption>' ) ) {
			return $block_element;
		}

		$figure_element_patched = substr_replace( $figure_element, $replace_figure, $pos_figure, strlen( $search_figure ) );

		// Now restore the patched <figure> element to the block.
		$patched_block_element = substr_replace( $block_element, $figure_element_patched, $figure_element_pos, strlen( $figure_element ) );

		return $patched_block_element;
	}

	/**
	 * Gets the inner text value of the element.
	 *
	 * @param string $html_element HTML element.
	 *
	 * @return string|false Inner text.
	 */
	private function get_caption_element_inner_text_value( $html_element ) {

		$caption_inner_text = $this->square_brackets_element_manipulator->get_inner_text( 'caption', $html_element );
		if ( ! $caption_inner_text ) {
			return false;
		}

		// Caption inner text may contain other elements. In found cases, caption text is at the far right, for example "capA":
		// [caption]<a href="/imgA2.jpg"><img src="/imgA.jpg"/></a>capA[/caption]
		// If other cases found, expand this to check for caption text in a better way.
		$last_occurence_of_right_angle_bracket = strrpos( $caption_inner_text, '>' );

		if ( false !== $last_occurence_of_right_angle_bracket ) {
			$caption_inner_text_value = substr( $caption_inner_text, $last_occurence_of_right_angle_bracket + 1 );
		} else {
			$caption_inner_text_value = $caption_inner_text;
		}

		return $caption_inner_text_value;
	}
}
