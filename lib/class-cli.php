<?php
/**
 * WP-CLI integration.
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

use WP_CLI;
use NewspackContentConverter\Config;

/**
 * Class Config
 *
 * @package NewspackContentConverter
 */
class CLI {

	/**
	 * Singleton instance.
	 *
	 * @var Config
	 */
	private static $instance;

	/**
	 * Config constructor.
	 */
	public function register_commands() {
		WP_CLI::add_command(
			'newspack-content-converter debug',
			array( $this, 'cli_debug' ),
		);
	}

	public function cli_debug() {
		$patch_handler = new ContentPatcher\PatchHandler(
			array(
				// Encode blocks as very first thing.
				new ContentPatcher\Patchers\BlockEncodePatcher(),
				new ContentPatcher\Patchers\WpFiltersPatcher(),
				// Pre-conversion Patchers.
				new ContentPatcher\Patchers\ShortcodePreconversionPatcher(),
				// Patchers.
				new ContentPatcher\Patchers\ImgPatcher(),
				new ContentPatcher\Patchers\CaptionImgPatcher(),
				new ContentPatcher\Patchers\ParagraphPatcher(),
				new ContentPatcher\Patchers\BlockquotePatcher(),
				new ContentPatcher\Patchers\VideoPatcher(),
				new ContentPatcher\Patchers\AudioPatcher(),
				new ContentPatcher\Patchers\ShortcodeModulePatcher(),
				new ContentPatcher\Patchers\ShortcodePullquotePatcher(),
				// Decode blocks as the very last thing.
				new ContentPatcher\Patchers\BlockDecodePatcher(),
			)
		);
		$processor = new ConversionProcessor(
			$patch_handler
		);

		// $controller = new \NewspackContentConverter\ConverterController( $processor );
		// $controller->get_conversion_batch_data();

		return;
	}

}
