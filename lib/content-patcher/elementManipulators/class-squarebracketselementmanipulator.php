<?php
/**
 * Manipulator for square brackets elements.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher\ElementManipulators;

/**
 * SquareBracketsElementManipulator.
 *
 * @package NewspackContentConverter\ContentPatcher\ElementManipulators
 */
class SquareBracketsElementManipulator {

	/**
	 * Matches the square brackets element.
	 */
	const PATTERN_SQUARE_BRACKETS_ELEMENT = '|
		\[          # beginning of the element with square brackets (literal "[" char is "\[" in regex pattern)
		%1$s        # element name/designation, should be substituted by using sprintf(), eg. sprintf( $this_pattern, \'video\' );
		.*?         # anything in the middle
		\]          # closing this part
		.*?         # anything in the middle
		\[/         # beginning of the closing tag
		%1$s        # element name/designation
		\]          # end of element
		|xim';

	/**
	 * Matches the square brackets element inner text.
	 */
	const PATTERN_SQUARE_BRACKETS_ELEMENT_INNER_TEXT = '|
		\[          # beginning of the element with square brackets (literal "[" char is "\[" in regex pattern)
		%1$s        # then locate the attribute name, should be substituted by using sprintf(), eg. sprintf( $this_pattern, \'caption\' );
		.*?         # possibly anything (attributes and values)
		\s*         # followed by zero or more spaces
		\]          # the closing bracket
		(.*?)       # inner text -- value being captured
		\[/         # beginning of the closing tag
		%1$s        # element name/designation
		\]          # end of element
		|xim';

	/**
	 * Matches the attribute value, and creates a group for that value.
	 */
	const PATTERN_ELEMENT_ATTRIBUTE_VALUE = '/
	    .*?             # anything at the beginning of the string
		%s              # then locate the attribute name, should be substituted by using sprintf(), eg. sprintf( $this_pattern, \'id\' );
		\s*             # followed by zero or more spaces
		=               # the equals char
		\s*             # once again, with possible zero or more spaces
		["\']           # the attribute value starts with one double or a single quote
		([^"\']+)       # capture (using parenthesis) the value -- one or more chars except (and up to) double or single quote
                        # (we don\'t care about the rest of the string, since we\'ve captured the attribute by now)
		/xim';

	/**
	 * Matches a square brackets element with closing tags. For example, the "[caption]...[/caption]" element has closing tags,
	 * which would be the ending "[/caption]" part of the string.
	 *
	 * @param string $element_name Name of the square bracket element, e.g. "caption", for the [caption]...[/caption] element.
	 * @param string $subject      Source in which to search for matches.
	 *
	 * @return array|null preg_match_all's $matches, or null.
	 */
	public function match_elements_with_closing_tags( $element_name, $subject ) {
		$pattern               = sprintf( self::PATTERN_SQUARE_BRACKETS_ELEMENT, $element_name );
		$preg_match_all_result = preg_match_all( $pattern, $subject, $matches, PREG_OFFSET_CAPTURE );

		return ( false === $preg_match_all_result || 0 === $preg_match_all_result ) ? null : $matches;
	}

	/**
	 * Gets the element's inner text.
	 *
	 * @param string $element_name Name of the square bracket element, e.g. "caption", for the [caption]...[/caption] element.
	 * @param string $subject      Source in which to search for matches.
	 *
	 * @return string|null Inner text, or null.
	 */
	public function get_inner_text( $element_name, $subject ) {
		$inner_text_matches = $this->match_inner_text( $element_name, $subject );

		return isset( $inner_text_matches[1][0][0] ) ? $inner_text_matches[1][0][0] : null;
	}

	/**
	 * Gets the element's attribute value.
	 *
	 * @param string $attribute_name The attribute name.
	 * @param string $element        The element.
	 *
	 * @return string|false Attribute value, or false.
	 */
	public function get_attribute_value( $attribute_name, $element ) {
		$match = $this->get_element_square_brackets_attribute_value_preg_match( $attribute_name, $element );

		return false !== $match ? $match[0] : false;
	}

	/**
	 * Matches the inner text of a square brackets element.
	 * Runs the preg_match_all() with the PREG_OFFSET_CAPTURE option, and returns the $match if found.
	 *
	 * @param string $element_name Name of the square bracket element, e.g. "caption", for the [caption]...[/caption] element.
	 * @param string $subject      Source in which to search for matches.
	 *
	 * @return array|null preg_match_all's $match, or null.
	 */
	private function match_inner_text( $element_name, $subject ) {
		$pattern               = sprintf( self::PATTERN_SQUARE_BRACKETS_ELEMENT_INNER_TEXT, $element_name );
		$preg_match_all_result = preg_match_all( $pattern, $subject, $matches, PREG_OFFSET_CAPTURE );

		return ( false === $preg_match_all_result || 0 === $preg_match_all_result ) ? null : $matches;
	}

	/**
	 * Matches the attribute value, and returns that portion of the match.
	 * Runs the preg_match() with the PREG_OFFSET_CAPTURE option, and returns the $match if found.
	 *
	 * @param string $attribute_name Attribute name.
	 * @param string $html_element   Attribute element.
	 *
	 * @return array|false preg_match's $match, or false.
	 */
	private function get_element_square_brackets_attribute_value_preg_match( $attribute_name, $html_element ) {
		$pattern = sprintf( self::PATTERN_ELEMENT_ATTRIBUTE_VALUE, $attribute_name );
		$res     = preg_match( $pattern, $html_element, $match, PREG_OFFSET_CAPTURE );
		if ( 1 === $res ) {
			// Check that the result is within bounds of element's tags, not within it's inner html.
			$pos_attribute_value            = $match[1][1];
			$pos_1st_closing_square_bracket = strpos( $html_element, ']' );
			if ( $pos_attribute_value < $pos_1st_closing_square_bracket ) {
				return $match[1];
			}
		}

		return false;
	}
}
