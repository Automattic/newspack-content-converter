<?php
/**
 * Conversion processor class.
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

use \NewspackContentConverter\ContentPatcher\PatchHandlerInterface;

/**
 * Class ConversionProcessor
 *
 * @package NewspackContentConverter
 */
class ConversionProcessor {

	/**
	 * Once "Run conversion" is clicked, the following options are created.
	 */
	// IDs to be converted in each batch are stored here. Use sprintf() to get the option name according to batch number. Using %s instead of %d so that it can be used in WHERE LIKE clause.
	const OPTION_QUEUED_BATCHES_SPRINTF = 'ncc_conversion_ids_csv_batch_%s';
	// Total number of batches in this conversion run.
	const OPTION_TOTAL_BATCHES = 'ncc_conversion_total_batches';
	// Total number of IDs to conver in this run.
	const OPTION_TOTAL_IDS = 'ncc_conversion_total_ids';

	/**
	 * Batches here are just integer numbers, i.e. 1st, 2nd, 3rd batch, ... When a browser tab starts converting IDs in a batch, that batch gets added to this option
	 * so the app can tell which batches have already started running.
	 * @var string self::OPTION_CONVERSION_BATCHES_RUNNING CSV of batch numbers that have started converting.
	 */
	const OPTION_CONVERSION_BATCHES_RUNNING = 'ncc_conversion_batches_csv_queue';

	/**
	 * Post meta.
	 */
	// Postmeta backup of the original converted post content.
	const POSTMETA_ORIGINAL_POST_CONTENT = 'ncc_post_content_original';

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
	public function get_conversion_post_types() {
		return ['post', 'page'];
	}

	/**
	 * Gets content type statuses to be processed by the plugin.
	 *
	 * @return array Content statuses.
	 */
	public function get_conversion_post_statuses() {
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
	 * Gets post IDs for conversion for a batch. Excludes posts that begin with blocks code.
	 */
	public function get_all_unconverted_ids() {
		global $wpdb;

		$post_types    = $this->get_conversion_post_types();
		$types_placeholders    = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );

		$post_statuses = $this->get_conversion_post_statuses();
		$statuses_placeholders = implode( ',', array_fill( 0, count( $post_statuses ), '%s' ) );

		// Get IDs. Exclude posts that begin with block code.
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID
				FROM {$wpdb->posts} wp
				-- Select desired post types.
				WHERE post_type IN ( {$types_placeholders} )
				AND post_status IN ( {$statuses_placeholders} )
				-- Filter out post which are already in blocks.
				AND post_content NOT LIKE '<!-- wp:%' ;",
				array_merge( $post_types, $post_statuses )
			)
		);

		return $ids;
	}


	/**
	 * Gets the successfully converted post IDs.
	 *
	 * @param array $post_statuses
	 * @param array $post_types
	 * @return void
	 */
	public function get_all_successfully_converted_ids() {
		global $wpdb;

		$post_statuses = $this->get_conversion_post_statuses();
		$statuses_placeholders = implode( ',', array_fill( 0, count( $post_statuses ), '%s' ) );

		$post_types    = $this->get_conversion_post_types();
		$types_placeholders    = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );

		// Excludes those that have already been converted and empty ones.
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT wp.ID
				FROM {$wpdb->posts} wp
				JOIN {$wpdb->postmeta} wpm
					ON wpm.post_id = wp.ID
					AND wpm.meta_key = %s
				WHERE wp.post_type IN ( {$types_placeholders} )
				AND wp.post_status IN ( {$statuses_placeholders} )
				-- Either unconverted posts (no meta) or converted but make sure they were converted successfully (wp_posts.post_content doesn't get updated if conversion syntax is empty, so it's kept the same).
				AND (
					wpm.meta_value IS NULL
					OR wpm.meta_value <> wp.post_content
				)
				; ",
				array_merge( [ self::POSTMETA_ORIGINAL_POST_CONTENT ], $post_types, $post_statuses )
			)
		);

		return $ids;
	}

	public function get_all_unsuccessfully_converted_ids() {
		global $wpdb;

		$post_statuses = $this->get_conversion_post_statuses();
		$statuses_placeholders = implode( ',', array_fill( 0, count( $post_statuses ), '%s' ) );

		$post_types    = $this->get_conversion_post_types();
		$types_placeholders    = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );

		// Excludes those that have already been converted and empty ones.
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT wp.ID
				FROM {$wpdb->posts} wp
				-- Get posts with the meta.
				JOIN {$wpdb->postmeta} wpm
				  ON wpm.post_id = wp.ID
				  AND wpm.meta_key = %s
				WHERE wp.post_type IN ( {$types_placeholders} )
				AND wp.post_status IN ( {$statuses_placeholders} )
				-- Fetch posts where conversion happened (meta exists) but post_content is still the same as original post content (wp_posts.post_content does not get updated/saved if the conversion returns an empty result i.e. was unsuccessful).
				AND wpm.meta_value = wp.post_content ;",
				array_merge( [ self::POSTMETA_ORIGINAL_POST_CONTENT ], $post_types, $post_statuses )
			)
		);

		return $ids;
	}

	/**
	 * Sets the next batch to queue.
	 * If all batches are processed, deletes all the conversion options and returns null.
	 *
	 * If all batches are processed, returns null.
	 *
	 * @return int|null Number of this batch, or null if no more batches to process.
	 */
	public function get_and_set_next_batch_to_queue() {

		// Get batches from queue.
		$batches_in_queue = empty( get_option( self::OPTION_CONVERSION_BATCHES_RUNNING ) ) ? [] : explode( ',', get_option( self::OPTION_CONVERSION_BATCHES_RUNNING ) );

		// Get new batch number.
		$next_batch = empty( $batches_in_queue ) ? 1 : max( $batches_in_queue ) + 1;

		// Get total number of batches.
		$total_batches = get_option( self::OPTION_TOTAL_BATCHES );

		// If all the batches were processed, delete all conversion options.
		if ( $next_batch > $total_batches ) {
			$this->delete_all_conversion_options();

			return null;
		}

		// Immediately add this batch to the queue.
		$new_queued_batches     = array_merge( $batches_in_queue, [ $next_batch ] );
		update_option( self::OPTION_CONVERSION_BATCHES_RUNNING, implode( ',', $new_queued_batches ) );

		return $next_batch;
	}

	/**
	 * Deletes all conversion batches options.
	 */
	public function delete_all_conversion_options() {
		global $wpdb;

		delete_option( self::OPTION_TOTAL_BATCHES );
		delete_option( self::OPTION_TOTAL_IDS );
		delete_option( self::OPTION_CONVERSION_BATCHES_RUNNING );
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s;",
			sprintf( self::OPTION_QUEUED_BATCHES_SPRINTF, '%' )
		) );
	}

	/**
	 * Creates all options needed to start the conversion.
	 */
	public function prepare_conversion() {
		// Clean up all options.
		$this->delete_all_conversion_options();

		// Store empty option self::OPTION_CONVERSION_BATCHES_RUNNING. When batches start getting converted, they will get added here as CSVs.
		add_option( self::OPTION_CONVERSION_BATCHES_RUNNING, '' );

		// Get all the IDs that need to be converted.
		$ids = $this->get_all_unconverted_ids();

		// Store option total number of unconverted IDs.
		$total_ids = count( $ids );
		add_option( self::OPTION_TOTAL_IDS, $total_ids );

		// Store option total number of batches.
		$total_batches = (int) ceil( $total_ids / $this->get_conversion_batch_size() );
		add_option( self::OPTION_TOTAL_BATCHES, $total_batches );

		// Split IDs into batches.
		$ids_batches = [];
		$batch_size = $this->get_conversion_batch_size();
		$batch_number = 0;
		for ($i = 0; $i < count( $ids ); $i += $batch_size) {
			$batch_number++;
			$ids_batches[ $batch_number ] = array_slice( $ids, $i, $batch_size );
		}

		// Store multiple options, one for each batch number containing CSVs of IDs to be converted by that batch.
		foreach ( $ids_batches as $batch_number => $ids ) {
			$option_name = sprintf( self::OPTION_QUEUED_BATCHES_SPRINTF, $batch_number );
			add_option( $option_name, implode( ',', $ids ) );
		}
	}

	/**
	 * Checks if conversion has been prepared and batches can be converted (either no batches have been converted yet, or some have, this just checks if batches have been prepared in options).
	 *
	 * @return boolean
	 */
	public function is_conversion_prepared() {
		// Check if option
		$exists_option_batches_running = false !== get_option( self::OPTION_CONVERSION_BATCHES_RUNNING );
		$exists_option_total_batches = (bool) get_option( self::OPTION_TOTAL_BATCHES );
		$exists_option_total_ids = (bool) get_option( self::OPTION_TOTAL_IDS );

		if ( $exists_option_batches_running && $exists_option_total_batches && $exists_option_total_ids ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if conversion is running, by checking if any batches have already been put to queue option.
	 *
	 * @return boolean
	 */
	public function is_conversion_running() {
		$batches_running_queue = get_option( self::OPTION_CONVERSION_BATCHES_RUNNING );

		if ( false !== $batches_running_queue && ! empty( $batches_running_queue ) ) {
			return true;
		}

		return false;
	}

	public function get_total_number_of_batches() {
		return get_option( self::OPTION_TOTAL_BATCHES );
	}

	public function get_ids_for_batch( int $batch ): array {
		$option_name = sprintf( self::OPTION_QUEUED_BATCHES_SPRINTF, $batch );
		$ids_csv = get_option( $option_name );
		$ids = explode( ',', $ids_csv );

		return $ids;
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
		add_post_meta( $post_id, self::POSTMETA_ORIGINAL_POST_CONTENT, $current_post_content );

		/**
		 * Update post_content.
		 */
		if ( ! empty( $blocks_content_patched ) ) {
			$wpdb->update( $wpdb->posts, [ 'post_content' => $blocks_content_patched ], [ 'ID' => $post_id ] );
		}
	}
}
