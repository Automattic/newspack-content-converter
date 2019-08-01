<?php
/**
 * The Patch handler which registers all patchers, and triggers patching of converted Gutenberg block content.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher;

/**
 * Class PatchHandler.
 * Registers specific content patchers and runs them.
 *
 * @package NewspackContentConverter\ContentPatcher
 */
class PatchHandler implements PatchHandlerInterface {

	/**
	 * List of active content patchers.
	 *
	 * @var array
	 */
	private $classes = [
		'\NewspackContentConverter\ContentPatcher\Patchers\ImgPatcher',
	];

	/**
	 * Instantiated patcher objects.
	 *
	 * @var array
	 */
	private $patchers = [];

	/**
	 * PatchHandler constructor.
	 */
	public function __construct() {
		$this->register_patchers();
	}

	/**
	 * Registers patchers.
	 */
	private function register_patchers() {
		foreach ( $this->classes as $class ) {
			$this->patchers[] = new $class();
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
		$patched_block_content = $block_content;

		foreach ( $this->patchers as $patcher ) {
			$patched_block_content = $patcher->patch_blocks_contents( $html_content, $patched_block_content );
		}

		return $patched_block_content;
	}
}
