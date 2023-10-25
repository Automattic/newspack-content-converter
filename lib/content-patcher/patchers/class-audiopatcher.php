<?php
/**
 * Patcher for the [audio][/audio] elements.
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
 * Patcher class for the [audio][/audio] elements.
 *
 * @package NewspackContentConverter\ContentPatcher\Patchers
 */
class AudioPatcher extends PatcherAbstract implements PatcherInterface {

	/**
	 * HtmlElementManipulator service.
	 *
	 * @var HtmlElementManipulator
	 */
	private $html_element_manipulator;

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
	 * AudioPatcher constructor.
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

		$matches_html = $this->square_brackets_element_manipulator->match_elements_with_closing_tags( 'audio', $source_html );
		if ( ! $matches_html ) {
			// TODO: DEBUG LOG 'no elements matched in HTML'.
			return $source_blocks;
		}

		$matches_blocks = $this->wp_block_manipulator->match_wp_block( 'wp:audio', $source_blocks );
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

			$patched_block_element = $this->patch_audio_src_attribute( $html_element, $blocks_element );
			if ( $patched_block_element ) {
				$source_blocks = substr_replace( $source_blocks, $patched_block_element, $position_blocks_element, strlen( $blocks_element ) );
			}
		}

		return $source_blocks;
	}

	/**
	 * Patches the audio src attribute, by searching for it in the HTML element, then applying it to the block element.
	 *
	 * @param string $html_element HTML element.
	 * @param string $block_element Block element.
	 *
	 * @return string|false Updated block element, or false.
	 */
	private function patch_audio_src_attribute( $html_element, $block_element ) {

		// Extract the specific src attribute from HTML [audio][/audio] element.
		// Different possible names of the src attributes: https://en.support.wordpress.com/accepted-filetypes/#audio .
		$possible_src_attributes = [ 'mp3', 'm4a', 'ogg', 'wav' ];

		foreach ( $possible_src_attributes as $attribute_name ) {
			$attribute_value = $this->square_brackets_element_manipulator->get_attribute_value( $attribute_name, $html_element );
			if ( $attribute_value ) {
				break;
			}
		}

		if ( ! $attribute_value ) {
			// TODO: DEBUG LOG 'no src audio matched in HTML'.
			return false;
		}

		// The found src is to be patched as a new <audio> element inside the <figure> element, like this:
		// - before patching: <figure class="wp-block-audio"></figure>
		// - after patching:  <figure class="wp-block-audio"><audio controls src="/audio/path"></audio></figure>
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
		$search_figure      = '>';
		$replace_figure     = sprintf( '><audio controls src="%s"></audio>', $attribute_value );
		$pos_figure         = strpos( $figure_element, $search_figure );

		// Check if <figure> already contains the src.
		if ( false !== strpos( $figure_element, 'src="' . $attribute_value . '"' ) ) {
			return $block_element;
		}

		$figure_element_patched = substr_replace( $figure_element, $replace_figure, $pos_figure, strlen( $search_figure ) );

		// Now restore the patched <figure> element to the block.
		$patched_block_element = substr_replace( $block_element, $figure_element_patched, $figure_element_pos, strlen( $figure_element ) );

		return $patched_block_element;
	}
}
