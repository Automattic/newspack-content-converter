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
				'callback'            => [ $this, 'api_settings_get_info' ],
				'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
			]
		);

		// Fetches info for the conversion page.
		register_rest_route(
			$namespace,
			'/conversion/get-info',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'api_conversion_get_info' ],
				'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
			]
		);

		// Initializes the conversion queue.
		register_rest_route(
			$namespace,
			'/conversion/initialize',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'api_conversion_initialize' ],
				'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
			]
		);

		// Fetches a batch to be converted to blocks.
		register_rest_route(
			$namespace,
			'/conversion/get-batch-data',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'api_conversion_get_batch_data' ],
				'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
			]
		);

		// Fetches info for the patching page.
		register_rest_route(
			$namespace,
			'/patching/get-info',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'api_patching_get_info' ],
				'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
			]
		);

		// Initializes patching.
		register_rest_route(
			$namespace,
			'/patching/initialize',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'api_patching_initialize' ],
				'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
			]
		);

		// Processes the next patching batch.
		register_rest_route(
			$namespace,
			'/patching/process-next-batch',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'api_patching_process_next_batch' ],
				'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
			]
		);

		// Fetches post_content.
		register_rest_route(
			$namespace,
			'/get-post-content-by-id',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'api_get_post_content_by_id' ],
				'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
			]
		);

		// Updates the converted Post content.
		register_rest_route(
			$namespace,
			'/conversion/update-post',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'api_update_converted_post_content' ],
				'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
			]
		);
	}

	/**
	 * Permission check, common to basic Converter endpoints.
	 *
	 * @return bool|WP_Error
	 */
	public function newspack_content_converter_rest_permission() {
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
	public function api_settings_get_info() {
		$content_types_csv     = $this->conversion_processor->get_conversion_content_types();
		$content_statuses_csv  = $this->conversion_processor->get_conversion_content_statuses();
		$conversion_batch_size = $this->conversion_processor->get_conversion_batch_size();
		$patching_batch_size   = $this->conversion_processor->get_patching_batch_size();
		$queued_entries        = $this->conversion_processor->get_queued_entries_total_number();

		return [
			'conversionContentTypesCsv'    => $content_types_csv,
			'conversionContentStatusesCsv' => $content_statuses_csv,
			'conversionBatchSize'          => $conversion_batch_size,
			'patchingBatchSize'            => $patching_batch_size,
			'queuedEntries'                => $queued_entries,
		];
	}

	/**
	 * Callback for the /conversion/get-info route.
	 * Fetches info for the conversion page.
	 *
	 * @return array Info for the settings page.
	 */
	public function api_conversion_get_info() {
		$is_conversion_ongoing = $this->conversion_processor->is_conversion_queued();
		$queued_entries        = $this->conversion_processor->get_queued_entries_total_number();
		$conversion_batch_size = $this->conversion_processor->get_conversion_batch_size();
		$queued_batches        = $this->conversion_processor->get_conversion_queued_batches();
		$max_batch             = $this->conversion_processor->get_conversion_max_batch();

		return [
			'isConversionOngoing' => $is_conversion_ongoing ? '1' : '0',
			'queuedEntries'       => $queued_entries,
			'conversionBatchSize' => $conversion_batch_size,
			'queuedBatchesCsv'    => implode( ',', $queued_batches ),
			'maxBatch'            => $max_batch,
		];
	}

	/**
	 * Callback for the /conversion/initialize route.
	 * Initializes the conversion queue.
	 *
	 * @param WP_REST_Request $params JSON param, key 'request' with value 'initialize'.
	 * @return array Formatted response.
	 */
	public function api_conversion_initialize() {
		$initialized = $this->conversion_processor->initialize_conversion();

		return ( true === $initialized ) ? [ 'result' => 'queued' ] : null;
	}

	/**
	 * Callback for the /conversion/get-batch-data route.
	 * Fetches a batch to be converted to blocks.
	 *
	 * @return array Conversion batch data.
	 */
	public function api_conversion_get_batch_data() {
		$ids        = $this->conversion_processor->set_next_conversion_batch_to_queue();
		$this_batch = max( $this->conversion_processor->get_conversion_queued_batches() );
		$max_batch  = $this->conversion_processor->get_conversion_max_batch();

		return [
			'ids'       => $ids,
			'thisBatch' => $this_batch,
			'maxBatch'  => $max_batch,
		];
	}

	/**
	 * Callback for the /patching/get-info route.
	 * Fetches info for the patching page.
	 *
	 * @return array Info for the patching page.
	 */
	public function api_patching_get_info() {
		$is_patching_ongoing     = $this->conversion_processor->is_patching_queued();
		$queued_batches_patching = $this->conversion_processor->get_patching_queued_batches();
		$max_batch_patching      = $this->conversion_processor->get_patching_max_batch();
		$patching_batch_size     = $this->conversion_processor->get_patching_batch_size();
		$queued_entries          = $this->conversion_processor->get_queued_entries_total_number();

		return [
			'isPatchingOngoing'        => $is_patching_ongoing,
			'queuedBatchesPatchingCsv' => implode( ',', $queued_batches_patching ),
			'maxBatchPatching'         => $max_batch_patching,
			'patchingBatchSize'        => $patching_batch_size,
			'queuedEntries'            => $queued_entries,
		];

	}

	/**
	 * Callback for the /patching/initialize route.
	 * Initializes patching.
	 *
	 * @param WP_REST_Request $params JSON param, key 'request' with value 'initialize'.
	 * @return array Formatted response.
	 */
	public function api_patching_initialize() {
		$initialized = $this->conversion_processor->initialize_patching();

		return ( true === $initialized ) ? [ 'result' => 'queued' ] : null;
	}

	/**
	 * Callback for the /patching/process-next-batch route.
	 * /patching/process-next-batch
	 *
	 * @return array Formatted response.
	 */
	public function api_patching_process_next_batch() {
		if ( ! $this->conversion_processor->is_patching_queued() ) {
			return;
		}

		$current_batch = $this->conversion_processor->move_next_patching_batch_to_queue();
		if ( false === $current_batch ) {
			return;
		}

		$patched = $this->conversion_processor->apply_patches_to_batch( $current_batch );
		if ( false == $patched ) {
			return;
		}

		return [
			'result' => 'patched',
		];
	}

	/**
	 * Callback for the /get-post-content-by-id route.
	 * Fetches post_content.
	 *
	 * @param WP_REST_Request $params Params: 'id' Post ID.
	 * @return array Post content.
	 */
	public function api_get_post_content_by_id( $params ) {
		$json_params = $params->get_json_params();
		$post_id     = isset( $json_params['id'] ) ? $json_params['id'] : null;

		if ( ! $post_id ) {
			return;
		}

		return $this->conversion_processor->get_post_content_by_id( $post_id );
	}

	/**
	 * Callable for /conversion/update-post API endpoint.
	 * Updates the converted Post content.
	 *
	 * @param WP_REST_Request $params Params: 'id' Post ID, 'content' Post content.
	 */
	public function api_update_converted_post_content( $params ) {
		$json_params    = $params->get_json_params();
		$post_id        = isset( $json_params['post_id'] ) ? $json_params['post_id'] : null;
		$content_html   = isset( $json_params['content_html'] ) ? $json_params['content_html'] : null;
		$content_blocks = isset( $json_params['content_blocks'] ) ? $json_params['content_blocks'] : null;

		if ( ! $post_id || ! $content_html || ! $content_blocks ) {
			return;
		}

		$this->conversion_processor->save_converted_post_content( $post_id, $content_html, $content_blocks );
	}
}
