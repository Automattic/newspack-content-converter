<?php
/**
 * Dependency includer script.
 * TODO: use Composer autoloading instead of this.
 *
 * @package Newspack
 */

if ( ! class_exists( '\NewspackContentConverter\Config' ) ) {
	include_once dirname( __FILE__ ) . '/lib/class-config.php';
}
if ( ! class_exists( '\NewspackContentConverter\Converter' ) ) {
	include_once dirname( __FILE__ ) . '/lib/class-converter.php';
}
if ( ! class_exists( '\NewspackContentConverter\ConverterController' ) ) {
	include_once dirname( __FILE__ ) . '/lib/class-convertercontroller.php';
}
if ( ! class_exists( '\NewspackContentConverter\Installer' ) ) {
	include_once dirname( __FILE__ ) . '/lib/class-installer.php';
}
if ( ! class_exists( '\NewspackContentConverter\ConversionProcessor' ) ) {
	include_once dirname( __FILE__ ) . '/lib/class-conversionprocessor.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\PatchHandlerInterface' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/interface-patch-handler.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\PatchHandler' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/class-patchhandler.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\Patchers\PatcherInterface' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/interface-patcher.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-patcherabstract.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\Patchers\ImgPatcher' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-imgpatcher.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\Patchers\ParagraphPatcher' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-paragraphpatcher.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\Patchers\BlockquotePatcher' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-blockquotepatcher.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\Patchers\VideoPatcher' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-videopatcher.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\Patchers\AudioPatcher' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-audiopatcher.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\Patchers\CaptionImgPatcher' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-captionimgpatcher.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\ElementManipulators\HtmlElementManipulator' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/elementManipulators/class-htmlelementmanipulator.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\ElementManipulators\SquareBracketsElementManipulator' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/elementManipulators/class-squarebracketselementmanipulator.php';
}
if ( ! class_exists( '\NewspackContentConverter\ContentPatcher\ElementManipulators\WpBlockManipulator' ) ) {
	include_once dirname( __FILE__ ) . '/lib/content-patcher/elementManipulators/class-wpblockmanipulator.php';
}
