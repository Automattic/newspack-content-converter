<?php
/**
 * Patcher for the [pullquote][/pullquote] elements.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher\Patchers;

use NewspackContentConverter\ContentPatcher\Patchers\PatcherInterface;
use NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract;
use NewspackContentConverter\ContentPatcher\ElementManipulators\SquareBracketsElementManipulator;
use NewspackContentConverter\ContentPatcher\ElementManipulators\WpBlockManipulator;

/**
 * Patcher class for the [pullquote][/pullquote] elements.
 *
 * @package NewspackContentConverter\ContentPatcher\Patchers
 */
class ShortcodePullquotePatcher extends PatcherAbstract implements PatcherInterface {

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
	 * VideoPatcher constructor.
	 */
	public function __construct() {
		$this->square_brackets_element_manipulator = new SquareBracketsElementManipulator();
		$this->wp_block_manipulator                = new WpBlockManipulator();
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
		$matches_blocks = $this->wp_block_manipulator->match_wp_block( 'wp:shortcode', $source_blocks );
		if ( ! $matches_blocks ) {
			return $source_blocks;
		}

		foreach ( $matches_blocks[0] as $matched_block ) {
			$block = $matched_block[0];
			if ( false === strpos( $block, '[/pullquote]' ) ) {
				continue;
			}

			$converted_block = $this->convert_shortcode_block_to_pullquote( $block );
			$source_blocks   = str_replace( $block, $converted_block, $source_blocks );
		}

		return $source_blocks;
	}

	/**
	 * Convert a shortcode block with the pullquote shortcode into a pullquote block.
	 *
	 * @param string $block Raw block content.
	 * @return string New block content.
	 */
	protected function convert_shortcode_block_to_pullquote( $block ) {
		// Remove newlines because they confuse the matchers.
		$block = str_replace( "\n", '', $block );

		$shortcode_matches = $this->square_brackets_element_manipulator->match_elements_with_closing_tags( 'pullquote', $block );
		$shortcode         = $shortcode_matches[0][0][0];

		// Get content.
		$allowed_tags = array(
			'a' => array(
				'href' => array(),
			),
		);
		$content      = $this->square_brackets_element_manipulator->get_inner_text( 'pullquote', $shortcode );
		$content      = trim( wp_kses( $content, $allowed_tags ) );

		// Get citation.
		$cite   = '';
		$author = trim( $this->square_brackets_element_manipulator->get_attribute_value( 'author', $shortcode ) );
		if ( $author ) {
			$cite = '<cite>' . trim( wp_kses( $author, $allowed_tags ) ) . '</cite>';
		}

		return "<!-- wp:pullquote {\"align\":\"left\"} -->\n<figure class=\"wp-block-pullquote alignleft\"><blockquote><p>$content</p>$cite</blockquote></figure>\n<!-- /wp:pullquote -->";
	}
}
