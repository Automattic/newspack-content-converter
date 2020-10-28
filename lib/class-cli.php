<?php
/**
 * WP-CLI integration.
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

use NewspackContentConverter\Config;

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
		\WP_CLI::add_command(
			'newspack-content-converter reset',
			[ $this, 'cli_reset' ],
			[
				'shortdesc' => 'Resets the conversion queue: clears the current `ncc_wp_posts` table from previously added Posts, and adds new Posts which need conversion.',
			]
		);
		\WP_CLI::add_command(
			'newspack-content-converter restore-content',
			[ $this, 'cli_restore_content' ],
			[
				'shortdesc' => 'Restores Post contents to the original HTML content before conversion, or if the `--blocks` flag is used, restores to post-conversion block contents.',
				[
					'type'        => 'flag',
					'name'        => 'blocks',
					'description' => 'If this param is used, restores Post contents to post-conversion block contents.',
					'optional'    => true,
					'repeating'   => false,
				],
			]
		);
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

	/**
	 * Callable for the `newspack-content-converter restore-content` command.
	 *
	 * @param array $args       WP_CLI command's $args param.
	 * @param array $assoc_args WP_CLI command's $assoc_args param.
	 */
	public function cli_restore_content( $args, $assoc_args ) {
		$restore_blocks = isset( $assoc_args['blocks'] ) ? true : false;

		\WP_CLI::line( sprintf( 'Restoring original %s content to Posts...', $restore_blocks ? 'blocks' : 'HTML' ) );

		global $wpdb;
		$ncc_table_name_esc = esc_sql( Config::get_instance()->get( 'table_name' ) );
		$posts_table_name   = $wpdb->prefix . 'posts';
		$restore_column     = $restore_blocks ? 'post_content_gutenberg_converted' : 'post_content';

		// phpcs:ignore -- allow this direct DB call since prepare() can not sanitize table names or columns, and all the params used here are sanitized.
		$wpdb->query( "UPDATE {$posts_table_name} wp JOIN {$ncc_table_name_esc} nwp ON nwp.ID = wp.ID SET wp.post_content = nwp.{$restore_column} ;" );
	}
}
new \NewspackContentConverter\CLI();
