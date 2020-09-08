<?php
/**
 * PreconversionPatcherInterface is used to patch the original HTML source before any conversion to Blocks is performed.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher\Patchers;

/**
 * Interface PreconversionPatcherInterface
 *
 * Defines a Patcher which gets to patch/improve the original HTML source before it gets converted to Blocks.
 *
 * @package NewspackContentConverter\ContentPatcher
 */
interface PreconversionPatcherInterface {

	/**
	 * Patcher's main method which updates the HTML source before it's piped to be converted to Blocks.
	 *
	 * @param string $html_content HTML source, original Post content.
	 *
	 * @return string Patched HTML source which is about to be converted to Blocks.
	 */
	public function patch_html_source( $html_content );

}
