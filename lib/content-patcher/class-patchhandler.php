<?php
/**
 * The Patch handler which registers all patchers, and triggers patching of converted Gutenberg block content.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher;

use NewspackContentConverter\ContentPatcher\Patchers\PatcherInterface;
/**
 * Class PatchHandler.
 * Registers specific content patchers and runs them.
 *
 * @package NewspackContentConverter\ContentPatcher
 */
class PatchHandler implements PatchHandlerInterface {

	/**
	 * Patcher objects, have interface
	 *
	 * @var array
	 */
	private $patchers = [];

	/**
	 * PatchHandler constructor.
	 *
	 * @param array $patchers An array of PatcherInterface objects.
	 */
	public function __construct( $patchers ) {
		if ( $patchers && is_array( $patchers ) ) {
			foreach ( $patchers as $patcher ) {
				if ( $patcher instanceof PatcherInterface ) {
					$this->patchers[] = $patcher;
				}
			}
		}
	}

	/**
	 * See the \NewspackContentConverter\ContentPatcher\PatchHandlerInterface::run_all_patches.
	 *
	 * @param string $html_content  HTML content.
	 * @param string $block_content Blocks content.
	 *
	 * @return string|null
	 */
	public function run_all_patches( $html_content, $block_content ) {
		foreach ( $this->patchers as $patcher ) {
			$block_content = $patcher->patch_blocks_contents( $html_content, $block_content );
		}

		return $block_content;
	}
}
