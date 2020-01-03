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
 * Patcher class for the <img/> elements.
 *
 * @package NewspackContentConverter\ContentPatcher\Patchers
 */
class ImgPatcher extends PatcherAbstract implements PatcherInterface {

	/**
	 * Align values processed (supported) by this patcher.
	 *
	 * @var array
	 */
	private $supported_align_values = [
		'left',
		'right',
	];

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
	 * ImgPatcher constructor.
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

		// --- Looping over individual <img> elements
		$matches_html = $this->html_element_manipulator->match_elements_with_self_closing_tags( 'img', $source_html );
		if ( ! $matches_html ) {
			// TODO: DEBUG LOG 'no elements matched in HTML'.
			return $source_blocks;
		}

		$matches_blocks = $this->html_element_manipulator->match_elements_with_self_closing_tags( 'img', $source_blocks );
		// We expect to find the same number of <img> elements in HTML and blocks contents (this is a very basic check).
		if ( ! $this->validate_html_and_block_matches( $matches_html[0], $matches_blocks[0] ) ) {
			// TODO: DEBUG LOG 'HTML and block matches do not correspond'.
			return $source_blocks;
		}

		// Get full image blocks, with both position of start and end tag.
		$matched_image_blocks = $this->wp_block_manipulator->match_wp_block( 'wp:image', $source_blocks );
		if ( ! $matched_image_blocks ) {
			return $source_blocks;
		}


		// Only apply patches on image blocks. So for this, we need to detect which images have been converted to image blocks
		// (e.g. an <img> contained in a list will be converted into a list element).
		// To determine this:
		// - go through every <img> element in the block contents,
		// - check if it's surrounded by an image block,
		// - if it isn't, remove it from these matches (and from the HTML matches too, by key).
		foreach ( $matches_blocks[0] as $key_element => $match_blocks ) {
			$img_element     = $match_blocks[0];
			$img_element_pos = $match_blocks[1];

			// Go through all wp:image blocks, and if this <img> element is not inside one, eliminate it from matches.
			$img_is_inside_img_block = false;
			foreach ( $matched_image_blocks[0] as $key_blocks => $matched_image_block ) {
				$img_block             = $matched_image_blocks[0][ $key_blocks ][0];
				$img_block_start_pos   = $matched_image_blocks[0][ $key_blocks ][1];
				$img_block_end_tag     = $matched_image_blocks[1][ $key_blocks ][0];
				$img_block_end_tag_pos = $matched_image_blocks[1][ $key_blocks ][1];

				if ( ( $img_element_pos > $img_block_start_pos ) && ( $img_element_pos < $img_block_end_tag_pos ) ) {
					$img_is_inside_img_block = true;
					break;
				}
			}

			// Unset this <img> from matches, because we won't be patching it, since it's not in wp:image.
			if ( false === $img_is_inside_img_block ) {
				unset( $matches_blocks[0][ $key_element ] );
				unset( $matches_html[0][ $key_element ] );
			}
		}

		// TODO: check whether to change/update the validation above?
		// TODO: If empty matches, return?
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

			$patched_block_element = $this->html_element_manipulator->patch_attribute( $html_element, $blocks_element, 'height' );
			$patched_block_element = $this->html_element_manipulator->patch_attribute( $html_element, $patched_block_element, 'width' );

			$source_blocks = substr_replace( $source_blocks, $patched_block_element, $position_blocks_element, strlen( $blocks_element ) );
		}


		// --- Looping over the Image Blocks
		$matches_blocks = $this->wp_block_manipulator->match_wp_block( 'wp:image', $source_blocks );
		if ( ! $this->validate_html_and_block_matches( $matches_html[0], $matches_blocks[0] ) ) {
			// TODO: DEBUG LOG 'HTML and block matches do not correspond'.
			return $source_blocks;
		}

		// New blocks were matched, also need to be reversed to correspond to HTML matches.
		$matches_blocks[0] = array_reverse( $matches_blocks[0] );

		foreach ( $matches_html[0] as $key => $match_html ) {
			$html_element            = $match_html[0];
			$position_html_element   = $match_html[1];
			$blocks_element          = $matches_blocks[0][ $key ][0];
			$position_blocks_element = $matches_blocks[0][ $key ][1];

			$patched_block_element = $blocks_element;

			$patched_block_element = $this->patch_align_attribute( $html_element, $patched_block_element );
			$patched_block_element = $this->strip_double_align_class( $html_element, $patched_block_element );

			$img_height = $this->html_element_manipulator->get_attribute_value( 'height', $html_element );
			if ( false !== $img_height ) {
				$patched_block_element = $this->patch_block_attribute( $patched_block_element, 'height', (int) $img_height, false );
			}
			$img_width = $this->html_element_manipulator->get_attribute_value( 'width', $html_element );
			if ( false !== $img_width ) {
				$patched_block_element = $this->patch_block_attribute( $patched_block_element, 'width', (int) $img_width, false );
			}

			// If height or width were patched, also add 'is-resized' class to the figure element.
			if ( false !== $img_height || false !== $img_width ) {
				$figure_element_matches = $this->html_element_manipulator->match_elements_with_closing_tags( 'figure', $patched_block_element );
				if ( isset( $figure_element_matches[0][0][0] ) ) {
					$figure_element     = $figure_element_matches[0][0][0];
					$figure_element_pos = $figure_element_matches[0][0][1];

					$figure_element_patched = $this->html_element_manipulator->patch_class( $figure_element, 'is-resized' );
					$patched_block_element  = substr_replace( $patched_block_element, $figure_element_patched, $figure_element_pos, strlen( $figure_element ) );
				}
			}

			$source_blocks = substr_replace( $source_blocks, $patched_block_element, $position_blocks_element, strlen( $blocks_element ) );
		}

		return $source_blocks;
	}

	/**
	 * Adds the attribute to the block element (comments header definition). If the attribute already exists in the block, it will
	 * be replaced.
	 *
	 * @param string $block_element Block element, accepts a multiline string.
	 * @param string $attribute_name The attribute value.
	 * @param string $attribute_value The attribute value to be used. Thi value is type sensitive value -- an int or a float will
	 *                                not be surrounded by quotes, strings will be.
	 *
	 * @return string
	 */
	private function patch_block_attribute( $block_element, $attribute_name, $attribute_value ) {

		// Get block 1st line.
		$blocks_lines   = explode( "\n", $block_element );
		$block_1st_line = $blocks_lines[0];

		// get already existing attributes.
		$pos_attribute_curly_open  = strpos( $block_1st_line, '{' );
		$pos_attribute_curly_close = strrpos( $block_1st_line, '}' );
		if ( false !== $pos_attribute_curly_open && false !== $pos_attribute_curly_close ) {
			$length_existing_attributes = $pos_attribute_curly_close + 1 - $pos_attribute_curly_open;
			$existing_attributes        = substr( $block_1st_line, $pos_attribute_curly_open, $length_existing_attributes );
		}
		$attributes = isset( $existing_attributes ) ? $existing_attributes : '{}';

		// patch (replace) attribute value.
		$attributes_json                    = json_decode( $attributes, true );
		$attributes_json[ $attribute_name ] = $attribute_value;
		$attributes_patched                 = wp_json_encode( $attributes_json, JSON_NUMERIC_CHECK );

		// put back patched attributes.
		if ( isset( $existing_attributes ) ) {
			$block_1st_line_patched = substr_replace( $block_1st_line, $attributes_patched, $pos_attribute_curly_open, $length_existing_attributes );
		} else {
			$closing_tag            = '-->';
			$pos_closing_tag        = strpos( $block_1st_line, $closing_tag );
			$block_1st_line_patched = substr_replace( $block_1st_line, $attributes_patched . ' ' . $closing_tag, $pos_closing_tag, strlen( $closing_tag ) );
		}

		// Put back patched first line and return block element.
		$blocks_lines[0] = $block_1st_line_patched;
		$block_element   = implode( "\n", $blocks_lines );

		return $block_element;
	}

	/**
	 * The <img/> element supports only a specific number of 'align' attribute values:
	 *      left, right, middle, top, bottom,
	 * while the Block Editor supports only some of those:
	 *      left, right, center, wide, full.
	 * So, we're making sure that the 'left' and the 'right' image aligns get patched correctly.
	 *
	 * The align is supposed to be applied both to the block comment tag's "align" attribute, and to the child <figure>'s class
	 * ("alignright" or "alignleft")
	 *
	 * Eg. source HTML:
	 *
	 *      <img align="right" src="/img.jpg">
	 *
	 * Converted by Gutenberg (Classic block -> convert to blocks), align is lost:
	 *
	 *      <!-- wp:image -->
	 *      <figure class="wp-block-image"><img src="http://chicagoreporter.com/wp-content/uploads/archive/Graphic_Damage-over-time-300w.jpg" alt=""/></figure>
	 *      <!-- /wp:image -->
	 *
	 * Patched by this patcher:
	 *
	 *      <!-- wp:image {"align":"right"} -->
	 *      <figure class="wp-block-image alignright"><img src="http://chicagoreporter.com/wp-content/uploads/archive/Graphic_Damage-over-time-300w.jpg" alt=""/></figure>
	 *      <!-- /wp:image -->
	 *
	 * Necessary to note that this form of an image block is also considered correct by the Block Editor (important when checking
	 * whether the block already has the align attribute set):
	 *
	 *      <!-- wp:image {"align":"right"} -->
	 *      <div class="wp-block-image"><figure class="alignright"><img src="http://chicagoreporter.com/wp-content/uploads/archive/Graphic_Damage-over-time-300w.jpg"/></figure></div>
	 *      <!-- /wp:image -->
	 *
	 * @param string $html_element HTML element.
	 * @param string $block_element Block element.
	 *
	 * @return string Updated block element.
	 */
	private function patch_align_attribute( $html_element, $block_element ) {

		// Check that align is not already contained in tag comments (first line).
		$blocks_lines = explode( "\n", $block_element );
		if ( false !== strpos( $blocks_lines[0], '"align":"' ) ) {
			// TODO: DEBUG LOG 'align attribute already contained in image block comments'.
			return $block_element;
		}

		// Check that "alignright"/"alignleft" class not already present as <figure>'s class attribute.
		$figure_element_matches = $this->html_element_manipulator->match_elements_with_closing_tags( 'figure', $block_element );
		if ( ! isset( $figure_element_matches[0][0][0] ) ) {
			// TODO: DEBUG LOG 'figure element not found in block_element while searching for align class'.
			return $block_element;
		}

		$figure_element     = $figure_element_matches[0][0][0];
		$figure_element_pos = $figure_element_matches[0][0][1];

		// Get <img/> element.
		$img_element_matches = $this->html_element_manipulator->match_elements_with_self_closing_tags( 'img', $html_element );
		if ( ! isset( $img_element_matches[0][0][0] ) ) {
			// TODO: DEBUG LOG 'img element not found in HTML while searching for align class'.
			return $block_element;
		}

		$img_element     = $img_element_matches[0][0][0];
		$attribute_value = $this->html_element_manipulator->get_attribute_value( 'align', $img_element );

		// Check if align value is supported.
		if ( ( false !== $attribute_value ) && ( false === in_array( $attribute_value, $this->supported_align_values ) ) ) {
			// TODO: DEBUG LOG 'image align value unsupported'.
			return $block_element;
		}

		foreach ( $this->supported_align_values as $supported_align ) {
			if ( false !== strpos( 'align' . $supported_align, $figure_element ) ) {
				// TODO: DEBUG LOG 'a supported align value already found as in figure element's align attribute'.
				return $block_element;
			}
		}

		// Get actual align value.
		$align_value        = $this->html_element_manipulator->get_attribute_value( 'align', $html_element );
		$figure_class_value = $this->html_element_manipulator->get_attribute_value( 'class', $figure_element );
		if ( false === $align_value ) {
			// TODO: DEBUG LOG 'no align value found on HTML image'.
			return $block_element;
		}

		// Check that <figure> class attribute doesn't already have one of the supported aligns.
		$figure_classes = explode( ' ', $figure_class_value );
		foreach ( $this->supported_align_values as $supported_align ) {
			if ( in_array( 'align' . $supported_align, $figure_classes ) ) {
				// TODO: DEBUG LOG 'a supported align value already found as in figure element's class attribute'.
				return $block_element;
			}
		}

		// Add a new <figure> class attribute, or append value to existing class attribute.
		$figure_class_patched  = $figure_class_value ? $figure_class_value . ' ' : '';
		$figure_class_patched .= 'align' . $align_value;
		if ( false === $figure_class_value ) {
			$figure_element_patched = $this->html_element_manipulator->add_attribute( $figure_element, 'class', $figure_class_patched );
		} else {
			$figure_element_patched = $this->html_element_manipulator->replace_attribute_value( $figure_element, 'class', $figure_class_patched );
		}
		$block_element = substr_replace( $block_element, $figure_element_patched, $figure_element_pos, strlen( $figure_element ) );

		// Update block comments tag's attribute.
		$block_element_patched = $this->wp_block_manipulator->add_attribute( $block_element, 'align', $align_value );

		return $block_element_patched;
	}

	/**
	 * Gutenberg can sometimes place two align classes into block body, both in <div> and <figure> so this removes the one of the
	 * two duplicates, the one from the <div>.
	 *
	 * @param string $html_element HTML element.
	 * @param string $block_element Block element.
	 *
	 * @return string Updated Block element.
	 */
	private function strip_double_align_class( $html_element, $block_element ) {

		// Check that "alignright"/"alignleft" class not already present as <figure>'s class attribute.
		$figure_element_matches = $this->html_element_manipulator->match_elements_with_closing_tags( 'figure', $block_element );
		if ( ! isset( $figure_element_matches[0][0][0] ) ) {
			// TODO: LOG 'figure element not found in block_element while searching for align class'.
			return $block_element;
		}

		$div_element_matches = $this->html_element_manipulator->match_elements_with_closing_tags( 'div', $block_element );
		if ( ! isset( $div_element_matches[0][0][0] ) ) {
			// TODO: LOG 'div element not found in block_element while searching for align class'.
			return $block_element;
		}

		$img_element_matches = $this->html_element_manipulator->match_elements_with_self_closing_tags( 'img', $html_element );
		if ( ! isset( $img_element_matches[0][0][0] ) ) {
			// TODO: LOG 'img element not found in HTML while searching for align class'.
			return $block_element;
		}

		$figure_element     = $figure_element_matches[0][0][0];
		$figure_element_pos = $figure_element_matches[0][0][1];
		$div_element        = $div_element_matches[0][0][0];
		$div_element_pos    = $div_element_matches[0][0][1];
		$img_element        = $img_element_matches[0][0][0];
		$img_element_pos    = $img_element_matches[0][0][1];

		// Get classes for div and figure.
		$div_class_value    = $this->html_element_manipulator->get_attribute_value( 'class', $div_element );
		$div_classes        = explode( ' ', $div_class_value );
		$figure_class_value = $this->html_element_manipulator->get_attribute_value( 'class', $figure_element );
		$figure_classes     = explode( ' ', $figure_class_value );

		// Get actual align value, first try from the html_element, then try from <figure> class.
		$align_value = $this->html_element_manipulator->get_attribute_value( 'align', $html_element );
		if ( false === $align_value ) {
			// If align attribute not found on the img element, also attempt to read it from the <figure> class.
			foreach ( $this->supported_align_values as $supported_align ) {
				$supported_align_class_value = 'align' . $supported_align;
				$key                         = array_search( $supported_align_class_value, $figure_classes );
				if ( false !== $key ) {
					$align_value = $supported_align;
					break;
				}
			}
		}

		// TODO: DEBUG LOG 'Align class not found in figure.'.
		if ( false === $align_value ) {
			return $block_element;
		}

		$align_classname = 'align' . $align_value;

		// Check that <figure> class attribute doesn't already have one of the supported aligns.
		$has_figure_align = in_array( $align_classname, $figure_classes );
		$has_div_align    = in_array( $align_classname, $div_classes );

		// Check if both figure and div have the align classes.
		if ( ! $has_div_align && ! $has_figure_align ) {
			// TODO: DEBUG LOG 'figure and div do not both have the align classes'.
			return $block_element;
		}

		// Remove align class from the div.
		$key                 = array_search( $align_classname, $div_classes );
		$div_classes_patched = $div_classes;
		if ( false !== $key ) {
			unset( $div_classes_patched[ $key ] );
		}
		$div_classes_patched_value = implode( ' ', $div_classes_patched );

		$div_element_patched   = $this->html_element_manipulator->replace_attribute_value( $div_element, 'class', $div_classes_patched_value );
		$block_element_patched = substr_replace( $block_element, $div_element_patched, $div_element_pos, strlen( $div_element ) );

		return $block_element_patched;
	}
}
