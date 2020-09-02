<?php
/**
 * Data Provider for tests.
 *
 * @package Newspack
 */

/**
 * Class DataProviderShortcodePullquotePatcher
 *
 * Data Provider for tests concerning unit/content-patcher/patchers/test-shortcodepullquote-patcher.php
 */
class DataProviderShortcodePreconversionPatcher {

	/**
	 * Get an unpatched HTML with gallery shortcoded of which some need newline prefixing.
	 *
	 * @return string Unpatched HTML content.
	 */
	public static function get_html_with_gallery_shortcodes_mixed() {
		return '[gallery ids="1111"] This shortcode stays where it is because it is at the beginning.
The following shortcode should be broken to new line...[gallery ids="2222"]
Some txt.
The following should be broken too.[gallery ids="3333"]
The next one should not get an extra line break.
[gallery ids="4441,4442"]
End.';
	}

	/**
	 * Get the patched HTML.
	 *
	 * @return string Patched block content.
	 */
	public static function get_html_with_gallery_shortcodes_mixed_expected() {
		return '[gallery ids="1111"] This shortcode stays where it is because it is at the beginning.
The following shortcode should be broken to new line...
[gallery ids="2222"]
Some txt.
The following should be broken too.
[gallery ids="3333"]
The next one should not get an extra line break.
[gallery ids="4441,4442"]
End.';
	}
}
