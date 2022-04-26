<?php
/**
 * The plugin uninstall script.
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

// TODO: Switch to Composer autoloading.
require __DIR__ . '/vendor/autoload.php';

\NewspackContentConverter\Installer::uninstall_plugin();
