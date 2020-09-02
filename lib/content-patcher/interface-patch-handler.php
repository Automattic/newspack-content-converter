<?php
/**
 * PatchHandlerInterface.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher;

interface PatchHandlerInterface {

	/**
	 * Runs all pre-conversion patches which get to modify the original HTML source before it gets converted to Blocks.
	 *
	 * @param string $html_content  HTML source, original content being converted.
	 *
	 * @return string Patched HTML source, before it gets converted to Blocks.
	 */
	public function run_all_preconversion_patches( $html_content );

	/**
	 * Runs all patches on given block content.
	 *
	 * @param string $html_content  HTML source, original content being converted.
	 * @param string $block_content Block content as result of Gutenberg "conversion to blocks".
	 *
	 * @return string Patched block content.
	 */
	public function run_all_patches( $html_content, $block_content );

}
