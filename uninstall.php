<?php
/**
 * The plugin uninstall script.
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

// TODO: Switch to Composer autoloading.
require_once dirname( __FILE__ ) . '/dependency-includer-script.php';

\NewspackContentConverter\Installer::uninstall_plugin();
