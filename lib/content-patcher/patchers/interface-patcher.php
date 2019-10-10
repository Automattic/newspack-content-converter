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
 * @package NewspackContentConverter\ContentPatcher
 */
interface PatcherInterface {

	/**
	 * Patcher's main method by which a patch is applied to the block content.
	 *
	 * @param string $html_content  HTML source, original content being converted.
	 * @param string $block_content Block content as result of Gutenberg "conversion to blocks".
	 *
	 * @return string Patched block content.
	 */
	public function patch_blocks_contents( $html_content, $block_content );

}
