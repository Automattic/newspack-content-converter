<?php
/**
 * Plugin Name: Newspack Content Converter
 * Description: Mass converts pre-Gutenberg HTML content to Gutenberg Blocks.
 * Version: 0.2.0
 * Author: Automattic
 * Author URI: https://newspack.blog/
 * License: GPL2
 * Text Domain: newspack-content-converter
 * Domain Path: /languages/
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

require __DIR__ . '/vendor/autoload.php';

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'NCC_PLUGIN_FILE' ) ) {
	// Warning, __FILE__ might not play well with symlinks in dev env.
	define( 'NCC_PLUGIN_FILE', __FILE__ );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	( new CLI() )->register_commands();
}

// Construct the app with a dependency graph, without the use of a service container.
$patch_handler = new ContentPatcher\PatchHandler(
	[
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
	]
);
$processor     = new ConversionProcessor(
	$patch_handler
);
$controller    = new ConverterController(
	$processor
);
$converter     = new Converter(
	$controller
);
