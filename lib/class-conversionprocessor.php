<?php
/**
 * Conversion processor class.
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

use \NewspackContentConverter\ContentPatcher\PatchHandlerInterface;
use \NewspackContentConverter\Config;

/**
 * Class ConversionProcessor
 *
 * @package NewspackContentConverter
 */
class ConversionProcessor {

	/**
	 * The Patch handler service.
	 *
	 * @var PatchHandlerInterface $patcher_handler
	 */
	private $patcher_handler;

	/**
	 * ConversionProcessor constructor.
	 *
	 * @param PatchHandlerInterface $patcher_handler Patcher handler.
	 */
	public function __construct( PatchHandlerInterface $patcher_handler ) {
		$this->patcher_handler = $patcher_handler;
	}

	/**
	 * Gets content types to be processed by the plugin.
	 *
	 * @return array Content types.
	 */
	public function get_conversion_content_types() {
		return ['post', 'page'];
	}

	/**
	 * Gets content type statuses to be processed by the plugin.
	 *
	 * @return array Content statuses.
	 */
	public function get_conversion_content_statuses() {
		return [ 'publish' ];
	}

	/**
	 * Gets the number of posts/content processed by a conversion batch.
	 *
	 * @return int Number of posts/content processed by a conversion batch.
	 */
	public function get_conversion_batch_size() {
		return 100;
	}

	/**
	 * Gets number of batches to be converted.
	 *
	 * @return int|null Batch number.
	 */
	public function get_number_of_batches_to_be_converted() {
		$batches = (int) ceil( $this->get_unconverted_posts_total_number() / $this->get_conversion_batch_size() );

		return $batches;
	}

	/**
	 * This gets the posts that need to be converted -- either all (if $limit = 0), or just for the next batch (if $limit = size of batch).
	 * Excludes empty posts.
	 * Ordering is by ID descending.
	 *
	 * @param int   $limit         You want to use size of batch here to get just the IDs for the next batch.
	 * @param array $post_statuses
	 * @param array $post_types
	 * @return array Results from $wpdb->get_results() as ARRAY_A.
	 */
	public function get_unconverted_posts( int $limit = 0, array $post_statuses = ['publish','draft','pending','future','private'], array  $post_types = ['post','page'] ) {
		global $wpdb;

		$statuses_placeholders = implode( ',', array_fill( 0, count( $post_statuses ), '%s' ) );
		$types_placeholders    = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );

		$limit_clause = '';
		if ( $limit > 0 ) {
			$limit_clause = $wpdb->prepare( " LIMIT %d ", $limit );
		}

		// Excludes those that have already been converted and empty ones.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT wp.ID,wp.post_type,wp.post_status,wp.post_content,wp.post_excerpt,wp.post_name,wp.guid
				FROM {$wpdb->posts} wp
				-- Left join to get all wp_posts with or without meta.
				LEFT JOIN {$wpdb->postmeta} wpm
					ON wpm.post_id = wp.ID
					AND wpm.meta_key = 'ncc_original_post_content_blocks'
				-- Select desired post types.
				WHERE wp.post_type IN ( {$types_placeholders} )
				AND wp.post_status IN ( {$statuses_placeholders} )
				-- Filter out post which are already in blocks.
				AND wp.post_content NOT LIKE '<!-- wp:%'
				-- Fetch posts that were not yet converted i.e. have no saved meta.
				AND wpm.meta_id IS NULL
				ORDER BY wp.ID DESC
				-- Optional limit gets all posts or posts in the next batch.
				{$limit_clause} ;",
				array_merge( $post_types, $post_statuses )
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Gets the successfully converted post IDs.
	 *
	 * @param array $post_statuses
	 * @param array $post_types
	 * @return void
	 */
	public function get_successfully_converted_ids( array $post_statuses = ['publish','draft','pending','future','private'], array  $post_types = ['post','page'] ) {
		global $wpdb;

		$statuses_placeholders = implode( ',', array_fill( 0, count( $post_statuses ), '%s' ) );
		$types_placeholders    = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );

		// Excludes those that have already been converted and empty ones.
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT wp.ID
				FROM {$wpdb->posts} wp
				JOIN {$wpdb->postmeta} wpm
					ON wpm.post_id = wp.ID
					AND wpm.meta_key = 'ncc_original_post_content_blocks'
				WHERE wp.post_type IN ( {$types_placeholders} )
				AND wp.post_status IN ( {$statuses_placeholders} )
				-- Either unconverted posts (no meta) or converted but make sure they were converted successfully (wp_posts.post_content doesn't get updated if conversion syntax is empty, so it's kept the same).
				AND (
					wpm.meta_value IS NULL
					OR wpm.meta_value <> wp.post_content
				)
				; ",
				array_merge( $post_types, $post_statuses )
			)
		);

		return $ids;
	}

	public function get_unsuccessfully_converted_ids( array $post_statuses = ['publish','draft','pending','future','private'], array  $post_types = ['post','page'] ) {
		global $wpdb;

		$statuses_placeholders = implode( ',', array_fill( 0, count( $post_statuses ), '%s' ) );
		$types_placeholders    = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );

		// Excludes those that have already been converted and empty ones.
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT wp.ID
				FROM {$wpdb->posts} wp
				-- Get posts with the meta.
				JOIN {$wpdb->postmeta} wpm
				  ON wpm.post_id = wp.ID
				  AND wpm.meta_key = 'ncc_original_post_content_blocks'
				WHERE wp.post_type IN ( {$types_placeholders} )
				AND wp.post_status IN ( {$statuses_placeholders} )
				-- Fetch posts where conversion happened (meta exists) but post_content is still the same as original post content (wp_posts.post_content does not get updated/saved if the conversion returns an empty result i.e. was unsuccessful).
				AND wpm.meta_value = wp.post_content ;",
				array_merge( $post_types, $post_statuses )
			)
		);

		return $ids;
	}

	/**
	 * Gets total number of entries (posts) configured for conversion.
	 *
	 * @return int|false Total number of entries.
	 */
	public function get_unconverted_posts_total_number() {
		return count( $this->get_unconverted_posts() );
	}

	/**
	 * Fetches posts IDs for a batch.
	 * Default orderig of IDs is descending.
	 *
	 * @param int $this_batch Batch number.
	 * @param int $batch_size Batch size (number of posts/entries per batch).
	 *
	 * @return array Post IDs.
	 */
	public function get_ids_for_next_batch() {
		$results = $this->get_unconverted_posts( $this->get_conversion_batch_size() );

		$ids = [];
		foreach ( $results as $result ) {
			$ids[] = $result['ID'];
		}

		return $ids;
	}

	/**
	 * Sets the next batch to queue.
	 *
	 * If all batches are processed, returns null.
	 *
	 * @return int|null Number of this batch, or null if no more batches to process.
	 */
	public function set_next_batch_to_queue() {
		// Get queued batches.
		$queued_batches                    = $this->get_conversion_queued_batches();
		$number_of_batches_to_be_converted = $this->get_number_of_batches_to_be_converted();

		// If all the batches were processed, clear the queue.
		$this_batch = empty( $queued_batches ) ? 1 : max( $queued_batches ) + 1;
		if ( $this_batch > $number_of_batches_to_be_converted ) {
			delete_option( 'ncc-conversion_queued_batches_csv' );

			return null;
		}

		// Immediately add this batch to the queue.
		$this->add_batch_to_coversion_queue( $this_batch, $queued_batches );

		return $this_batch;
	}








	/**
	 * Gets the original pre-conversion `post_content` with the applied filter 'the_content'.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string|null Post content, or null.
	 */
	public function get_post_content( $post_id ) {
		global $wpdb;

		$post_content = $wpdb->get_var( $wpdb->prepare( "SELECT post_content FROM {$wpdb->posts} WHERE ID = %d;", $post_id ) );
		$post_content_filtered = $this->patcher_handler->run_all_preconversion_patches( $post_content );

		return $post_content_filtered;
	}

	/**
	 * Updates converted post content, but only if resulting block content is not empty.
	 * Also creates a backup of original post content as post meta.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $html_content HTML content.
	 * @param string $blocks_content Blocks content.
	 */
	public function update_converted_post( $post_id, $html_content, $blocks_content ) {
		global $wpdb;

		if ( ! $post_id || ! $html_content || ! $blocks_content ) {
			return;
		}

		$blocks_content_patched = $this->patcher_handler->run_all_patches( $html_content, $blocks_content );

		/**
		 * Back up original post_content as post meta.
		 */
		$current_post_content = $wpdb->get_var( $wpdb->prepare( "SELECT post_content FROM {$wpdb->posts} WHERE ID = %d;", $post_id ) );
		add_post_meta( $post_id, 'ncc_original_post_content_blocks', $current_post_content );

		/**
		 * Update post_content.
		 */
		if ( ! empty( $blocks_content_patched ) ) {
			$wpdb->update( $wpdb->posts, [ 'post_content' => $blocks_content_patched ], [ 'ID' => $post_id ] );
		}
	}

	/**
	 * Updates conversion queue table.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data Column=>value data for update.
	 */
	private function update_ncc_posts_table( $post_id, $data ) {
		global $wpdb;

		$table_name  = Config::get_instance()->get( 'table_name' );
		$date        = new \DateTime();
		$time_ts     = $date->format( 'Y-m-d H:i:s' );
		$time_gmt_ts = get_gmt_from_date( $time_ts );
		$timestamps  = [
			'post_modified'     => $time_ts,
			'post_modified_gmt' => $time_gmt_ts,
		];

		// phpcs:ignore -- OK to query DB directly.
		$wpdb->update( $table_name, array_merge( $data, $timestamps ), [ 'ID' => $post_id ] );
	}

	/**
	 * Checks whether conversion is queued/active.
	 *
	 * @return bool Is queued or not.
	 */
	public function is_conversion_running() {
		get_option( 'ncc-conversion_queued_batches_csv', [] );
		if ( ! empty( $queued_batches ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Fetches conversion batches in queue.
	 *
	 * @return array Array of integers, queued batches.
	 */
	public function get_conversion_queued_batches() {
		$queued_batches = get_option( 'ncc-conversion_queued_batches_csv', [] );
		if ( ! empty( $queued_batches ) ) {
			// Option field contains CSV of integers.
			$queued_batches = array_map( 'intval', explode( ',', $queued_batches ) );
		}

		return $queued_batches;
	}

	/**
	 * Gets total count of already converted Posts.
	 *
	 * @return int|null Number of converted posts, or null.
	 */
	public function get_posts_converted_count() {
		global $wpdb;

		$table_name = esc_sql( Config::get_instance()->get( 'table_name' ) );

		// phpcs:ignore -- the following is a false positive; this SQL is safe, and the table name is escaped above.
		$results = $wpdb->get_results( "SELECT COUNT(*) as count_converted FROM {$table_name} WHERE post_content_gutenberg_converted <> '';" );
		if ( ! $results ) {
			return null;
		}

		return isset( $results[0]->count_converted ) ? $results[0]->count_converted : null;
	}

	/**
	 * Gets the total count of Posts that weren't converted yet.
	 *
	 * @return int|null Count of queued posts not converted yet.
	 */
	public function get_incomplete_conversions_count() {
		global $wpdb;

		$table_name = esc_sql( Config::get_instance()->get( 'table_name' ) );

		// phpcs:ignore -- the following is a false positive; this SQL is safe, and the table name is escaped above.
		$results = $wpdb->get_results( "SELECT COUNT(*) as count_incomplete FROM {$table_name} WHERE post_content_gutenberg_converted = '';" );
		if ( ! $results ) {
			return null;
		}

		return isset( $results[0]->count_incomplete ) ? $results[0]->count_incomplete : null;
	}

	/**
	 * Unsets the `retry_conversion` column for IDs.
	 *
	 * @param array $ids An array of IDs.
	 *
	 * @return bool
	 */
	private function unset_retry_conversion_flag_for_ids( $ids ) {
		if ( ! is_array( $ids ) || empty( $ids ) ) {
			return false;
		}

		global $wpdb;

		$table_name = Config::get_instance()->get( 'table_name' );
		$table_name = esc_sql( $table_name );

		$ids_placeholders     = array_fill( 0, count( $ids ), '%d' );
		$ids_placeholders_csv = implode( ',', $ids_placeholders );
		$sql_placeholders     = "UPDATE {$table_name} SET `retry_conversion` = NULL WHERE ID IN ( $ids_placeholders_csv ) ;";
		// phpcs:ignore -- false positive, all params are fully sanitized.
		$wpdb->get_results( $wpdb->prepare( $sql_placeholders, array_merge( $ids ) ) );
	}

	/**
	 * Adds a batch number to the batch queue.
	 *
	 * @param int   $this_batch Current batch number.
	 * @param array $queued_batches Batches currently in queue.
	 */
	private function add_batch_to_coversion_queue( $this_batch, $queued_batches ) {
		$new_queued_batches     = array_merge( $queued_batches, [ $this_batch ] );
		$new_queued_batches_csv = implode( ',', $new_queued_batches );
		update_option( 'ncc-conversion_queued_batches_csv', $new_queued_batches_csv );
	}


	/**
	 * Resets any ongoing conversions.
	 */
	public function reset_ongoing_conversion() {
		$this->reset_conversion();
	}

	/**
	 * Resets it if there's an ongoing conversion of all content.
	 */
	private function reset_conversion() {
		delete_option( 'ncc-conversion_queued_batches_csv' );
	}
}
