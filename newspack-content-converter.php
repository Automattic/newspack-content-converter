<?php
/**
 * Plugin Name: Newspack Content Converter
 * Description:
 * Version: 0.0.1-alpha
 * Author: Automattic
 * Author URI: https://newspack.blog/
 * License: GPL2
 * Text Domain: newspack-content-converter
 * Domain Path: /languages/
 *
 * @package Newspack
 */

defined( 'ABSPATH' ) || exit;


// TODO: remove manual including, and switch to Composer autoloading.
if ( ! class_exists( '\NewspackContentConverter\Converter' ) ) {
	include_once dirname( __FILE__ ) . '/lib/class-converter.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\PatchHandlerInterface' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/interface-patch-handler.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\PatchHandler' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/class-patchhandler.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\PatcherInterface' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/interface-patcher.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-patcherabstract.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\Patchers\ImgPatcher' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-imgpatcher.php';
}


new \NewspackContentConverter\Converter(
	new \NewspackContentConverter\ContentPatcher\PatchHandler()
);
