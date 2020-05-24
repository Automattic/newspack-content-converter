<?php
/**
 * WP-CLI integration.
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

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
	public function __construct() {
		\WP_CLI::add_command( 'newspack-content-converter reset', [ $this, 'cli_reset' ] );
	}

	/**
	 * Reset the Newspack Content Converter tables. This action is equivalent to uninstalling, deleting, and reinstaalling the plugin.
	 */
	public static function cli_reset() {
		\NewspackContentConverter\Installer::uninstall_plugin( true );
		\WP_CLI::line( __( 'Uninstallation complete.', 'newspack-content-converter' ) );
		\NewspackContentConverter\Installer::install_plugin( true );
		\WP_CLI::line( __( 'Installation complete.', 'newspack-content-converter' ) );
		\WP_CLI::success( 'Reset complete.' );
	}
}
new \NewspackContentConverter\CLI();
