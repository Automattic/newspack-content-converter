<?php
/**
 * Convert pre-Gutenberg post content to Gutenberg Blocks
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

use \NewspackContentConverter\ContentPatcher\PatchHandler;

/**
 * Gutenberg Converter Class
 */
class Converter {

	/**
	 * Path to the temporary log file.
	 *
	 * @var string
	 */
	private $temp_log_file;

	/**
	 * The PatchHandler.
	 *
	 * @var PatchHandler
	 */
	private $patcher_handler;

	/**
	 * Constructor.
	 *
	 * @param PatchHandler $patcher_handler The PatchHandler.
	 */
	public function __construct( PatchHandler $patcher_handler ) {

		$this->temp_log_file   = dirname( __FILE__ ) . '/../convert.log';
		$this->patcher_handler = $patcher_handler;

		$this->disable_autosave_posts();
		$this->add_admin_menu();
		$this->redirect_page_to_editor();
		$this->enqueue_newspack_block_editor_assets();
		$this->register_api_routes();
	}

	/**
	 * Disables auto-saving on the Plugin's page.
	 * The Converter plugin works on this page /wp-admin/post-new.php?newspack-content-converter, and the autosave feature
	 * automatically rewrites this URL to the newly saved draft's post ID.
	 */
	private function disable_autosave_posts() {
		if ( ! $this->is_content_converter_page() ) {
			return;
		}

		if ( false === defined( 'AUTOSAVE_INTERVAL' ) ) {
			define( 'AUTOSAVE_INTERVAL', 60 * 60 * 24 * 5 ); // Seconds.
		}
	}

	/**
	 * Checks if Newspack Content Converter page is currently accessed.
	 *
	 * @return bool
	 */
	private function is_content_converter_page() {
		return isset( $_GET['newspack-content-converter'] );
	}

	/**
	 * Plugin tab & page -- currently automatically redirected by $this->newspack_content_converter_redirect().
	 */
	private function add_admin_menu() {
		add_action(
			'admin_menu',
			function() {
				add_menu_page( 'newspack-content-converter', __( 'Newspack Content Converter' ), 'manage_options', 'newspack-content-converter', 'converter_plugin_page' );
			}
		);
	}

	/**
	 * Blank content for the plugin tab/page.
	 */
	private function converter_plugin_page() {
		?>
		blank_redirect:::
		<?php
	}

	/**
	 * Routes' registration.
	 */
	private function register_api_routes() {
		// Endpoint to update converted Post content.
		add_action(
			'rest_api_init',
			function() {
				register_rest_route(
					'newspack-content-converter',
					'/update-post',
					[
						'methods'             => 'POST',
						'callback'            => [ $this, 'update_converted_post_content' ],
						'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
					]
				);
			}
		);
	}

	/**
	 * Callable for /update-post API endpoint.
	 *
	 * @param WP_REST_Request $params Params: 'id' Post ID, 'content' Post content.
	 */
	public function update_converted_post_content( $params ) {
		$json_params    = $params->get_json_params();
		$post_id        = isset( $json_params['post_id'] ) ? $json_params['post_id'] : null;
		$content_html   = isset( $json_params['content_html'] ) ? $json_params['content_html'] : null;
		$content_blocks = isset( $json_params['content_blocks'] ) ? $json_params['content_blocks'] : null;

		if ( ! $post_id || ! $content_html || ! $content_blocks ) {
			return;
		}

		$content_blocks_patched = $this->patch_converted_blocks_content( $content_html, $content_blocks );

		// TODO: actually update post content.
		$this->write_to_log_file(
			'================= START post_id=' . $post_id . "\n" .
			$post_id . "\n" .
			$content_blocks_patched . "\n" .
			// print_r( $json_params, true ) . "\n" .
			// $content_html . "\n" .
			// $content_blocks . "\n" .
			'================= END post_id=' . $post_id . "\n\n"
		);
	}

	/**
	 * Runs patches on converted blocks content.
	 *
	 * @param string $content_html   HTML content.
	 * @param string $content_blocks Blocks content.
	 *
	 * @return string|null Patched blocks content.
	 */
	private function patch_converted_blocks_content( $content_html, $content_blocks ) {
		return $this->patcher_handler->run_all_patches( $content_html, $content_blocks );
	}

	/**
	 * Write a message to log file
	 *
	 * @param string $msg The message to write.
	 */
	private function write_to_log_file( $msg ) {
		/*
		// TODO: remove -- temporarily available for temp debugging purposes.
		$fh = fopen( $this->temp_log_file, 'a' ) or die( "Can't open file" );
		fwrite( $fh, $msg );
		fclose( $fh );
		 */
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
	 * Add a redirect.
	 */
	private function redirect_page_to_editor() {
		add_action( 'admin_init', [ $this, 'newspack_content_converter_redirect' ] );
	}

	/**
	 * Temporarily redirects the plugin page to the extended Block Editor.
	 */
	public function newspack_content_converter_redirect() {
		global $pagenow;

		if (
			'admin.php' === $pagenow &&
			isset( $_GET['page'] ) &&
			'newspack-content-converter' === $_GET['page']
		) {
			wp_safe_redirect( admin_url( 'post-new.php?newspack-content-converter' ) );
			exit;
		}
	}

	/**
	 * Enqueues Block Editor assets for NCC.
	 */
	private function enqueue_newspack_block_editor_assets() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'newspack_content_converter_enqueue_script' ] );
	}

	/**
	 * Enqueues script assets.
	 */
	public function newspack_content_converter_enqueue_script() {
		if ( ! $this->is_content_converter_page() ) {
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
				'wp-components',
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

}
