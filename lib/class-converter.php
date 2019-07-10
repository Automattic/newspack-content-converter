<?php

namespace NewspackContentConverter;

class Converter {

    private $temp_log_file;

	public function init() {

	    $this->temp_log_file = dirname( __FILE__ ) . '/../convert.log';

		$this->add_admin_menu();
		$this->redirect_page_to_editor();
		$this->enqueue_newspack_block_editor_assets();
		$this->register_api_routes();

		// TODO: content insertion temporary disabled, since Block Editor automatically saves it as new Drafts...
		// add_filter( 'default_title', [ $this, 'newspack_content_converter_title' ] );
		// add_filter( 'default_content', [ $this, 'newspack_content_converter_content' ] );

	}

	/**
	 * Checks if Newspack Content Converter page is currently accessed.
	 *
	 * @return bool
	 */
	private function is_content_converter_page() {
		return isset( $_GET[ 'newspack-content-converter' ] );
	}

	/**
	 * Plugin tab & page -- currently automatically redirected by $this->newspack_content_converter_redirect().
	 */
	private function add_admin_menu() {
		add_action( 'admin_menu', function() {
			add_menu_page( 'newspack-content-converter', __( 'Newspack Content Converter' ), 'manage_options', 'newspack-content-converter', 'converter_plugin_page' );
		} );
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
		add_action( 'rest_api_init', function() {
			register_rest_route(
				'newspack-content-converter',
				'/update-post',
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'update_converted_post_content' ],
					'permission_callback' => [ $this, 'newspack_content_converter_rest_permission' ],
				]
			);
		} );
	}

	/**
	 * Callable for /update-post API endpoint.
	 *
	 * @param WP_REST_Request $params Params: 'id' Post ID, 'content' Post content.
	 */
	public function update_converted_post_content( $params ) {
		$id      = $params->get_param( 'id' );
		$content = $params->get_param( 'content' );

		// TODO: actually update post content.
		$this->write_to_log_file(
			">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> START post_id=" . $id . "\n" .
			$content . "\n" .
			">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> END post_id=" . $id . "\n\n"
		);
	}

	private function write_to_log_file( $msg ) {

		$fh = fopen( $this->temp_log_file, 'a' ) or die( "Can't open file" );
		fwrite( $fh, $msg );
		fclose( $fh );
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
	 * Assigns custom content to the Block Editor.
	 *
	 * @param string $content Default post content.
	 *
	 * @return string
	 */
	public function newspack_content_converter_content( $content ) {
		if ( ! $this->is_content_converter_page() ) {
			return $content;
		}

		return "<!-- wp:paragraph --><p>". __( 'Please hold...', 'newspack-content-converter' ) ."</p><!-- /wp:paragraph -->";
	}

	/**
	 * Assigns custom title to the Block Editor.
	 *
	 * @param string $title Default post title.
	 *
	 * @return string Demo title, or the default title.
	 */
	public function newspack_content_converter_title( $title ) {
		if ( ! $this->is_content_converter_page() ) {
			return $title;
		}

		return __( 'Newspack content converter', 'newspack-content-converter' );
	}

	/**
	 * Gets all files in directory. Can filter results by extension.
	 *
	 * @param string      $dir_path         Full path to scan for files.
	 * @param string|null $extension_filter If provided, only returns files with this extension.
	 *
	 * @return array Files found in the directory.
	 */
	private function get_all_files_in_dir( $dir_path, $extension_filter = null ) {
		$files = scandir( $dir_path );
		$files = array_diff( scandir( $dir_path ), [ '.', '..' ] );

		if ( $extension_filter ) {
			foreach ( $files as $key => $file ) {
				$file_extension = substr( $file, -1 * strlen( $extension_filter ) );
				if ( strtolower( $extension_filter ) !== strtolower( $file_extension ) ) {
					unset( $files[ $key ] );
				}
			}
			$files = array_values( $files );
		}

		return $files ?? [];
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
			plugins_url( 'newspack_content_converter_init.js', __FILE__ ),
			[],
			filemtime( plugin_dir_path( __FILE__ ) . '/newspack_content_converter_init.js' )
		);

		$js_files = $this->get_all_files_in_dir( plugin_dir_path( __FILE__ ) . './../build/js', '.js' );
		foreach ( $js_files as $key => $js_file ) {
			$js_files[ $key ] = '/wp-content/plugins/newspack-content-converter/build/js/' . $js_file;
		}

		wp_localize_script( 'newspack-content-converter-script', 'converterScriptResources', $js_files );
	}

}
