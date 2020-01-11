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
	 * Sets the next conversion batch in motion. It fetches the current conversion queue, finds the next queue number, and adds it
	 * to the queue. If fetches and returns IDs belonging to that queue.
	 *
	 * If queue maxed out (all processed), or conversion not ongoing, returns void.
	 *
	 * @return array|void Array of IDs, or void.
	 */
	public function set_next_conversion_batch_to_queue() {
		if ( false === $this->is_queued_conversion() ) {
			return;
		}

		// Get queued batches.
		$queued_batches = $this->get_conversion_queued_batches();
		$max_batches    = $this->get_conversion_max_batch();

		// If the whole queue is processed, clear it.
		$this_batch = empty( $queued_batches ) ? 1 : max( $queued_batches ) + 1;
		if ( $this_batch > $max_batches ) {
			$this->clear_conversion_queue();

			return;
		}

		// Immediately add this batch to the queue.
		$this->add_batch_to_coversion_queue( $this_batch, $queued_batches );

		// Get IDs for conversion.
		$batch_size = $this->get_conversion_batch_size();
		$ids        = $this->select_ids_for_batch( $this_batch, $batch_size );

		return $ids;
	}

	/**
	 * Sets the next batch of Posts which previously failed to get converted to now retry their conversion. It fetches the current
	 * conversion queue, gets the next queue number, and appends it to the queue. If then fetches and returns IDs for that queue.
	 *
	 * If queue is maxed out (all Posts processed), or conversion is not ongoing, returns void.
	 *
	 * @return array|void Array of IDs, or void.
	 */
	public function set_next_retry_conversion_failed_batch_to_queue() {
		if ( false === $this->is_queued_conversion_retry_failed() ) {
			return;
		}

		// Get queued batches.
		$queued_batches = $this->get_conversion_retry_failed_queued_batches();
		$max_batches    = $this->get_conversion_retry_failed_max_batch();

		// If the whole queue is processed, reset it.
		$this_batch = empty( $queued_batches ) ? 1 : max( $queued_batches ) + 1;
		if ( $this_batch > $max_batches ) {
			$this->clear_conversion_retry_failed_queue();

			return;
		}

		// Immediately add this batch to the retry-conversion-of-failed-Posts queue, and get the IDs.
		$this->add_batch_to_coversion_retry_failed_queue( $this_batch, $queued_batches );
		$batch_size = $this->get_conversion_batch_size();
		$ids        = $this->select_ids_for_next_batch_retry_conversion_failed( $batch_size );

		return $ids;
	}

	/**
	 * Gets the original pre-conversion `post_content` (with the applied filter 'the_content') from the Plugin's queue table.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string|null Post content, or null.
	 */
	public function get_post_content( $post_id ) {
		if ( ! $post_id ) {
			return null;
		}

		global $wpdb;

		$table_name = Config::get_instance()->get( 'table_name' );
		$table_name = esc_sql( $table_name );

		// phpcs:ignore -- the following is a false positive; this SQL is safe, and the table name is escaped above.
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE ID = %d;", $post_id ) );
		if ( ! $results ) {
			return null;
		}

		$post                  = $results[0];
		$post_content_filtered = apply_filters( 'the_content', $post->post_content );

		return $post_content_filtered;
	}

	/**
	 * Gets the Post object from the plugin's queue table (equivalent to the `wp_posts` table, with extra columns).
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return object|null Post object, or null.
	 */
	public function get_ncc_post( $post_id ) {
		global $wpdb;

		if ( ! $post_id ) {
			return null;
		}

		$table_name = Config::get_instance()->get( 'table_name' );
		$table_name = esc_sql( $table_name );

		// phpcs:ignore -- the following is a false positive; this SQL is safe, and the table name is escaped above.
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE ID = %d;", $post_id ) );
		if ( ! $results ) {
			return null;
		}

		return $results[0];
	}

	/**
	 * Takes Post ID, original HTML content, Gutenberg converted blocks content, applies content patchers and saves to Post.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $html_content HTML content.
	 * @param string $blocks_content Blocks content.
	 */
	public function save_converted_post_content( $post_id, $html_content, $blocks_content ) {
		if ( ! $post_id || ! $html_content || ! $blocks_content ) {
			return;
		}

		$blocks_content_patched = $this->patcher_handler->run_all_patches( $html_content, $blocks_content );
		$this->update_ncc_posts_table( $post_id, [ 'post_content_gutenberg_converted' => $blocks_content ] );
		$this->update_posts_table( $post_id, [ 'post_content' => $blocks_content_patched ] );
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
	 * Updates the WP posts table.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data Column=>value data for update.
	 */
	private function update_posts_table( $post_id, $data ) {
		global $wpdb;

		$date        = new \DateTime();
		$time_ts     = $date->format( 'Y-m-d H:i:s' );
		$time_gmt_ts = get_gmt_from_date( $time_ts );
		$timestamps  = [
			'post_modified'     => $time_ts,
			'post_modified_gmt' => $time_gmt_ts,
		];

		// phpcs:ignore -- OK to query DB directly.
		$wpdb->update( $wpdb->posts, array_merge( $data, $timestamps ), [ 'ID' => $post_id ] );
	}

	/**
	 * Checks whether conversion is queued/active.
	 *
	 * @return bool Is queued or not.
	 */
	public function is_queued_conversion() {
		$conversion_queued = get_option( Config::get_instance()->get( 'option_is_queued_conversion' ), false );
		if ( '1' === $conversion_queued ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks whether the Retry conversion of failed Posts is queued.
	 *
	 * @return bool Is queued or not.
	 */
	public function is_queued_conversion_retry_failed() {
		$conversion_queued = get_option( Config::get_instance()->get( 'option_is_queued_retry_failed_conversion' ), false );
		if ( '1' === $conversion_queued ) {
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
		$queued_batches = get_option( Config::get_instance()->get( 'option_conversion_queued_batches' ), [] );
		if ( ! empty( $queued_batches ) ) {
			// Option field contains CSV of integers.
			$queued_batches = array_map( 'intval', explode( ',', $queued_batches ) );
		}

		return $queued_batches;
	}

	/**
	 * Fetches the queued batches for retrying the conversion of failed Posts.
	 *
	 * @return array The queued batches.
	 */
	public function get_conversion_retry_failed_queued_batches() {
		$queued_batches = get_option( Config::get_instance()->get( 'option_retry_conversion_failed_queued_batches' ), [] );
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
	 * Checks whether there are any unconverted posts left.
	 *
	 * @return bool Are there any incomplete conversions left.
	 */
	public function has_incomplete_conversions() {
		global $wpdb;

		$table_name = esc_sql( Config::get_instance()->get( 'table_name' ) );

		// phpcs:ignore -- the following is a false positive; this SQL is safe, and the table name is escaped above.
		$results = $wpdb->get_results( "SELECT COUNT(*) as count_incomplete FROM {$table_name} WHERE post_content_gutenberg_converted = '';" );
		if ( ! $results ) {
			return false;
		}

		return isset( $results[0]->count_incomplete ) && $results[0]->count_incomplete > 0 ? true : false;
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
	 * Gets max conversion batch.
	 *
	 * @return int|null Batch number.
	 */
	public function get_conversion_max_batch() {
		$max_batches = get_option( Config::get_instance()->get( 'option_conversion_max_batches' ), null );

		return null == $max_batches ? null : (int) $max_batches;
	}

	/**
	 * Gets the max batch number for the retry-conversion-of-failed-posts queue.
	 *
	 * @return int|null Batch number.
	 */
	public function get_conversion_retry_failed_max_batch() {
		$max_batches = get_option( Config::get_instance()->get( 'option_retry_conversion_failed_max_batches' ), null );

		return null == $max_batches ? null : (int) $max_batches;
	}

	/**
	 * Gets content types to be processed by the plugin.
	 *
	 * @return string|null CSV, or null.
	 */
	public function get_conversion_content_types() {
		return get_option( Config::get_instance()->get( 'option_conversion_post_types_csv' ), null );
	}

	/**
	 * Gets content type statuses to be processed by the plugin.
	 *
	 * @return string|null CSV, or null.
	 */
	public function get_conversion_content_statuses() {
		return get_option( Config::get_instance()->get( 'option_conversion_post_statuses_csv' ), null );
	}

	/**
	 * Gets the number of posts/content processed by a conversion batch.
	 *
	 * @return int|null Number of posts/content processed by a conversion batch.
	 */
	public function get_conversion_batch_size() {
		$batch_size = get_option( Config::get_instance()->get( 'option_conversion_batch_size' ), null );

		return null == $batch_size ? null : (int) $batch_size;
	}

	/**
	 * Gets total number of entries (posts) configured for conversion.
	 *
	 * @return int|false Total number of entries.
	 */
	public function get_queued_entries_total_number() {
		global $wpdb;

		$table_name = Config::get_instance()->get( 'table_name' );
		$table_name = esc_sql( $table_name );

		// phpcs:ignore -- the following is a false positive; this SQL is safe, and the table name is escaped above.
		$results = $wpdb->get_results( "SELECT COUNT(*) as total FROM `$table_name` ;" );

		return isset( $results[0]->total ) ? (int) $results[0]->total : false;
	}

	/**
	 * Initalize conversion by flagging it to "queued".
	 *
	 * @return bool Is initialized.
	 */
	public function initialize_conversion() {
		$this->clear_conversion_queue();
		$set = $this->set_conversion_queue();

		return $set;
	}

	/**
	 * Set conversion flag up.
	 *
	 * @return bool Success.
	 */
	private function set_conversion_queue() {
		return update_option( Config::get_instance()->get( 'option_is_queued_conversion' ), 1 );
	}

	/**
	 * Clears the conversion queue.
	 */
	private function clear_conversion_queue() {
		update_option( Config::get_instance()->get( 'option_is_queued_conversion' ), 0 );
		delete_option( Config::get_instance()->get( 'option_conversion_queued_batches' ) );
	}

	/**
	 * Initializes retrying conversion of failed Posts.
	 *
	 * @return bool Is initialized.
	 */
	public function initialize_conversion_retry_failed() {
		$this->clear_conversion_retry_failed_queue();
		$set = $this->set_conversion_retry_failed_queue();
		$this->set_retry_conversion_flags_for_failed_posts();
		$set = $set && $this->set_retry_conversion_max_batches();

		return $set;
	}

	/**
	 * Sets the option value 'option_retry_conversion_failed_max_batches', saying how many batches are there in the
	 * retry-converting-failed-posts queue.
	 *
	 * @return bool
	 */
	private function set_retry_conversion_max_batches() {
		global $wpdb;

		$table_name = esc_sql( Config::get_instance()->get( 'table_name' ) );
		$batch_size = get_option( Config::get_instance()->get( 'option_conversion_batch_size' ), null );

		// phpcs:ignore -- OK to query DB directly.
		$total = $wpdb->get_var("SELECT COUNT(*) as total FROM $table_name WHERE `retry_conversion` = 1 ; ");

		$max_batches = (int) ceil( $total / $batch_size );

		return update_option( Config::get_instance()->get( 'option_retry_conversion_failed_max_batches' ), $max_batches );
	}

	/**
	 * Set the 'is_queued' flag up for Retry converting failed Posts.
	 *
	 * @return bool Success.
	 */
	private function set_conversion_retry_failed_queue() {
		return update_option( Config::get_instance()->get( 'option_is_queued_retry_failed_conversion' ), 1 );
	}

	/**
	 * Clears the retry converting failed Posts queue.
	 */
	private function clear_conversion_retry_failed_queue() {
		update_option( Config::get_instance()->get( 'option_is_queued_retry_failed_conversion' ), 0 );
		delete_option( Config::get_instance()->get( 'option_retry_conversion_failed_queued_batches' ) );
		delete_option( Config::get_instance()->get( 'option_retry_conversion_failed_max_batches' ) );
	}

	/**
	 * Sets the `retry_conversion` column for failed posts, meaning that they become queued for a conversion retry.
	 */
	private function set_retry_conversion_flags_for_failed_posts() {
		global $wpdb;

		$table_name = Config::get_instance()->get( 'table_name' );
		$table_name = esc_sql( $table_name );

		// phpcs:ignore -- allow a direct call here.
		$wpdb->update( $table_name, [ 'retry_conversion' => 1 ], [ 'post_content_gutenberg_converted' => '' ] );
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
		update_option( Config::get_instance()->get( 'option_conversion_queued_batches' ), $new_queued_batches_csv );
	}

	/**
	 * Adds a batch number to the retry conversion of failed Posts batch queue.
	 *
	 * @param int   $this_batch Current batch number.
	 * @param array $queued_batches Batches currently in queue.
	 */
	private function add_batch_to_coversion_retry_failed_queue( $this_batch, $queued_batches ) {
		$new_queued_batches     = array_merge( $queued_batches, [ $this_batch ] );
		$new_queued_batches_csv = implode( ',', $new_queued_batches );
		update_option( Config::get_instance()->get( 'option_retry_conversion_failed_queued_batches' ), $new_queued_batches_csv );
	}

	/**
	 * Fetches posts IDs for a batch.
	 *
	 * @param int $this_batch Batch number.
	 * @param int $batch_size Batch size (number of posts/entries per batch).
	 *
	 * @return array Post IDs.
	 */
	private function select_ids_for_batch( $this_batch, $batch_size ) {
		global $wpdb;

		$table_name = Config::get_instance()->get( 'table_name' );
		$table_name = esc_sql( $table_name );
		$this_batch = esc_sql( $this_batch );
		$batch_size = esc_sql( $batch_size );

		$offset        = ( $this_batch - 1 ) * $batch_size;
		$query_prepare = "SELECT ID FROM $table_name ORDER BY ID ASC LIMIT %d OFFSET %d ;";
		// phpcs:ignore -- the following is a false positive; this SQL is safe, and the table name is escaped above.
		$results = $wpdb->get_results( $wpdb->prepare( $query_prepare, $batch_size, $offset ) );

		$ids = [];
		foreach ( $results as $result ) {
			$ids[] = $result->ID;
		}

		return $ids;
	}

	/**
	 * Fetches posts IDs for the batch of Retrying conversion of failed Posts.
	 *
	 * @param int $batch_size Batch size.
	 *
	 * @return array Post IDs.
	 */
	private function select_ids_for_next_batch_retry_conversion_failed( $batch_size ) {
		global $wpdb;

		$table_name = Config::get_instance()->get( 'table_name' );
		$table_name = esc_sql( $table_name );
		$batch_size = esc_sql( $batch_size );

		$query_prepare = "SELECT ID FROM $table_name WHERE `retry_conversion` = 1 ORDER BY ID ASC LIMIT %d ;";
		// phpcs:ignore -- the following is a false positive; this SQL is safe, and the table name is escaped above.
		$results = $wpdb->get_results( $wpdb->prepare( $query_prepare, $batch_size ) );

		$ids = [];
		foreach ( $results as $result ) {
			$ids[] = $result->ID;
		}

		// Unset `retry_conversion` flag for IDs which have just been picked up.
		$this->unset_retry_conversion_flag_for_ids( $ids );

		return $ids;
	}

	/**
	 * Resets any ongoing conversions.
	 */
	public function reset_ongoing_conversion() {
		$this->reset_conversion();
		$this->reset_conversion_retry_failed();
	}

	/**
	 * Resets it if there's an ongoing conversion of all content.
	 */
	private function reset_conversion() {
		delete_option( Config::get_instance()->get( 'option_is_queued_conversion' ) );
		delete_option( Config::get_instance()->get( 'option_conversion_queued_batches' ) );
	}

	/**
	 * Resets it if there's an ongoing retry-conversion-of-failed-posts.
	 */
	private function reset_conversion_retry_failed() {
		delete_option( Config::get_instance()->get( 'option_is_queued_retry_failed_conversion' ) );
		delete_option( Config::get_instance()->get( 'option_retry_conversion_failed_queued_batches' ) );
		delete_option( Config::get_instance()->get( 'option_retry_conversion_failed_max_batches' ) );
	}
}
