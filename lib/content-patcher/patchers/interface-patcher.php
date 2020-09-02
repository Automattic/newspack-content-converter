<?php
/**
 * PatcherInterface to be used by all content patchers.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher\Patchers;

/**
 * Interface PatcherInterface
 *
 * Defines a Patcher which gets to patch/improve the converted Block Content source.
 *
 * @package NewspackContentConverter\ContentPatcher
 */
interface PatcherInterface {

	/**
	 * Patcher's main method which which gets to update/patch the Block source after conversion by Gutenberg.
	 *
	 * @param string $html_content  HTML source, original content being converted.
	 * @param string $block_content Block content as result of Gutenberg "conversion to blocks".
	 *
	 * @return string Patched block content.
	 */
	public function patch_blocks_contents( $html_content, $block_content );

}
