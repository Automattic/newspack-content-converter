<?php
/**
 * Main plugin class.
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

use \NewspackContentConverter\Installer;
use \NewspackContentConverter\ConversionProcessor;

/**
 * Content Converter.
 */
class Converter {

	/**
	 * The installer service.
	 *
	 * @var Installer
	 */
	private $installer;

	/**
	 * The conversion processor service.
	 *
	 * @var ConversionProcessor
	 */
	private $conversion_processor;

	/**
	 * Converter constructor.
	 *
	 * @param Installer           $installer The installer service.
	 * @param ConversionProcessor $conversion_processor The conversion processor service.
	 */
	public function __construct( Installer $installer, ConversionProcessor $conversion_processor ) {
		$this->installer            = $installer;
		$this->conversion_processor = $conversion_processor;

		$this->register_installation_hook();
		$this->add_admin_menu();
		$this->enqueue_newspack_block_editor_assets();
		$this->register_api_routes();
		$this->disable_autosave_posts();
	}

	/**
	 * Registers installation hook.
	 */
	public function register_installation_hook() {
		register_activation_hook( NCC_PLUGIN_FILE, [ '\NewspackContentConverter\Installer', 'install_plugin' ] );
		// uninstall.php is used instead of the uninstall hook, since 'WP_UNINSTALL_PLUGIN' is defined there.
	}

	/**
	 * Disables the auto-saving on the Plugin's conversion app.
	 * The Converter plugin app piggy-backs on top of the Block Editor (loads on the page
	 * /wp-admin/post-new.php?newspack-content-converter), so turning off Autosave is necessary not to have new Drafts generated
	 * while converting.
	 */
	private function disable_autosave_posts() {
		if ( ! $this->is_current_page_the_converter_app_page() ) {
			return;
		}

		if ( false === defined( 'AUTOSAVE_INTERVAL' ) ) {
			define( 'AUTOSAVE_INTERVAL', 60 * 60 * 24 * 5 ); // Seconds.
		}
	}

	/**
	 * Adds admin menu.
	 */
	private function add_admin_menu() {

		// Remember to refresh $this->is_current_page_a_plugin_page() when adding pages here.
		add_action(
			'admin_menu',
			function () {

				add_menu_page(
					__( 'Newspack Content Converter' ),
					__( 'Newspack Content Converter' ),
					'manage_options',
					'newspack-content-converter',
					function () {
						echo '<div id="ncc-settings"></div>';
					}
				);

				add_submenu_page(
					'newspack-content-converter',
					__( 'Run Conversion' ),
					__( 'Run Conversion' ),
					'manage_options',
					'ncc-conversion',
					function () {
						echo '<div id="ncc-conversion"></div>';
					}
				);

				add_submenu_page(
					'newspack-content-converter',
					__( 'Re-apply Patchers *dev*' ),
					__( 'Re-apply Patchers *dev*' ),
					'manage_options',
					'ncc-patchers',
					function () {
						echo '<div id="ncc-patchers"></div>';
					}
				);

				// The 'ncc-content-repatching' page is not added to the menu (parent slug == null).
				add_submenu_page(
					null,
					__( 'Re-apply Patchers React app *dev*' ),
					__( 'Re-apply Patchers React app *dev*' ),
					'manage_options',
					'ncc-content-repatching',
					function () {
						echo '<div id="ncc-content-repatcher">!!! :)</div>';
					}
				);

			}
		);
	}

	/**
	 * Enqueues assets.
	 */
	private function enqueue_newspack_block_editor_assets() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueues script assets.
	 */
	public function enqueue_scripts() {
		if ( ! $this->is_current_page_a_plugin_page() && ! $this->is_current_page_the_converter_app_page() ) {
			return;
		}

		wp_enqueue_script(
			'newspack-content-converter-script',
			plugins_url( '../assets/dist/main.js', __FILE__ ),
			[
				'wp-element',
				'wp-components',
				// TODO: remove those that are unused (list taken gutenberg/docs/contributors/scripts.md).
				// 'wp-blocks' .
				'wp-annotations',
				'wp-block-editor',
				'wp-blocks',
				'wp-compose',
				'wp-data',
				'wp-dom-ready',
				'wp-edit-post',
				'wp-edit-post',
				'wp-editor',
				'wp-element',
				'wp-hooks',
				'wp-i18n',
				'wp-plugins',
				'wp-rich-text',
			],
			filemtime( plugin_dir_path( __FILE__ ) . '../assets/dist/main.js' ),
			false
		);

		wp_enqueue_style(
			'newspack-content-converter-script',
			plugins_url( '../assets/dist/main.css', __FILE__ ),
			[],
			filemtime( plugin_dir_path( __FILE__ ) . '../assets/dist/main.css' )
		);
	}

	/**
	 * Checks if a plugin page is being accessed.
	 *
	 * @return bool
	 */
	private function is_current_page_a_plugin_page() {
		$current_screen = get_current_screen();

		return isset( $current_screen ) && (
			false !== strpos( $current_screen->id, 'newspack-content-converter' ) ||
			'admin_page_ncc-content-repatching' === $current_screen->id
		);
	}

	/**
	 * Checks if the Gutenberg mass converter app is currently accessed, which is at
	 * /wp-admin/post-new.php?newspack-content-converter .
	 *
	 * @return bool
	 */
	private function is_current_page_the_converter_app_page() {
		global $pagenow;

		return (
			'post-new.php' === $pagenow &&
			isset( $_GET['newspack-content-converter'] )
		);
	}

	/**
	 * Registers routes.
	 */
	private function register_api_routes() {

		// Fetches info for the settings page.
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'newspack-content-converter',
					'/settings/get-info',
					[
						'methods'             => 'GET',
						'callback'            => [ $this, 'api_settings_get_info' ],
						'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
					]
				);
			}
		);

		// Fetches info for the conversion page.
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'newspack-content-converter',
					'/conversion/get-info',
					[
						'methods'             => 'GET',
						'callback'            => [ $this, 'api_conversion_get_info' ],
						'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
					]
				);
			}
		);

		// Initializes the conversion queue.
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'newspack-content-converter',
					'/conversion/initialize',
					[
						'methods'             => 'POST',
						'callback'            => [ $this, 'api_conversion_initialize' ],
						'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
					]
				);
			}
		);

		// Fetches a batch to be converted to blocks.
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'newspack-content-converter',
					'/conversion/get-batch-data',
					[
						'methods'             => 'GET',
						'callback'            => [ $this, 'api_conversion_get_batch_data' ],
						'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
					]
				);
			}
		);

		// Fetches info for the patching page.
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'newspack-content-converter',
					'/patching/get-info',
					[
						'methods'             => 'GET',
						'callback'            => [ $this, 'api_patching_get_info' ],
						'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
					]
				);
			}
		);

		// Initializes patching.
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'newspack-content-converter',
					'/patching/initialize',
					[
						'methods'             => 'POST',
						'callback'            => [ $this, 'api_patching_initialize' ],
						'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
					]
				);
			}
		);

		// Processes the next patching batch.
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'newspack-content-converter',
					'/patching/process-next-batch',
					[
						'methods'             => 'GET',
						'callback'            => [ $this, 'api_patching_process_next_batch' ],
						'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
					]
				);
			}
		);

		// Fetches post_content.
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'newspack-content-converter',
					'/get-post-content-by-id',
					[
						'methods'             => 'POST',
						'callback'            => [ $this, 'api_get_post_content_by_id' ],
						'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
					]
				);
			}
		);

		// Updates the converted Post content.
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'newspack-content-converter',
					'/conversion/update-post',
					[
						'methods'             => 'POST',
						'callback'            => [ $this, 'api_update_converted_post_content' ],
						'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
					]
				);
			}
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
			'info' => [
				'conversionContentTypesCsv'    => $content_types_csv,
				'conversionContentStatusesCsv' => $content_statuses_csv,
				'conversionBatchSize'          => $conversion_batch_size,
				'patchingBatchSize'            => $patching_batch_size,
				'queuedEntries'                => $queued_entries,
			],
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
			'info' => [
				'isConversionOngoing' => $is_conversion_ongoing ? '1' : '0',
				'queuedEntries'       => $queued_entries,
				'conversionBatchSize' => $conversion_batch_size,
				'queuedBatchesCsv'    => implode( ',', $queued_batches ),
				'maxBatch'            => $max_batch,
			],
		];
	}

	/**
	 * Callback for the /conversion/initialize route.
	 * Initializes the conversion queue.
	 *
	 * @param WP_REST_Request $params JSON param, key 'request' with value 'initialize'.
	 * @return array Formatted response.
	 */
	public function api_conversion_initialize( $params ) {
		$json_params   = $params->get_json_params();
		$request_param = isset( $json_params['request'] ) ? $json_params['request'] : null;

		if ( ! $request_param || 'initialize' !== $request_param ) {
			return;
		}

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
			'info' => [
				'isPatchingOngoing'        => $is_patching_ongoing,
				'queuedBatchesPatchingCsv' => implode( ',', $queued_batches_patching ),
				'maxBatchPatching'         => $max_batch_patching,
				'patchingBatchSize'        => $patching_batch_size,
				'queuedEntries'            => $queued_entries,
			],
		];

	}

	/**
	 * Callback for the /patching/initialize route.
	 * Initializes patching.
	 *
	 * @param WP_REST_Request $params JSON param, key 'request' with value 'initialize'.
	 * @return array Formatted response.
	 */
	public function api_patching_initialize( $params ) {
		$json_params   = $params->get_json_params();
		$request_param = isset( $json_params['request'] ) ? $json_params['request'] : null;

		if ( ! $request_param || 'initialize' !== $request_param ) {
			return;
		}

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
