<?php
/**
 * The Patch handler which registers all patchers, and triggers patching of converted Gutenberg block content.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher;

use NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract;
use NewspackContentConverter\ContentPatcher\Patchers\PreconversionPatcherAbstract;
/**
 * Class PatchHandler.
 * Registers specific content patchers and runs them.
 *
 * @package NewspackContentConverter\ContentPatcher
 */
class PatchHandler implements PatchHandlerInterface {

	/**
	 * PatcherInterface objects, have interface
	 *
	 * @var array
	 */
	private $patchers = [];

	/**
	 * PreconversionPatcherInterface objects, have interface
	 *
	 * @var array
	 */
	private $preconversion_patchers = [];

	/**
	 * PatchHandler constructor.
	 *
	 * @param array $patchers An array of PatcherInterface objects.
	 */
	public function __construct( $patchers ) {
		if ( $patchers && is_array( $patchers ) ) {
			foreach ( $patchers as $patcher ) {
				if ( $patcher instanceof PreconversionPatcherAbstract ) {
					$this->preconversion_patchers[] = $patcher;
				} elseif ( $patcher instanceof PatcherAbstract ) {
					$this->patchers[] = $patcher;
				}
			}
		}
	}

	/**
	 * See the \NewspackContentConverter\ContentPatcher\PatchHandlerInterface::run_all_preconversion_patches.
	 *
	 * @param string $html_content HTML content.
	 * @param int    $post_id      Post ID.
	 *
	 * @return string|null Patched HTML content.
	 */
	public function run_all_preconversion_patches( $html_content, $post_id ) {
		if ( empty( $this->preconversion_patchers ) ) {
			return $html_content;
		}

		foreach ( $this->preconversion_patchers as $patcher ) {
			$html_content = $patcher->patch_html_source( $html_content, $post_id );
		}

		return $html_content;
	}

	/**
	 * See the \NewspackContentConverter\ContentPatcher\PatchHandlerInterface::run_all_patches.
	 *
	 * @param string $html_content  HTML content.
	 * @param string $block_content Blocks content.
	 *
	 * @return string|null Patched Blocks content.
	 */
	public function run_all_postconversion_patches( $html_content, $block_content ) {
		if ( empty( $this->patchers ) ) {
			return $block_content;
		}

		foreach ( $this->patchers as $patcher ) {
			$block_content = $patcher->patch_blocks_contents( $html_content, $block_content );
		}

		return $block_content;
	}
}
