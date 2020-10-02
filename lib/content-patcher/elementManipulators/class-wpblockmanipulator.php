<?php
/**
 * Manipulator for Gutenber Block elements.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher\ElementManipulators;

/**
 * WpBlockManipulator.
 *
 * @package NewspackContentConverter\ContentPatcher\ElementManipulators
 */
class WpBlockManipulator {

	/**
	 * Matches a block element with both opening and closing tags. It creates one group -- around the closing block comment tags.
	 */
	const PATTERN_WP_BLOCK_ELEMENT = '|
		\<\!--      # beginning of the block element
		\s          # followed by a space
		%1$s        # element name/designation, should be substituted by using sprintf(), eg. sprintf( $this_pattern, \'wp:video\' );
		.*?         # anything in the middle
		--\>        # end of opening tag
		.*?         # anything in the middle
		(\<\!--     # beginning of the closing tag
		\s          # followed by a space
		/           # one forward slash
		%1$s        # element name/designation, should be substituted by using sprintf(), eg. sprintf( $this_pattern, \'wp:video\' );
		\s          # followed by a space
		--\>)       # end of block
				    # "s" modifier also needed here to match accross multi-lines
		|xims';

	/**
	 * Matches a self-closing block element -- which is one that does NOT have both an opening tag `<!-- wp:__ -->` and a closing
	 * tag `<!-- /wp:__ -->`, but rather has just one "self-closing tag", e.g. `<!-- wp:__ /-->`.
	 */
	const PATTERN_WP_BLOCK_ELEMENT_SELFCLOSING = '|
		\<\!--        # beginning of the block element
		\s            # followed by a space
		%s            # element name/designation, should be substituted by using sprintf()
		.*?           # anything in the middle
		\/--\>        # ends with a self-closing tag
		|xims';

	/**
	 * Searches and matches block elements in given source.
	 * Runs the preg_match_all() with the PREG_OFFSET_CAPTURE option, and returns the $match.
	 *
	 * @param string $block_name Block name to search for (match).
	 * @param string $subject Blocks content source in which to search for blocks.
	 *
	 * @return array|null| $matches from the preg_match_all() or null.
	 */
	public function match_wp_block( $block_name, $subject ) {

		$pattern = sprintf( self::PATTERN_WP_BLOCK_ELEMENT, $block_name );

		$preg_match_all_result = preg_match_all( $pattern, $subject, $matches, PREG_OFFSET_CAPTURE );
		return ( false === $preg_match_all_result || 0 === $preg_match_all_result ) ? null : $matches;
	}

	/**
	 * Searches and matches blocks in given source.
	 *
	 * Uses preg_match_all() with the PREG_OFFSET_CAPTURE option, and returns its $match.
	 *
	 * @param string $block_name Block name/designation to search for.
	 * @param string $subject    The Block source in which to search for the block occurences.
	 *
	 * @return array|null The `$matches` array as set by preg_match_all() with the PREG_OFFSET_CAPTURE option, or null if no matches found.
	 */
	public function match_wp_block_selfclosing( $block_name, $subject ) {

		$pattern = sprintf( self::PATTERN_WP_BLOCK_ELEMENT_SELFCLOSING, $block_name );
		$preg_match_all_result = preg_match_all( $pattern, $subject, $matches, PREG_OFFSET_CAPTURE );

		return ( false === $preg_match_all_result || 0 === $preg_match_all_result ) ? null : $matches;
	}

	/**
	 * Adds an attribute to the block element's header.
	 * It doesn't check whethet the attribute already exists, simply appends it to the block definition.
	 *
	 * @param string $block_element The block element, accepts a multiline string.
	 * @param string $attribute_name Attribute name.
	 * @param string $attribute_value Attribute value.
	 *
	 * @return string Updated block element.
	 */
	public function add_attribute( $block_element, $attribute_name, $attribute_value ) {
		$block_element_lines    = explode( "\n", $block_element );
		$block_element_1st_line = $block_element_lines[0];

		$pos_close_curly = strrpos( $block_element_1st_line, '}' );
		if ( false !== $pos_close_curly ) {
			// If some attributes already exist, append to those.
			$block_element_1st_line_patched = substr_replace( $block_element_1st_line, ',"' . $attribute_name . '":"' . $attribute_value . '"}', $pos_close_curly, $length = 1 );
		} else {
			// Otherwise, add the curly brackets first.
			$pos_close_comment              = strrpos( $block_element_1st_line, '-->' );
			$block_element_1st_line_patched = substr_replace( $block_element_1st_line, '{"' . $attribute_name . '":"' . $attribute_value . '"} -->', $pos_close_comment, $length = 3 );
		}

		$block_element_lines[0] = $block_element_1st_line_patched;
		$block_element_patched  = implode( "\n", $block_element_lines );

		return $block_element_patched;
	}
}
