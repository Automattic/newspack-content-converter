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

if ( ! class_exists( '\NewspackContentConverter\Converter' ) ) {
	include_once dirname( __FILE__ ) . '/lib/class-converter.php';
}

( new \NewspackContentConverter\Converter )->init();
