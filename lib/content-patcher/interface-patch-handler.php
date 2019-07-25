<?php

namespace NewspackContentConverter\ContentPatcher;

interface PatchHandlerInterface {

	/**
	 * Runs all patches on given block content.
	 *
	 * @param string $html_content  HTML source, original content being converted.
	 * @param string $block_content Block content as result of Gutenberg "conversion to blocks".
	 *
	 * @return string Patched block content.
	 */
	public function run_all_patches( string $html_content, string $block_content ) : ?string;

}