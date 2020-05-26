<?php
/**
 * Dependency includer script.
 *
 * @package Newspack
 */

// TODO: Switch to Composer autoloading and remove this file all together.
require_once dirname( __FILE__ ) . '/lib/class-config.php';
require_once dirname( __FILE__ ) . '/lib/class-converter.php';
require_once dirname( __FILE__ ) . '/lib/class-convertercontroller.php';
require_once dirname( __FILE__ ) . '/lib/class-installer.php';
require_once dirname( __FILE__ ) . '/lib/class-conversionprocessor.php';
require_once dirname( __FILE__ ) . '/lib/content-patcher/interface-patch-handler.php';
require_once dirname( __FILE__ ) . '/lib/content-patcher/class-patchhandler.php';
require_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/interface-patcher.php';
require_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-patcherabstract.php';
require_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-imgpatcher.php';
require_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-paragraphpatcher.php';
require_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-blockquotepatcher.php';
require_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-videopatcher.php';
require_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-audiopatcher.php';
require_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-captionimgpatcher.php';
require_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-shortcodemodulepatcher.php';
require_once dirname( __FILE__ ) . '/lib/content-patcher/patchers/class-shortcodepullquotepatcher.php';
require_once dirname( __FILE__ ) . '/lib/content-patcher/elementManipulators/class-htmlelementmanipulator.php';
require_once dirname( __FILE__ ) . '/lib/content-patcher/elementManipulators/class-squarebracketselementmanipulator.php';
require_once dirname( __FILE__ ) . '/lib/content-patcher/elementManipulators/class-wpblockmanipulator.php';
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once dirname( __FILE__ ) . '/lib/class-cli.php';
}
