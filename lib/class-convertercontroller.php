<?php
/**
 * Main controller class.
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Server;
use NewspackContentConverter\ConversionProcessor;

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

		// Fetches info for the restore page.
		register_rest_route(
			$namespace,
			'/restore/get-info',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_restore_info' ],
				'permission_callback' => [ $this, 'rest_permission' ],
			]
		);

		// Initializes and prepare batches to start being converted.
		register_rest_route(
			$namespace,
			'/conversion/prepare',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'prepare_conversion' ],
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
				'args'                => [
					'id' => [
						'validate_callback' => function ( $param, $request, $key ) {
							return is_numeric( $param );
						},
					],
				],
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

		// Updates the converted Post content.
		register_rest_route(
			$namespace,
			'/restore/restore-post-contents',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'restore_post_contents' ],
				'permission_callback' => [ $this, 'rest_permission' ],
			]
		);

		register_rest_route(
			$namespace,
			'/conversion/get-all-converted-ids',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_all_converted_ids' ],
				'permission_callback' => [ $this, 'rest_permission' ],
			]
		);

		register_rest_route(
			$namespace,
			'/conversion/get-all-unconverted-ids',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_all_unconverted_ids' ],
				'permission_callback' => [ $this, 'rest_permission' ],
			]
		);

		register_rest_route(
			$namespace,
			'/conversion/flush-all-meta-backups',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'flush_all_meta_backups' ],
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
		$is_user_authorized = current_user_can( 'edit_others_posts' );
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
				'conversionContentTypesCsv'    => $this->conversion_processor->get_conversion_post_types(),
				'conversionContentStatusesCsv' => $this->conversion_processor->get_conversion_post_statuses(),
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
		$is_conversion_prepared               = $this->conversion_processor->is_conversion_prepared() ? '1' : '0';
		$unconverted_count                    = count( $this->conversion_processor->get_all_unconverted_ids() );
		$total_number_of_batches              = ceil( $unconverted_count / $this->conversion_processor->get_conversion_batch_size() );
		$are_there_successfully_converted_ids = count( $this->conversion_processor->get_all_converted_ids() ) > 0;
		$are_there_unconverted_ids            = $unconverted_count > 0;

		$response = rest_ensure_response(
			[
				'isConversionPrepared'             => $is_conversion_prepared,
				'unconvertedCount'                 => $unconverted_count,
				'totalNumberOfBatches'             => $total_number_of_batches,
				'areThereSuccessfullyConvertedIds' => $are_there_successfully_converted_ids,
				'areThereUnconvertedIds'           => $are_there_unconverted_ids,
			]
		);
		return $response;
	}

	/**
	 * Callback for the /restore/get-info route.
	 *
	 * @return array Info for the restore page.
	 */
	public function get_restore_info() {
		$number_of_converted_ids = count( $this->conversion_processor->get_all_converted_ids() );

		return rest_ensure_response(
			[
				'numberOfConvertedIds' => $number_of_converted_ids,
			]
		);
	}

	/**
	 * Callback for the /restore/restore-post-contents route.
	 *
	 * @return array
	 */
	public function restore_post_contents( $params ) {
		$post_ids_csv = $params['post_ids'] ?? null;

		// Sanitize input values and get array of post IDs.
		$post_ids = [];
		$values   = explode( ',', $post_ids_csv );
		foreach ( $values as $value ) {
			$trimmed_value = trim( $value );
			if ( ctype_digit( $trimmed_value ) ) {
				$post_ids[] = (int) $trimmed_value;
			}
		}

		// Restore content.
		$success = $this->conversion_processor->restore_post_contents_to_before_conversion( $post_ids );

		return rest_ensure_response( [ 'success' => $success ] );
	}

	/**
	 * Callback for the /conversion/prepare route.

	 * @return void
	 */
	public function prepare_conversion() {
		$this->conversion_processor->prepare_conversion();

		return rest_ensure_response( [ 'success' => true ] );
	}

	/**
	 * Callback for the /conversion/get-batch-data route.
	 *
	 * Fetches a batch of posts to be converted to blocks.
	 * Once the last batch of posts is converted, the conversion is finalized and disabled (otherwise in a
	 * specific use case te conversion tabs which auto-reload might continue picking up posts indefinitely).
	 *
	 * @return array Conversion batch data.
	 */
	public function get_conversion_batch_data() {

		// Check if conversion has been prepared and the next batch can be converted.
		$is_conversion_prepared = $this->conversion_processor->is_conversion_prepared();
		if ( false === $is_conversion_prepared ) {
			return rest_ensure_response(
				[
					'isConversionPrepared' => '0',
				]
			);
		}

		$total_number_of_batches = $this->conversion_processor->get_total_number_of_batches();
		/**
		 * Get and set a new batch number. Note that if there are no more batches, $this_batch will be returned null
		 * and all conversion options will be deleted, i.e. $this->conversion_processor->is_conversion_prepared()
		 * will start returning false if called again.
		 */
		$this_batch = $this->conversion_processor->get_and_set_next_batch_to_queue();
		$ids        = $this_batch ? $this->conversion_processor->get_ids_for_batch( $this_batch ) : [];

		/**
		 * Conversion is finished if there's no new batch to process.
		 */
		if ( empty( $ids ) || ( null === $this_batch ) || ( $this_batch > $total_number_of_batches ) ) {

			// Flush the cache.
			wp_cache_flush();

			return rest_ensure_response(
				[
					'isConversionPrepared' => '0',
					'isConversionFinished' => '1',
					'ids'                  => [],
					'thisBatch'            => null,
					'totalNumberOfBatches' => null,
				]
			);
		}

		return rest_ensure_response(
			[
				'isConversionPrepared' => '1',
				'isConversionFinished' => '0',
				'ids'                  => $ids,
				'thisBatch'            => $this_batch,
				'totalNumberOfBatches' => $total_number_of_batches,
			]
		);
	}

	/**
	 * Resets any ongoing conversion queue
	 *
	 * @return bool
	 */
	public function reset_conversion() {
		/**
		 * By removing the option, we clear any ongoing conversion queue (which might have been unwillingly
		 * terminated and needs to be reset). After it's been reset, the conversion can now be started again.
		 */
		$this->conversion_processor->delete_all_conversion_options();

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
		$post_id      = $params['id'];
		$post_content = $this->conversion_processor->get_post_content( $post_id );

		return rest_ensure_response( $post_content );
	}

	/**
	 * Callable for /conversion/update-post API endpoint.
	 * Updates the converted Post content.
	 *
	 * @param WP_REST_Request $request Params: 'id' Post ID, 'content_html' and 'content_blocks'.
	 * @return bool Success.
	 */
	public function update_post_content( $request ) {
		$params         = $request->get_json_params();
		$post_id        = isset( $params['post_id'] ) ? $params['post_id'] : null;
		$content_html   = isset( $params['content_html'] ) ? $params['content_html'] : null;
		$content_blocks = isset( $params['content_blocks'] ) ? $params['content_blocks'] : null;

		if ( ! $post_id || ! $content_html || ! $content_blocks ) {
			return;
		}

		$this->conversion_processor->update_converted_post( $post_id, $content_html, $content_blocks );

		return true;
	}

	/**
	 * Callback for the /conversion/get-all-converted-ids route.
	 * Fetches successfully converted post IDs.
	 *
	 * @return array Successfully converted post IDs.
	 */
	public function get_all_converted_ids() {
		$converted_ids     = $this->conversion_processor->get_all_converted_ids();
		$converted_ids_csv = implode( ',', $converted_ids );

		return rest_ensure_response(
			[
				'ids' => $converted_ids_csv,
			]
		);
	}

	/**
	 * Callback for the /get-all-unconverted-ids route.
	 * Fetches unsuccessfully converted post IDs.
	 *
	 * @return array Unsuccessfully converted post IDs.
	 */
	public function get_all_unconverted_ids() {
		$ids     = $this->conversion_processor->get_all_unconverted_ids();
		$ids_csv = implode( ',', $ids );

		return rest_ensure_response(
			[
				'ids' => $ids_csv,
			]
		);
	}

	/**
	 * Callback for the /get-all-unconverted-ids route.
	 * Fetches unsuccessfully converted post IDs.
	 *
	 * @return array Unsuccessfully converted post IDs.
	 */
	public function flush_all_meta_backups() {
		$ids = $this->conversion_processor->flush_all_meta_backups();

		return rest_ensure_response( [ 'success' => true ] );
	}
}
