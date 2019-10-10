<?php
/**
 * Patcher for the image elements.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher\Patchers;

use NewspackContentConverter\ContentPatcher\Patchers\PatcherInterface;
use NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract;
use NewspackContentConverter\ContentPatcher\ElementManipulators\HtmlElementManipulator;
use NewspackContentConverter\ContentPatcher\ElementManipulators\WpBlockManipulator;

/**
 * Patcher class for the <p></p> elements.
 *
 * @package NewspackContentConverter\ContentPatcher\Patchers
 */
class ParagraphPatcher extends PatcherAbstract implements PatcherInterface {

	/**
	 * HtmlElementManipulator service.
	 *
	 * @var HtmlElementManipulator
	 */
	private $html_element_manipulator;

	/**
	 * WpBlockManipulator service.
	 *
	 * @var WpBlockManipulator
	 */
	private $wp_block_manipulator;

	/**
	 * ParagraphPatcher constructor.
	 */
	public function __construct() {
		$this->html_element_manipulator = new HtmlElementManipulator();
		$this->wp_block_manipulator     = new WpBlockManipulator();
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

		$matches_html = $this->html_element_manipulator->match_elements_with_closing_tags( 'p', $source_html );
		if ( ! $matches_html ) {
			// TODO: DEBUG LOG 'no elements matched in HTML'.
			return $source_blocks;
		}

		$matches_blocks = $this->html_element_manipulator->match_elements_with_closing_tags( 'p', $source_blocks );
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

			$patched_block_element = $this->html_element_manipulator->patch_attribute( $html_element, $blocks_element, 'dir' );

			$source_blocks = substr_replace( $source_blocks, $patched_block_element, $position_blocks_element, strlen( $blocks_element ) );
		}

		return $source_blocks;
	}
}
