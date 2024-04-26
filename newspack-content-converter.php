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

$converter = new Converter(
	new ConverterController(
		new ConversionProcessor()
	)
);
