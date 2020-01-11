<?php
/**
 * Plugin installer class.
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

use \NewspackContentConverter\Config;

/**
 * Class Installer
 *
 * @package NewspackContentConverter
 */
class Installer {

	/**
	 * Plugin installation.
	 * Creates a table, sets plugin options.
	 */
	public static function install_plugin() {
		set_time_limit( 0 );

		$table_name            = Config::get_instance()->get( 'table_name' );
		$post_statuses         = Config::get_instance()->get( 'post_statuses' );
		$conversion_batch_size = Config::get_instance()->get( 'conversion_batch_size' );
		$patching_batch_size   = Config::get_instance()->get( 'patching_batch_size' );
		$post_types            = self::get_post_types_editable_by_block_editor();

		self::create_table( $table_name );
		$total_entries = self::insert_entries( $table_name, $post_types, $post_statuses );
		self::set_initial_options(
			$post_types,
			$post_statuses,
			$total_entries,
			$conversion_batch_size,
			$patching_batch_size
		);
	}

	/**
	 * Plugin uninstallation.
	 * Drops the plugin table, deleted plugin options.
	 */
	public static function uninstall_plugin() {
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			exit();
		}

		$table_name = Config::get_instance()->get( 'table_name' );

		self::drop_table( $table_name );
		self::delete_all_options();
	}

	/**
	 * Create a table in DB.
	 *
	 * @param string $table_name Table name.
	 */
	private static function create_table( $table_name ) {
		if ( self::table_exists( $table_name ) ) {
			return;
		}

		global $wpdb;

		$table_name = esc_sql( $table_name );

		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE `{$table_name}` (
	  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	  `post_author` bigint(20) unsigned NOT NULL DEFAULT 0,
	  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `post_content` longtext NOT NULL,
	  `post_title` text NOT NULL,
	  `post_excerpt` text NOT NULL,
	  `post_status` varchar(20) NOT NULL DEFAULT 'publish',
	  `comment_status` varchar(20) NOT NULL DEFAULT 'open',
	  `ping_status` varchar(20) NOT NULL DEFAULT 'open',
	  `post_password` varchar(255) NOT NULL DEFAULT '',
	  `post_name` varchar(200) NOT NULL DEFAULT '',
	  `to_ping` text NOT NULL,
	  `pinged` text NOT NULL,
	  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `post_content_filtered` longtext NOT NULL,
	  `post_parent` bigint(20) unsigned NOT NULL DEFAULT 0,
	  `guid` varchar(255) NOT NULL DEFAULT '',
	  `menu_order` int(11) NOT NULL DEFAULT 0,
	  `post_type` varchar(20) NOT NULL DEFAULT 'post',
	  `post_mime_type` varchar(100) NOT NULL DEFAULT '',
	  `comment_count` bigint(20) NOT NULL DEFAULT 0,
	  `post_content_gutenberg_converted` longtext DEFAULT '',
	  `retry_conversion` tinyint(1) DEFAULT NULL,
	  PRIMARY KEY (`ID`),
	  KEY `post_name` (`post_name`(191)),
	  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
	  KEY `post_parent` (`post_parent`),
	  KEY `post_author` (`post_author`)
	) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore -- allow DB modification for custom table.
		dbDelta( $sql );
	}

	/**
	 * Drops a table from the DB.
	 *
	 * @param string $table_name Table name.
	 */
	private static function drop_table( $table_name ) {
		global $wpdb;

		$table_name_esc = esc_sql( $table_name );
		// phpcs:ignore -- allow this direct DB call; the table name is escaped.
		$wpdb->query( "DROP TABLE IF EXISTS {$table_name_esc};" );
	}

	/**
	 * Gets post_types which are editable by the Block Editor.
	 * It attempts to use the use_block_editor_for_post_type() function, and if it's not found, it defaults to 'post', and 'page'.
	 *
	 * @return array Post types.
	 */
	private static function get_post_types_editable_by_block_editor() {
		$post_types = get_post_types( array( 'public' => true ) );

		// phpcsignore -- ignore warning, allow following comment with valid code.
		// Attempt to use use_block_editor_for_post_type() function, or else default to 'post' and 'page'.
		require_once ABSPATH . 'wp-admin/includes/post.php';
		if ( function_exists( 'use_block_editor_for_post_type' ) ) {
			foreach ( $post_types as $post_type ) {
				if ( use_block_editor_for_post_type( $post_type ) ) {
					$post_types_for_be[] = $post_type;
				}
			}
		} else {
			if ( in_array( 'post', $post_types ) ) {
				$post_types_for_be[] = 'post';
			}
			if ( in_array( 'page', $post_types ) ) {
				$post_types_for_be[] = 'page';
			}
		}

		return $post_types_for_be;
	}

	/**
	 * Inserts posts/entries into the table.
	 *
	 * It takes and queues for conversion entries from the `wp_posts` table, but leaves out those with blank content, or which
	 * already contain Gutenberg Blocks.
	 *
	 * @param string $table_name Table name.
	 * @param array  $post_types Post Types.
	 * @param array  $post_statuses Post Statuses.
	 *
	 * @return int|false Total entries inserted, or false.
	 */
	private static function insert_entries( $table_name, $post_types, $post_statuses ) {
		global $wpdb;

		$table_name           = esc_sql( $table_name );
		$wp_posts_table       = $wpdb->posts;
		$wp_posts_columns_csv = 'ID,post_author,post_date,post_date_gmt,post_content,post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count';

		// Insert specified content into plugin's table.
		$type_placeholders         = array_fill( 0, count( $post_types ), '%s' );
		$type_placeholders_csv     = implode( ',', $type_placeholders );
		$statuses_placeholders     = array_fill( 0, count( $post_statuses ), '%s' );
		$statuses_placeholders_csv = implode( ',', $statuses_placeholders );

		// SQL `NOT REGEXP` part explanation: it excludes empty Posts, i.e. where NOT matched one or more of following:
		// [:space:] -- any blank space,
		// (&nbsp;)  -- this literal group of characters,
		// (\r\n)    -- line break (being in a string, here escaped with extra `\`).
		$sql_placeholders = "INSERT INTO {$table_name} ( {$wp_posts_columns_csv} )
									  SELECT {$wp_posts_columns_csv}
									  FROM {$wp_posts_table}
									  WHERE post_status IN ({$statuses_placeholders_csv})
									  AND post_type IN ({$type_placeholders_csv})
									  AND post_content <> ''
									  AND post_content NOT LIKE '<!-- wp:%'
									  AND post_content NOT REGEXP '^[[:space:]|(&nbsp;)|(\\r\\n)]+$' ;";
		// phpcs:ignore -- false positive, all params are fully sanitized.
		$wpdb->get_results( $wpdb->prepare( $sql_placeholders, array_merge( $post_statuses, $post_types ) ) );

		// phpcs:ignore -- a false positive, this SQL is safe.
		$results       = $wpdb->get_results( "SELECT COUNT(*) AS total FROM {$table_name} ; " );
		$total_entries = isset( $results[0]->total ) ? (int) $results[0]->total : false;

		return $total_entries;
	}

	/**
	 * Sets initial plugin options during installation.
	 *
	 * @param array $post_types_for_conversion Post types configured for conversion.
	 * @param array $post_statuses_for_conversion Post statuses configured for conversion.
	 * @param int   $queued_entries_total Total number of posts/entries queued for conversion.
	 * @param int   $conversion_batch_size Size of the conversion batch (number of posts/entries per batch).
	 * @param int   $patching_batch_size Size of the patching batch (number of posts/entries per batch).
	 */
	private static function set_initial_options(
		$post_types_for_conversion,
		$post_statuses_for_conversion,
		$queued_entries_total,
		$conversion_batch_size,
		$patching_batch_size
	) {
		$post_types_csv                      = implode( ',', $post_types_for_conversion );
		$post_statuses_csv                   = implode( ',', $post_statuses_for_conversion );
		$conversion_max_batches              = (int) ceil( $queued_entries_total / $conversion_batch_size );
		$patching_max_batches                = (int) ceil( $queued_entries_total / $patching_batch_size );
		$option_conversion_post_types_csv    = Config::get_instance()->get( 'option_conversion_post_types_csv' );
		$option_conversion_post_statuses_csv = Config::get_instance()->get( 'option_conversion_post_statuses_csv' );
		$option_conversion_batch_size        = Config::get_instance()->get( 'option_conversion_batch_size' );
		$option_patching_batch_size          = Config::get_instance()->get( 'option_patching_batch_size' );
		$option_conversion_max_batches       = Config::get_instance()->get( 'option_conversion_max_batches' );
		$option_patching_max_batches         = Config::get_instance()->get( 'option_patching_max_batches' );

		if ( null === get_option( $option_conversion_post_types_csv, null ) ) {
			update_option( $option_conversion_post_types_csv, $post_types_csv );
		}
		if ( null === get_option( $option_conversion_post_statuses_csv, null ) ) {
			update_option( $option_conversion_post_statuses_csv, $post_statuses_csv );
		}
		if ( null === get_option( $option_conversion_batch_size, null ) ) {
			update_option( $option_conversion_batch_size, $conversion_batch_size );
		}
		if ( null === get_option( $option_patching_batch_size, null ) ) {
			update_option( $option_patching_batch_size, $patching_batch_size );
		}
		if ( null === get_option( $option_conversion_max_batches, null ) ) {
			update_option( $option_conversion_max_batches, $conversion_max_batches );
		}
		if ( null === get_option( $option_patching_max_batches, null ) ) {
			update_option( $option_patching_max_batches, $patching_max_batches );
		}
	}

	/**
	 * Deletes all plugin's options.
	 */
	private static function delete_all_options() {
		delete_option( Config::get_instance()->get( 'option_conversion_post_types_csv' ) );
		delete_option( Config::get_instance()->get( 'option_conversion_post_statuses_csv' ) );
		delete_option( Config::get_instance()->get( 'option_conversion_batch_size' ) );
		delete_option( Config::get_instance()->get( 'option_conversion_max_batches' ) );
		delete_option( Config::get_instance()->get( 'option_retry_conversion_failed_max_batches' ) );
		delete_option( Config::get_instance()->get( 'option_is_queued_conversion' ) );
		delete_option( Config::get_instance()->get( 'option_is_queued_retry_failed_conversion' ) );
		delete_option( Config::get_instance()->get( 'option_conversion_queued_batches' ) );
		delete_option( Config::get_instance()->get( 'option_retry_conversion_failed_queued_batches' ) );
		delete_option( Config::get_instance()->get( 'option_patching_batch_size' ) );
		delete_option( Config::get_instance()->get( 'option_patching_max_batches' ) );
		delete_option( Config::get_instance()->get( 'option_patching_is_queued' ) );
		delete_option( Config::get_instance()->get( 'option_patching_queued_batches' ) );
	}

	/**
	 * Chechs whether a table exists in the DB.
	 *
	 * @param string $table_name Table name.
	 *
	 * @return bool
	 */
	private static function table_exists( $table_name ) {
		global $wpdb;

		$table_name = esc_sql( $table_name );

		// phpcs:ignore -- OK to use a direct DB call here.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT *
		         FROM INFORMATION_SCHEMA.TABLES
		         WHERE TABLE_SCHEMA = %s
		         AND TABLE_NAME = %s;',
				$wpdb->dbname,
				$table_name
			)
		);

		$table_exsists = $results && isset( $results[0] ) && ! empty( $results[0] ) ? true : false;

		return $table_exsists;
	}
}
