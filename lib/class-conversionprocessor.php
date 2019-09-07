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
		if ( false === $this->is_conversion_queued() ) {
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
	 * Sets the next patching batch in motion. It fetches the current patching queue, finds the next queue number, and adds it
	 * to the queue. If returns that queue number.
	 *
	 * If queue maxed out (all processed), or patching not ongoing, returns false.
	 *
	 * @return int|bool Current batch (which was just added to the queue), false if patching not queued, or max batches reached.
	 */
	public function move_next_patching_batch_to_queue() {
		if ( false === $this->is_patching_queued() ) {
			return false;
		}

		// Get queued batches.
		$queued_batches = $this->get_patching_queued_batches();
		$max_batches    = $this->get_patching_max_batch();

		// If the whole queue is processed, clear it.
		$this_batch = empty( $queued_batches ) ? 1 : max( $queued_batches ) + 1;
		if ( $this_batch > $max_batches ) {
			$this->clear_patching_queue();

			return false;
		}

		// Immediately add this batch to the queue.
		$this->add_batch_to_patching_queue( $this_batch, $queued_batches );

		return $this_batch;
	}

	/**
	 * Gets the original pre-conversion `post_content` (with the applied filter 'the_content') from the Plugin's queue table.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string|null Post content, or null.
	 */
	public function get_post_content_by_id( $post_id ) {
		if ( ! $post_id ) {
			return null;
		}

		global $wpdb;

		$table_name = Config::get_instance()->get( 'table_name' );
		$table_name = esc_sql( $table_name );
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

		$results = $wpdb->get_results( "SELECT * FROM {$table_name} WHERE ID = $post_id;" );
		if ( ! $results ) {
			return null;
		}

		return $results[0];
	}

	/**
	 * Applies patchers to all posts in specified batch.
	 *
	 * @param int $batch_number Batch number.
	 *
	 * @return bool Success.
	 */
	public function apply_patches_to_batch( $batch_number ) {
		$batch_size = $this->get_patching_batch_size();
		$post_ids   = $this->select_ids_for_batch( $batch_number, $batch_size );

		foreach ( $post_ids as $post_id ) {
			$ncc_post = $this->get_ncc_post( $post_id );
			if ( ! $ncc_post ) {
				return false;
			}

			$html_content           = apply_filters( 'the_content', $ncc_post->post_content );
			$blocks_content         = $ncc_post->post_content_gutenberg_converted;
			$blocks_content_patched = $this->patcher_handler->run_all_patches( $html_content, $blocks_content );

			$this->update_posts_table( $post_id, [ 'post_content' => $blocks_content_patched ] );
		}

		return true;
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

		$wpdb->update( $wpdb->posts, array_merge( $data, $timestamps ), [ 'ID' => $post_id ] );
	}

	/**
	 * Checks whether conversion is queued/active.
	 *
	 * @return bool Is queued or not.
	 */
	public function is_conversion_queued() {
		$conversion_queued = get_option( Config::get_instance()->get( 'option_conversion_is_queued' ), false );
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
	 * Gets max conversion batch.
	 *
	 * @return int|null Batch number.
	 */
	public function get_conversion_max_batch() {
		$max_batches = get_option( Config::get_instance()->get( 'option_conversion_max_batches' ), null );

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
	 * Checks whether patching is queued/active.
	 *
	 * @return bool Is queued or not.
	 */
	public function is_patching_queued() {
		$patching_queued = get_option( Config::get_instance()->get( 'option_patching_is_queued' ), false );
		if ( '1' === $patching_queued ) {
			return true;
		}

		return false;
	}

	/**
	 * Fetches patching batches in queue.
	 *
	 * @return array Array of integers, queued batches.
	 */
	public function get_patching_queued_batches() {
		$queued_batches = get_option( Config::get_instance()->get( 'option_patching_queued_batches' ), [] );
		if ( ! empty( $queued_batches ) ) {
			// Option field contains CSV of integers.
			$queued_batches = array_map( 'intval', explode( ',', $queued_batches ) );
		}

		return $queued_batches;
	}

	/**
	 * Gets max patching batch.
	 *
	 * @return int|null Batch number.
	 */
	public function get_patching_max_batch() {
		$max_batches = get_option( Config::get_instance()->get( 'option_patching_max_batches' ), null );

		return null == $max_batches ? null : (int) $max_batches;
	}

	/**
	 * Gets the number of posts/content processed by a patching batch.
	 *
	 * @return int|null Number of posts/content processed by a patching batch.
	 */
	public function get_patching_batch_size() {
		$batch_size = get_option( Config::get_instance()->get( 'option_patching_batch_size' ), null );

		return null == $batch_size ? null : (int) $batch_size;
	}

	/**
	 * Gets total number of entries (posts) configured for conversion/patching.
	 *
	 * @return int|false Total number of entries.
	 */
	public function get_queued_entries_total_number() {
		global $wpdb;

		$table_name = Config::get_instance()->get( 'table_name' );
		$table_name = esc_sql( $table_name );

		$results = $wpdb->get_results( "SELECT COUNT(*) as total FROM {$table_name} ;" );

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
		return update_option( Config::get_instance()->get( 'option_conversion_is_queued' ), 1 );
	}

	/**
	 * Clears the conversion queue.
	 */
	private function clear_conversion_queue() {
		update_option( Config::get_instance()->get( 'option_conversion_is_queued' ), 0 );
		delete_option( Config::get_instance()->get( 'option_conversion_queued_batches' ) );
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
	 * Fetches posts IDs for a batch.
	 *
	 * @param int $this_batch Batch number.
	 * @param int $batch_size Size of a batch (number of posts/entries per batch).
	 *
	 * @return array Post IDs.
	 */
	private function select_ids_for_batch( $this_batch, $batch_size ) {
		global $wpdb;

		$table_name = Config::get_instance()->get( 'table_name' );
		$table_name = esc_sql( $table_name );
		$batch_size = esc_sql( $batch_size );

		$offset  = ( $this_batch - 1 ) * $batch_size;
		$results = $wpdb->get_results( "SELECT ID FROM {$table_name} ORDER BY ID ASC LIMIT $batch_size OFFSET $offset ;" );

		$ids = [];
		foreach ( $results as $result ) {
			$ids[] = $result->ID;
		}

		return $ids;
	}

	/**
	 * Initialize patching.
	 *
	 * @return bool Initialized.
	 */
	public function initialize_patching() {
		$this->clear_patching_queue();
		$set = $this->set_patching_queue();

		return $set;
	}

	/**
	 * Clear patching queue.
	 */
	private function clear_patching_queue() {
		update_option( Config::get_instance()->get( 'option_patching_is_queued' ), 0 );
		delete_option( Config::get_instance()->get( 'option_patching_queued_batches' ) );
	}

	/**
	 * Set patching flag up.
	 *
	 * @return bool Success.
	 */
	private function set_patching_queue() {
		return update_option( Config::get_instance()->get( 'option_patching_is_queued' ), 1 );
	}

	/**
	 * Adds a batch to the processing queue.
	 *
	 * @param int   $this_batch Batch to be added to the queue.
	 * @param array $queued_batches Batches currently in queue.
	 */
	private function add_batch_to_patching_queue( $this_batch, $queued_batches ) {
		$new_queued_batches     = array_merge( $queued_batches, [ $this_batch ] );
		$new_queued_batches_csv = implode( ',', $new_queued_batches );
		update_option( Config::get_instance()->get( 'option_patching_queued_batches' ), $new_queued_batches_csv );
	}
}
