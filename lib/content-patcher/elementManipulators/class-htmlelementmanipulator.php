<?php
/**
 * Base manipulator for HTML elements.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher\ElementManipulators;

/**
 * HtmlElementManipulator.
 *
 * @package NewspackContentConverter\ContentPatcher\ElementManipulators
 */
class HtmlElementManipulator {

	/**
	 * Regex pattern to match all occurences of an HTML element which uses a closing tag.
	 * The element name/designation needs to be substituted by sprintf().
	 *
	 * For example, can match all paragraph elements:
	 *      <p id="someId">This can be matched by this regex</p>
	 */
	const PATTERN_HTML_ELEMENT = '|
		<           # beginning of the HTML element
		%1$s        # element name/designation, should be substituted by using sprintf(), eg. sprintf( $this_pattern, \'img\' );
		.*?         # anything in the middle
		>           # closing this part
		.*?         # anything in the middle
		</          # beginning of the closing tag
		%1$s        # element name/designation
		>           # end of element
		|xims';

	/**
	 * Regex pattern to match all occurences of a self closing HTML element.
	 * The element name/designation needs to be substituted by sprintf().
	 *
	 * For example, can match all image elements:
	 *      <img src="/location" id="imgId"/>
	 *
	 * Used for "self closing elements".
	 * "Self closing elements" are those that don't have specific closing tags. For example, <p> uses a closing tag </p>:
	 *      <p>...</p>
	 * but <img> doesn't, and it is a "self closing element":
	 *      <img ... />
	 */
	const PATTERN_HTML_ELEMENT_SELF_CLOSING = '/
		<           # beginning of the HTML element
		%s          # element name, should be substituted by using sprintf(), eg. sprintf( $this_pattern, \'img\' );
		.*?         # anything in the middle
		>           # > is the end of the element
		/xims';

	/**
	 * Regex pattern to match the attribute in a HTML element. It matches two groups: the attribute name, and the value.
	 * The element name/designation needs to be substituted by sprintf().
	 */
	const PATTERN_HTML_ELEMENT_ATTRIBUTE_WITH_VALUE = '/
		.*?             # Subject is HTML element, so beginning is not important
		(%s)            # find and group the attribute name
		\s*             # next zero or more spaces
		=               # the equals char
		\s*             # once again, with possible zero or more spaces
		["\']           # the attribute value starts with one double or a single quote
		([^"\']+)       # capture (using parenthesis) the attr value, one or more chars except (and up to) double or single quote
		.*?             # we captured all we were looking for, take the rest of the element.

		/xims';

	/**
	 * Match elements with closing tags.
	 * For example, for the <p> element, a closing tag is </p>, as in:
	 *      <p>...</p>
	 * but the <img> doesn't use a closing tag.:
	 *      <img ... />
	 *
	 * @param string $element_name HTML designation, eg. 'p', or 'img'.
	 * @param string $subject      HTML.
	 *
	 * @return array|null preg_match_all with PREG_OFFSET_CAPTURE matches, or null if no matches.
	 */
	public function match_elements_with_closing_tags( $element_name, $subject ) {
		$pattern               = sprintf( self::PATTERN_HTML_ELEMENT, $element_name );
		$preg_match_all_result = preg_match_all( $pattern, $subject, $matches, PREG_OFFSET_CAPTURE );

		return ( false === $preg_match_all_result || 0 === $preg_match_all_result ) ? null : $matches;
	}

	/**
	 * Match elements with self closing tags.
	 * For example, the <img> is an element with self closing tag:
	 *      <img ... />
	 * while the <p> element uses a separate closing tag is </p>, as in:
	 *      <p>...</p>
	 *
	 * @param string $element_name HTML designation, eg. 'p', or 'img'.
	 * @param string $subject      HTML.
	 *
	 * @return array|null preg_match_all with PREG_OFFSET_CAPTURE matches, or null if no matches.
	 */
	public function match_elements_with_self_closing_tags( $element_name, $subject ) {
		$pattern               = sprintf( self::PATTERN_HTML_ELEMENT_SELF_CLOSING, $element_name );
		$preg_match_all_result = preg_match_all( $pattern, $subject, $matches, PREG_OFFSET_CAPTURE );

		return ( false === $preg_match_all_result || 0 === $preg_match_all_result ) ? null : $matches;
	}

	/**
	 * Gets HTML element's attribute value.
	 *
	 * @param string $attribute_name The attribute name.
	 * @param string $html_element The HTML element.
	 *
	 * @return bool|mixed
	 */
	public function get_attribute_value( $attribute_name, $html_element ) {
		$match = $this->get_attribute_with_value_preg_match( $attribute_name, $html_element );
		$match = false !== $match ? $match[2] : false;

		return false !== $match ? $match[0] : false;
	}

	/**
	 * Gets the position of HTML element's attribute value.
	 *
	 * @param string $attribute_name The attribute name.
	 * @param string $html_element The HTML element.
	 *
	 * @return bool|mixed
	 */
	public function get_attribute_value_position( $attribute_name, $html_element ) {
		$match = $this->get_attribute_with_value_preg_match( $attribute_name, $html_element );
		$match = false !== $match ? $match[2] : false;

		return false !== $match ? $match[1] : false;
	}

	/**
	 * Runs a search for the attribute on the HTML element. Runs the preg_match() with PREG_OFFSET_CAPTURE option, and matches
	 * two regex groups: attribute name, attribute value.
	 * Returns the $match, or false if no match found.
	 *
	 * @param string $attribute_name The attribute name.
	 * @param string $html_element HTML element.
	 *
	 * @return array|bool preg_match() with PREG_OFFSET_CAPTURE result, or false.
	 */
	private function get_attribute_with_value_preg_match( $attribute_name, $html_element ) {
		$pattern = sprintf( self::PATTERN_HTML_ELEMENT_ATTRIBUTE_WITH_VALUE, $attribute_name );
		$res     = preg_match( $pattern, $html_element, $match, PREG_OFFSET_CAPTURE );
		if ( 1 !== $res ) {
			return false;
		}

		// Check that the result is matched within bounds of element's tags, not within it's inner text (inner text could contain another element with its own attribute and value, we don't want to match that).
		$pos_attribute_value           = $match[2][1];
		$pos_1st_closing_angle_bracket = strpos( $html_element, '>' );
		if ( $pos_attribute_value < $pos_1st_closing_angle_bracket ) {
			return $match;
		}

		return false;
	}

	/**
	 * Replaces the element's attribute value.
	 * If attribute not found, returns element unchanged.
	 *
	 * @param string $element HTML element.
	 * @param string $attribute_name Attribute name.
	 * @param string $attribute_value_new Attribute value.
	 *
	 * @return string Updated element.
	 */
	public function replace_attribute_value( $element, $attribute_name, $attribute_value_new ) {

		$attribute_value = $this->get_attribute_value( $attribute_name, $element );
		if ( false === $attribute_value ) {
			// TODO: DEBUG LOG 'attribute not found in element'.
			return $element;
		}

		$attribute_value_pos = $this->get_attribute_value_position( $attribute_name, $element );
		if ( false === $attribute_value_pos ) {
			// TODO: DEBUG LOG 'attribute not found in element'.
			return $element;
		}

		return substr_replace( $element, $attribute_value_new, $attribute_value_pos, strlen( $attribute_value ) );
	}

	/**
	 * Adds an attribute to the HTML element. Warning -- it doesn't search if attribute already exists.
	 *
	 * @param string $element The HTML element.
	 * @param string $attribute_name The attribute name.
	 * @param string $attribute_value The attribute value.
	 *
	 * @return mixed
	 */
	public function add_attribute( $element, $attribute_name, $attribute_value ) {

		// Element tag could end with either '>' or '/>'.
		$search_1 = '>';
		$pos_1    = strpos( $element, $search_1 );
		$search_2 = '/>';
		$pos_2    = strpos( $element, $search_2 );
		if ( false !== $pos_2 && $pos_2 <= $pos_1 ) {
			$search = $search_2;
			$pos    = $pos_2;
		} else {
			$search = $search_1;
			$pos    = $pos_1;
		}

		$replace = ' ' . $attribute_name . '="' . $attribute_value . '"' . $search;

		return substr_replace( $element, $replace, $pos, strlen( $search ) );
	}

	/**
	 * The method reads the attribute value from the source element, and patches its value to the destination element.
	 *
	 * @param string $element_source Source element from which the attribute is read.
	 * @param string $element_destination Destination element, to which the patch is applied.
	 * @param string $attribute_name The attribute name.
	 *
	 * @return string mixed Patched HTML element.
	 */
	public function patch_attribute( $element_source, $element_destination, $attribute_name ) {

		$attribute_value = $this->get_attribute_value( $attribute_name, $element_source );
		if ( ! $attribute_value ) {
			// TODO: DEBUG LOG 'attribute not found in source HTML'.
			return $element_destination;
		}

		if ( $this->get_attribute_value( $attribute_name, $element_destination ) ) {
			// TODO: DEBUG LOG 'attribute value not matched'.
			return $element_destination;
		}

		// Element tag could end with either '>' or '/>'.
		$search_1 = '>';
		$pos_1    = strpos( $element_destination, $search_1 );
		$search_2 = '/>';
		$pos_2    = strpos( $element_destination, $search_2 );
		if ( false !== $pos_2 && $pos_2 <= $pos_1 ) {
			$search = $search_2;
			$pos    = $pos_2;
		} else {
			$search = $search_1;
			$pos    = $pos_1;
		}

		if ( false === $pos ) {
			return $element_destination;
		}

		$replace              = sprintf( ' %s="%s"' . $search, $attribute_name, $attribute_value );
		$html_element_patched = substr_replace( $element_destination, $replace, $pos, strlen( $search ) );

		return $html_element_patched;
	}


	/**
	 * This checks whether class is already assigned, and appends the class if not.
	 *
	 * @param string $element The HTML element.
	 * @param string $class_value Class value.
	 *
	 * @return mixed
	 */
	public function patch_class( $element, $class_value ) {
		$class_value_existing = $this->get_attribute_value( 'class', $element );

		// This is determining the new class value.
		if ( false !== $class_value_existing ) {
			$classes = explode( ' ', $class_value_existing );
			if ( ! in_array( $class_value, $classes ) ) {
				$classes[] = $class_value;
			}
			$classes_value_patched = implode( ' ', $classes );
		} else {
			$classes_value_patched = $class_value;
		}

		$classes_expression_patched = 'class="' . $classes_value_patched . '"';

		// This is updating existing class attribvute with new value.
		if ( false !== $class_value_existing ) {

			$matches = $this->get_attribute_with_value_preg_match( 'class', $element );
			if ( false === $matches ) {
				// TODO: DEBUG LOG class not found -- this if segment will never be reached, sooo... it's actually unneccessary.
				return $element;
			}
			$class_pos            = $matches[1][1];
			$existing_classes_pos = $matches[2][1];

			$pos_start = $class_pos;
			$pos_end   = $existing_classes_pos + strlen( $class_value_existing ) + strlen( '"' );

			$length_existing_classes = $pos_end - $pos_start;

			$element_patched = substr_replace( $element, $classes_expression_patched, $pos_start, $length_existing_classes );

		} else {
			// And this is setting the class attribute anew.
			// Element tag could end with either '>' or '/>'.
			$search_1 = '>';
			$pos_1    = strpos( $element, $search_1 );
			$search_2 = '/>';
			$pos_2    = strpos( $element, $search_2 );
			if ( false !== $pos_2 && $pos_2 <= $pos_1 ) {
				$search = $search_2;
				$pos    = $pos_2;
			} else {
				$search = $search_1;
				$pos    = $pos_1;
			}

			$replace         = ' ' . $classes_expression_patched . $search;
			$element_patched = substr_replace( $element, $replace, $pos, strlen( $search ) );
		}

		return $element_patched;
	}
}
