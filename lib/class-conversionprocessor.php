<?php
/**
 * Conversion processor class.
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

use NewspackContentConverter\ContentPatcher\PatchHandlerInterface;

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
	 *
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
	 * Gets content types to be processed by the plugin.
	 *
	 * @return array Content types.
	 */
	public function get_conversion_post_types() {
		$post_types_option = get_option( 'ncc_conversion_post_types', ['post'] );
		// Even though filtering could happen on the option too, we keep this filter for legacy reasons.
		return apply_filters( 'ncc_filter_conversion_post_types', $post_types_option );
	}

	/**
	 * Gets content type statuses to be processed by the plugin.
	 *
	 * @return array Content statuses.
	 */
	public function get_conversion_post_statuses() {
		$post_statuses_option = get_option( 'ncc_conversion_post_statuses', ['publish'] );
		// Even though filtering could happen on the option too, we keep this filter for legacy reasons.
		return apply_filters( 'ncc_filter_conversion_post_statuses', $post_statuses_option );
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
	 * Gets unconverted IDs (ordered DESC). Excludes posts which begin with blocks code.
	 */
	public function get_all_unconverted_ids() {
		global $wpdb;

		$post_types         = $this->get_conversion_post_types();
		$types_placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );

		$post_statuses         = $this->get_conversion_post_statuses();
		$statuses_placeholders = implode( ',', array_fill( 0, count( $post_statuses ), '%s' ) );

		$min_post_id_to_process = get_option( 'ncc_min_post_id_to_process', 0 );
		$max_post_id_to_process = get_option( 'ncc_max_post_id_to_process', PHP_INT_MAX );

		// Get unconverted IDs. Exclude posts that begin with block code.
		// phpcs:disable -- WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery.
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID
				FROM {$wpdb->posts} wp
				-- Select desired post types.
				WHERE post_type IN ( {$types_placeholders} )
				AND post_status IN ( {$statuses_placeholders} )
				AND ID BETWEEN %d AND %d
				-- Filter out post which are already in blocks.
				AND post_content NOT LIKE '<!-- wp:%'
				ORDER BY ID DESC ;",
				[
					...$post_types,
					...$post_statuses,
					$min_post_id_to_process,
					$max_post_id_to_process,
				]
			)
		);
		// phpcs:enable

		return $ids;
	}

	/**
	 * Gets successfully converted post IDs from postmeta (ordered DESC) – it would be posts that has a POSTMETA_ORIGINAL_POST_CONTENT postmeta entry.
	 *
	 * @return array Post IDs.
	 */
	public function get_all_ids_with_original_content_meta() {
		global $wpdb;

		$ids = $wpdb->get_col(
			$wpdb->prepare(
				// In case there are multiple metas for same post_id, use the most recent one with the max meta id.
				"SELECT wpm.post_id, MAX( wpm.meta_id )
				FROM {$wpdb->postmeta} wpm
				WHERE wpm.meta_key = %s
				GROUP BY wpm.post_id
				ORDER BY wpm.post_id DESC ;",
				self::POSTMETA_ORIGINAL_POST_CONTENT
			)
		);

		return $ids;
	}

	/**
	 * Gets all converted post IDs.
	 *
	 * @return array Converted post IDs.
	 */
	public function get_all_converted_ids() {
		/**
		 * If a post was converted and then restored, the postmeta with original content will be attached to it, even though
		 * this post will be unconverted. To get the actual posts which have been converted, subtract all posts with the meta
		 * with unconverted posts.
		 */
		$ids_with_meta   = $this->get_all_ids_with_original_content_meta();
		$unconverted_ids = $this->get_all_unconverted_ids();
		$converted_ids   = array_diff( $ids_with_meta, $unconverted_ids );

		return $converted_ids;
	}

	/**
	 * Sets the next batch to options queue.
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
		$new_queued_batches = array_merge( $batches_in_queue, [ $next_batch ] );
		update_option( self::OPTION_CONVERSION_BATCHES_RUNNING, implode( ',', $new_queued_batches ) );

		return $next_batch;
	}

	/**
	 * Deletes all conversion batches options (all prepared options params, including the running queue).
	 */
	public function delete_all_conversion_options() {
		global $wpdb;

		delete_option( self::OPTION_TOTAL_BATCHES );
		delete_option( self::OPTION_TOTAL_IDS );
		delete_option( self::OPTION_CONVERSION_BATCHES_RUNNING );
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s;",
				sprintf( self::OPTION_QUEUED_BATCHES_SPRINTF, '%' )
			)
		);
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
		$ids_batches  = [];
		$batch_size   = $this->get_conversion_batch_size();
		$batch_number = 0;
		for ( $i = 0; $i < $total_ids; $i += $batch_size ) {
			++$batch_number;
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
		$exists_option_batches_running = false !== get_option( self::OPTION_CONVERSION_BATCHES_RUNNING );
		$exists_option_total_batches   = (bool) get_option( self::OPTION_TOTAL_BATCHES );
		$exists_option_total_ids       = (bool) get_option( self::OPTION_TOTAL_IDS );

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

	/**
	 * Gets total number of batches to be converted.
	 *
	 * @return int|false Total number of batches to be converted, or false if conversion is not prepared.
	 */
	public function get_total_number_of_batches() {
		return get_option( self::OPTION_TOTAL_BATCHES );
	}

	/**
	 * Gets the IDs for a batch.
	 *
	 * @param integer $batch Batch number.
	 * @return array IDs for the batch.
	 */
	public function get_ids_for_batch( int $batch ): array {
		$option_name = sprintf( self::OPTION_QUEUED_BATCHES_SPRINTF, $batch );
		$ids_csv     = get_option( $option_name );
		$ids         = explode( ',', $ids_csv );

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

		/**
		 * Filters HTML $post_content before conversion to blocks.
		 *
		 * @var string $post_content HTML content before conversion.
		 * @var int    $post_id      Post ID.
		 */
		$post_content_filtered = apply_filters( 'ncc_filter_html_before_conversion', $post_content, $post_id );

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

		$current_post_content = $wpdb->get_var( $wpdb->prepare( "SELECT post_content FROM {$wpdb->posts} WHERE ID = %d;", $post_id ) );

		/**
		 * Filter $blocks_content after conversion to blocks.
		 *
		 * @var string $blocks_content       Converted blocks content.
		 * @var string $current_post_content HTML content before conversion.
		 * @var int    $post_id              Post ID.
		 */
		$blocks_content_patched = apply_filters( 'ncc_filter_blocks_after_conversion', $blocks_content, $current_post_content, $post_id );

		// Only update if resulting blocks content is not empty and has been modified.
		if ( ! empty( $blocks_content_patched ) && ( $html_content != $blocks_content_patched ) ) {
			// Back up original post_content as post meta.
			add_post_meta( $post_id, self::POSTMETA_ORIGINAL_POST_CONTENT, $current_post_content );

			// Store revision before the content has been updated.
			// No need to store revision after update since the update triggers that.
			wp_save_post_revision( $post_id );

			// Update post_content.
			wp_update_post( [
				'ID' => $post_id,
				'post_content' => $blocks_content_patched,
			] );

			/**
			 * Fires after post content has been updated.
			 *
			 * @var int    $post_id                Post ID.
			 * @var string $blocks_content_patched Blocks content after filtering.
			 * @var string $blocks_content         Blocks content before filtering.
			 * @var string $current_post_content   HTML post content before update.
			 */
			do_action( 'ncc_after_post_content_updated', $post_id, $blocks_content_patched, $blocks_content, $current_post_content );
		}
	}

	/**
	 * Restores post contents to before conversion.
	 * If post_ids are provided only those posts will be restored, otherwise all posts will be restored.
	 *
	 * @param array $post_ids Optional Post IDs. If empty, all posts will be restored.
	 * @return boolean True if successful, false otherwise.
	 */
	public function restore_post_contents_to_before_conversion( array $post_ids = [] ): bool {
		global $wpdb;

		$where_post_ids_in_clause = '';
		if ( ! empty( $post_ids ) ) {
			$post_ids_placeholders    = array_fill( 0, count( $post_ids ), '%d' );
			$where_post_ids_in_clause = sprintf( ' WHERE wp.ID IN ( %s ) ', implode( ',', $post_ids_placeholders ) );
		}

		// phpcs:disable -- WordPress.DB.PreparedSQL.NotPrepared Query is prepared.
		$query  = $wpdb->prepare(
			"UPDATE {$wpdb->posts} wp
			JOIN (
				-- In case there are multiple metas for same post_id, use the most recent one.
				SELECT post_id, MAX( meta_id ) as max_meta_id
				FROM {$wpdb->postmeta}
				WHERE meta_key = %s
				GROUP BY post_id
			) max_meta
				ON wp.ID = max_meta.post_id
			JOIN wp_postmeta wpm
				ON wpm.meta_id = max_meta.max_meta_id
			SET wp.post_content = wpm.meta_value
			{$where_post_ids_in_clause} ;",
			array_merge( [ self::POSTMETA_ORIGINAL_POST_CONTENT ], $post_ids )
		);
		$result = $wpdb->query( $query );
		// phpcs:enable

		return (bool) $result;
	}

	/**
	 * Flushes all meta backups.
	 *
	 * @return int Number of deleted rows.
	 */
	public function flush_all_meta_backups() {
		global $wpdb;

		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s;",
				self::POSTMETA_ORIGINAL_POST_CONTENT
			)
		);

		return $deleted;
	}
}
