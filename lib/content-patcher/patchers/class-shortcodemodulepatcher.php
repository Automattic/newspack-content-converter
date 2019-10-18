<?php
/**
 * Patcher for the [module][/module] elements.
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
 * Patcher class for the [module][/module] elements.
 *
 * @package NewspackContentConverter\ContentPatcher\Patchers
 */
class ShortcodeModulePatcher extends PatcherAbstract implements PatcherInterface {

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
	 * VideoPatcher constructor.
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
		$matches_blocks = $this->wp_block_manipulator->match_wp_block( 'wp:shortcode', $source_blocks );
		if ( ! $matches_blocks ) {
			return $source_blocks;
		}

		foreach ( $matches_blocks[0] as $matched_block ) {
			$block = $matched_block[0];
			if ( false === strpos( $block, '[/module]' ) ) {
				continue;
			}

			$converted_block = $this->convert_shortcode_block_to_pullquote( $block );
			$source_blocks   = str_replace( $block, $converted_block, $source_blocks );
		}

		return $source_blocks;
	}

	/**
	 * Convert a shortcode block with the Lorgo theme's module shortcode into a pullquote block.
	 *
	 * @param string $block Raw block content.
	 * @return string New block content.
	 */
	protected function convert_shortcode_block_to_pullquote( $block ) {
		// Strip any fancy quotes that may be breaking shortcode attributes.
		// @see https://github.com/Automattic/newspack-content-converter/issues/11.
		$block = str_replace( '”', '"', $block );

		// Determine alignment.
		preg_match( '#\[module[^\]]*\]#', $block, $attributes_matches );
		$shortcode_atts = shortcode_parse_atts( $attributes_matches[0] );
		$alignment      = ( ! empty( $shortcode_atts['align'] ) && ( 'left' === $shortcode_atts['align'] || 'right' === $shortcode_atts['align'] ) ) ? sanitize_title( $shortcode_atts['align'] ) : '';

		// Determine content.
		preg_match( '#\[module[^\]]*\](.*)\[\/module\]#s', $block, $content_matches );
		$content = $content_matches[1];

		// Sanitize content.
		$allowed_tags = array(
			'a' => array(
				'href' => array(),
			),
		);
		$content      = trim( wp_kses( $content, $allowed_tags ) );

		$alignment_object = $alignment ? '{"align":"' . $alignment . '"} ' : '';
		$class            = 'wp-block-pullquote';
		if ( $alignment ) {
			$class .= ' align' . $alignment;
		}
		return '<!-- wp:pullquote ' . $alignment_object . "-->\n<figure class=\"" . esc_attr( $class ) . "\"><blockquote><p>$content</p></blockquote></figure>\n<!-- /wp:pullquote -->";
	}
}
