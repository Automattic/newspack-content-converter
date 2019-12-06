<?php
/**
 * Plugin Name: Newspack Content Converter
 * Description: Mass converts pre-Gutenberg HTML content to Gutenberg Blocks.
 * Version: 0.0.5-alpha
 * Author: Automattic
 * Author URI: https://newspack.blog/
 * License: GPL2
 * Text Domain: newspack-content-converter
 * Domain Path: /languages/
 *
 * @package Newspack
 */

defined( 'ABSPATH' ) || exit;

// TODO, Warning, __FILE__ might not play well with symlinks in dev env.
if ( ! defined( 'NCC_PLUGIN_FILE' ) ) {
	define( 'NCC_PLUGIN_FILE', __FILE__ );
}


// TODO: Switch to Composer autoloading.
require_once dirname( __FILE__ ) . '/dependency-includer-script.php';


// Construct the app with a dependency graph, without the use of a service container.
new \NewspackContentConverter\Converter(
	new \NewspackContentConverter\Installer(),
	new \NewspackContentConverter\ConverterController(
		new \NewspackContentConverter\ConversionProcessor(
			new \NewspackContentConverter\ContentPatcher\PatchHandler(
				array(
					new \NewspackContentConverter\ContentPatcher\Patchers\ImgPatcher(),
					new \NewspackContentConverter\ContentPatcher\Patchers\CaptionImgPatcher(),
					new \NewspackContentConverter\ContentPatcher\Patchers\ParagraphPatcher(),
					new \NewspackContentConverter\ContentPatcher\Patchers\BlockquotePatcher(),
					new \NewspackContentConverter\ContentPatcher\Patchers\VideoPatcher(),
					new \NewspackContentConverter\ContentPatcher\Patchers\AudioPatcher(),
					new \NewspackContentConverter\ContentPatcher\Patchers\ShortcodeModulePatcher(),
					new \NewspackContentConverter\ContentPatcher\Patchers\ShortcodePullquotePatcher(),
				)
			)
		)
	)
);
