<?php
/**
 * Main controller class.
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

use \WP_REST_Controller;
use \WP_REST_Server;
use \NewspackContentConverter\ConversionProcessor;

/**
 * Class ConverterController
 *
 * @package Newspack
 */
class ConverterController extends WP_REST_Controller {

	/**
	 * The conversion processor service.
	 *
	 * @var ConversionProcessor
	 */
	private $conversion_processor;

	/**
	 * ConverterController constructor.
	 *
	 * @param \NewspackContentConverter\ConversionProcessor $conversion_processor Conversion processor service.
	 */
	public function __construct( ConversionProcessor $conversion_processor ) {
		$this->conversion_processor = $conversion_processor;
	}

	/**
	 * Registers the routes.
	 */
	public function register_routes() {
		$namespace = 'newspack-content-converter';

		// Fetches info for the settings page.
		register_rest_route(
			$namespace,
			'/settings/get-info',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_settings_info' ],
				'permission_callback' => [ $this, 'rest_permission' ],
			]
		);

		// Fetches info for the conversion page.
		register_rest_route(
			$namespace,
			'/conversion/get-info',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_conversion_info' ],
				'permission_callback' => [ $this, 'rest_permission' ],
			]
		);

		// Initializes the conversion queue.
		register_rest_route(
			$namespace,
			'/conversion/initialize',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'initialize_conversion' ],
				'permission_callback' => [ $this, 'rest_permission' ],
			]
		);

		// Fetches a batch to be converted to blocks.
		register_rest_route(
			$namespace,
			'/conversion/get-batch-data',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_conversion_batch_data' ],
				'permission_callback' => [ $this, 'rest_permission' ],
			]
		);

		// Initializes retry converting failed posts.
		register_rest_route(
			$namespace,
			'/conversion-retry-failed/initialize',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'initialize_conversion_retry_failed' ],
				'permission_callback' => [ $this, 'rest_permission' ],
			]
		);

		// Fetches a batch of failed posts to retry conversion.
		register_rest_route(
			$namespace,
			'/conversion-retry-failed/get-batch-data',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_conversion_retry_failed_batch_data' ],
				'permission_callback' => [ $this, 'rest_permission' ],
			]
		);

		// Resets any ongoing conversion.
		register_rest_route(
			$namespace,
			'/conversion/reset',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'reset_conversion' ],
				'permission_callback' => [ $this, 'rest_permission' ],
			]
		);

		// Fetches post_content.
		register_rest_route(
			$namespace,
			'/get-post-content/(?P<id>\d+)',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_post_content' ],
				'args'                => [ 'id' ],
				'permission_callback' => [ $this, 'rest_permission' ],
			]
		);

		// Updates the converted Post content.
		register_rest_route(
			$namespace,
			'/conversion/update-post',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'update_post_content' ],
				'permission_callback' => [ $this, 'rest_permission' ],
			]
		);
	}

	/**
	 * Permission check, common to basic Converter endpoints.
	 *
	 * @return bool|WP_Error
	 */
	public function rest_permission() {
		$is_user_authorized = current_user_can( 'edit_posts' );

		if ( ! $is_user_authorized ) {
			return new WP_Error( 'newspack_content_converter_rest_invalid_permission', __( 'Unauthorized access.' ) );
		}

		return true;
	}

	/**
	 * Callback for the /settings/get-info route.
	 * Fetches info for the settings page.
	 *
	 * @return array Info for the settings page.
	 */
	public function get_settings_info() {
		return rest_ensure_response(
			[
				'conversionContentTypesCsv'    => $this->conversion_processor->get_conversion_content_types(),
				'conversionContentStatusesCsv' => $this->conversion_processor->get_conversion_content_statuses(),
				'conversionBatchSize'          => $this->conversion_processor->get_conversion_batch_size(),
				'queuedEntries'                => $this->conversion_processor->get_queued_entries_total_number(),
			]
		);
	}

	/**
	 * Callback for the /conversion/get-info route.
	 * Fetches info for the conversion page.
	 *
	 * @return array Info for the settings page.
	 */
	public function get_conversion_info() {
		$is_conversion_ongoing = $this->conversion_processor->is_queued_conversion()
									|| $this->conversion_processor->is_queued_conversion_retry_failed()
			? '1' : '0';
		$queued_entries        = $this->conversion_processor->get_queued_entries_total_number();
		$conversion_batch_size = $this->conversion_processor->get_conversion_batch_size();
		if ( $this->conversion_processor->is_queued_conversion() ) {
			$queued_batches = $this->conversion_processor->get_conversion_queued_batches();
			$max_batch      = $this->conversion_processor->get_conversion_max_batch();
		} elseif ( $this->conversion_processor->is_queued_conversion_retry_failed() ) {
			$queued_batches = $this->conversion_processor->get_conversion_retry_failed_queued_batches();
			$max_batch      = $this->conversion_processor->get_conversion_retry_failed_max_batch();
		} else {
			$queued_batches = null;
			$max_batch      = $this->conversion_processor->get_conversion_max_batch();
		}
		$posts_converted_count   = $this->conversion_processor->get_posts_converted_count();
		$has_converted_posts     = ! is_null( $posts_converted_count ) && $posts_converted_count > 0 ? true : false;
		$has_failed_conversions  = ! $this->conversion_processor->is_queued_conversion()
									&& $has_converted_posts
									&& $this->conversion_processor->has_incomplete_conversions();
		$count_failed_converting = $this->conversion_processor->get_incomplete_conversions_count();

		return rest_ensure_response(
			[
				'isConversionOngoing'   => $is_conversion_ongoing,
				'queuedEntries'         => $queued_entries,
				'conversionBatchSize'   => $conversion_batch_size,
				'queuedBatches'         => $queued_batches,
				'maxBatch'              => $max_batch,
				'hasConvertedPosts'     => $has_converted_posts,
				'hasFailedConversions'  => $has_failed_conversions,
				'countFailedConverting' => $count_failed_converting,
			]
		);
	}

	/**
	 * Callback for the /conversion/initialize route.
	 * Initializes the conversion queue.
	 *
	 * @return array Null or formatted response -- key 'result', value 'queued'.
	 */
	public function initialize_conversion() {
		$initialized = $this->conversion_processor->initialize_conversion();

		return ( true === $initialized ) ? rest_ensure_response( [ 'result' => 'queued' ] ) : null;
	}

	/**
	 * Callback for the /conversion/get-batch-data route.
	 * Fetches a batch to be converted to blocks.
	 *
	 * @return array Conversion batch data.
	 */
	public function get_conversion_batch_data() {
		$ids                        = $this->conversion_processor->set_next_conversion_batch_to_queue();
		$has_incomplete_conversions = ! $this->conversion_processor->is_queued_conversion() && $this->conversion_processor->has_incomplete_conversions();
		$queued_batches             = $this->conversion_processor->get_conversion_queued_batches();
		$this_batch                 = ! empty( $queued_batches ) ? max( $queued_batches ) : null;

		return rest_ensure_response(
			[
				'ids'                      => $ids,
				'thisBatch'                => $this_batch,
				'maxBatch'                 => $this->conversion_processor->get_conversion_max_batch(),
				'hasIncompleteConversions' => $has_incomplete_conversions,
			]
		);
	}

	/**
	 * Callback for the /conversion-retry-failed/initialize route.
	 * Initializes the retry converting failed Posts queue.
	 *
	 * @return array Null or formatted response -- key 'result', value 'queued'.
	 */
	public function initialize_conversion_retry_failed() {
		$initialized = $this->conversion_processor->initialize_conversion_retry_failed();

		return ( true === $initialized ) ? rest_ensure_response( [ 'result' => 'queued' ] ) : null;
	}

	/**
	 * Callback for the /conversion-retry-failed/get-batch-data route.
	 * Fetches a batch of previously failed to convert posts to retry converting.
	 *
	 * @return array Conversion retry failed posts batch data.
	 */
	public function get_conversion_retry_failed_batch_data() {
		$has_incomplete_conversions = ! $this->conversion_processor->is_queued_conversion() && $this->conversion_processor->has_incomplete_conversions();

		return rest_ensure_response(
			[
				'ids'                      => $this->conversion_processor->set_next_retry_conversion_failed_batch_to_queue(),
				'thisBatch'                => max( $this->conversion_processor->get_conversion_retry_failed_queued_batches() ),
				'maxBatch'                 => $this->conversion_processor->get_conversion_retry_failed_max_batch(),
				'hasIncompleteConversions' => $has_incomplete_conversions,
			]
		);
	}

	/**
	 * Resets any ongoing conversion queue.
	 *
	 * @return bool
	 */
	public function reset_conversion() {
		$this->conversion_processor->reset_ongoing_conversion();

		return true;
	}

	/**
	 * Callback for the /get-post-content route.
	 * Fetches post_content.
	 *
	 * @param WP_REST_Request $params Request parameters.
	 * @return array Post content.
	 */
	public function get_post_content( $params ) {
		$post_id = $params['id'];

		return rest_ensure_response( $this->conversion_processor->get_post_content( $post_id ) );
	}

	/**
	 * Callable for /conversion/update-post API endpoint.
	 * Updates the converted Post content.
	 *
	 * @param WP_REST_Request $params Params: 'id' Post ID, 'content' Post content.
	 * @return bool Success.
	 */
	public function update_post_content( $params ) {
		$json_params    = $params->get_json_params();
		$post_id        = isset( $json_params['post_id'] ) ? $json_params['post_id'] : null;
		$content_html   = isset( $json_params['content_html'] ) ? $json_params['content_html'] : null;
		$content_blocks = isset( $json_params['content_blocks'] ) ? $json_params['content_blocks'] : null;

		if ( ! $post_id || ! $content_html || ! $content_blocks ) {
			return;
		}

		$this->conversion_processor->save_converted_post_content( $post_id, $content_html, $content_blocks );

		return true;
	}
}
