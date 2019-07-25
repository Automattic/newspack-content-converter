<?php

namespace NewspackContentConverter\ContentPatcher;

/**
 * Class PatchHandler.
 * Registers specific content patchers and runs them.
 *
 * @package NewspackContentConverter\ContentPatcher
 */
class PatchHandler implements PatchHandlerInterface {

	/**
	 * @var array List of active content patchers.
	 */
	private $classes = [
		'\NewspackContentConverter\ContentPatcher\Patchers\ImgPatcher',
	];

	/**
	 * @var array Instantiated patcher objects.
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
			$this->patchers[] = new $class;
		}
	}

	/**
	 * @see \NewspackContentConverter\ContentPatcher\PatchHandlerInterface::run_all_patches
	 *
	 * @param string $html_content
	 * @param string $block_content
	 *
	 * @return string|null
	 */
    public function run_all_patches( string $html_content, string $block_content ) : ?string {
	    $patched_block_content = $block_content;

	    foreach ( $this->patchers as $patcher ) {
		    $patched_block_content = $patcher->patch_blocks_contents( $html_content, $patched_block_content );
		}

		return $patched_block_content;
	}
}