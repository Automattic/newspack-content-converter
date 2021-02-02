<?php
/**
 * WP-CLI integration.
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

use WP_CLI;
use NewspackContentConverter\Config;
use \NewspackContentConverter\Installer;

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
	public function register_commands() {
		WP_CLI::add_command(
			'newspack-content-converter reset',
			array( $this, 'cli_reset' ),
			array(
				'shortdesc' => 'Resets the conversion queue: clears the current `ncc_wp_posts` table from previously added Posts, and adds new Posts which need conversion.',
			)
		);
		WP_CLI::add_command(
			'newspack-content-converter restore-content',
			array( $this, 'cli_restore_content' ),
			array(
				'shortdesc' => 'Restores Post contents to the original HTML content before conversion, or if the `--blocks` flag is used, restores to post-conversion block contents.',
				array(
					'type'        => 'flag',
					'name'        => 'blocks',
					'description' => 'If this param is used, restores Post contents to post-conversion block contents.',
					'optional'    => true,
					'repeating'   => false,
				),
				'synopsis'  => array(
					array(
						'type'        => 'assoc',
						'name'        => 'post-ids',
						'description' => 'Optional CSV Post IDs. If provided, only these specific Posts will be affected.',
						'optional'    => true,
						'repeating'   => false,
					),
				),
			)
		);
	}

	/**
	 * Reset the Newspack Content Converter tables. This action is equivalent to uninstalling, deleting, and reinstaalling the plugin.
	 */
	public function cli_reset() {
		Installer::uninstall_plugin( true );
		WP_CLI::line( __( 'Uninstallation complete.', 'newspack-content-converter' ) );
		Installer::install_plugin( true );
		WP_CLI::line( __( 'Installation complete.', 'newspack-content-converter' ) );
		WP_CLI::success( 'Reset complete.' );
	}

	/**
	 * Callable for the `newspack-content-converter restore-content` command.
	 *
	 * @param array $args       WP_CLI command's $args param.
	 * @param array $assoc_args WP_CLI command's $assoc_args param.
	 */
	public function cli_restore_content( $args, $assoc_args ) {
		$restore_blocks = isset( $assoc_args['blocks'] ) ? true : false;
		$post_ids       = isset( $assoc_args['post-ids'] ) ? $assoc_args['post-ids'] : null;

		WP_CLI::line( sprintf( 'Restoring original %s content to Posts...', $restore_blocks ? 'blocks' : 'HTML' ) );

		global $wpdb;
		$ncc_table_name_esc = esc_sql( Config::get_instance()->get( 'table_name' ) );
		$posts_table_name   = $wpdb->prefix . 'posts';
		$restore_column     = $restore_blocks ? 'post_content_gutenberg_converted' : 'post_content';

		$query = "UPDATE {$posts_table_name} wp JOIN {$ncc_table_name_esc} nwp ON nwp.ID = wp.ID SET wp.post_content = nwp.{$restore_column} ";

		if ( $post_ids ) {
			// Sanitize $post_ids for DB query.
			$int_placeholders_arr = array_fill( 0, count( explode( ',', $post_ids ) ), '%d' );
			$int_placeholders_csv = implode( ',', $int_placeholders_arr );
			// phpcs:ignore -- allow, placeholders created safely here.
			$query                = $wpdb->prepare( $query . ' WHERE wp.ID IN ( ' . $int_placeholders_csv . ' ) ', explode( ',', $post_ids ) );
		}

		// phpcs:ignore -- allow, all params sanitized above.
		$wpdb->query( $query );

		wp_cache_flush();

		WP_CLI::line( 'Done 👍' );
	}
}
