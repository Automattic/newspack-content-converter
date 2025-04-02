<?php
/**
 * Main plugin class.
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

/**
 * Content Converter.
 */
class Converter {

	/**
	 * The main Controller.
	 *
	 * @var ConverterController $controller The main Controller.
	 */
	private $controller;

	/**
	 * Converter constructor.
	 *
	 * @param ConverterController $controller The main Controller.
	 */
	public function __construct( ConverterController $controller ) {

		$this->controller = $controller;

		$this->add_admin_menu();
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'rest_api_init', [ $this->controller, 'register_routes' ] );
		$this->disable_autosave_posts();
		$this->register_filters();
	}

	/**
	 * Registers filters.
	 *
	 * @return void
	 */
	public function register_filters() {

		/**
		 * Filters to run on HTML content before the conversion.
		 */
		$preconversion_filters = [
			// Encode blocks as very first thing.
			[ ContentPatcher\Patchers\SocialEmbedsPatcher::class, 'patch_html_source' ],
			[ ContentPatcher\Patchers\BlockEncodePatcher::class, 'patch_html_source' ],
			[ ContentPatcher\Patchers\WpFiltersPatcher::class, 'patch_html_source' ],
			[ ContentPatcher\Patchers\ShortcodePreconversionPatcher::class, 'patch_html_source' ],
		];
		foreach ( $preconversion_filters as $preconversion_filter ) {
			$class  = $preconversion_filter[0];
			$method = $preconversion_filter[1];
			if ( class_exists( $class ) && method_exists( $class, $method ) ) {
				$object = new $class();
				add_filter( 'ncc_filter_html_before_conversion', [ $object, $method ], 10, 2 );
			}
		}

		/**
		 * Filters to run on Blocks content after the conversion, and before getting saved to DB.
		 */
		$postconversion_filters = [
			[ ContentPatcher\Patchers\ImgPatcher::class, 'patch_blocks_contents' ],
			[ ContentPatcher\Patchers\CaptionImgPatcher::class, 'patch_blocks_contents' ],
			[ ContentPatcher\Patchers\ParagraphPatcher::class, 'patch_blocks_contents' ],
			[ ContentPatcher\Patchers\BlockquotePatcher::class, 'patch_blocks_contents' ],
			[ ContentPatcher\Patchers\VideoPatcher::class, 'patch_blocks_contents' ],
			[ ContentPatcher\Patchers\AudioPatcher::class, 'patch_blocks_contents' ],
			[ ContentPatcher\Patchers\ShortcodeModulePatcher::class, 'patch_blocks_contents' ],
			[ ContentPatcher\Patchers\ShortcodePullquotePatcher::class, 'patch_blocks_contents' ],
			// Decode blocks as the very last thing.
			[ ContentPatcher\Patchers\BlockDecodePatcher::class, 'patch_blocks_contents' ],
		];
		foreach ( $postconversion_filters as $ostconversion_filter ) {
			$class  = $ostconversion_filter[0];
			$method = $ostconversion_filter[1];
			if ( class_exists( $class ) && method_exists( $class, $method ) ) {
				$object = new $class();
				add_filter( 'ncc_filter_blocks_after_conversion', [ $object, $method ], 10, 3 );
			}
		}
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
					__( 'Content Converter' ),
					__( 'Content Converter' ),
					'manage_options',
					'newspack-content-converter',
					function () {
						echo '<div id="ncc-conversion"></div>';
					},
					'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgZmlsbD0ibm9uZSI+PHBhdGggZmlsbD0iI2ZmZiIgZD0iTTE1LjM5MiAxMC41MmgzLjI0OGMuNDg3IDAgLjgxMi0uMzI1LjgxMi0uODEyVjYuNDZjMC0uNDg3LS4zMjUtLjgxMi0uODEyLS44MTJoLTMuMjQ4Yy0uNDg3IDAtLjgxMi4zMjUtLjgxMi44MTJ2My4yNDhjMCAuNDg3LjMyNS44MTIuODEyLjgxMlptLTYuNDk2IDMuMjQ4SDUuNjQ4Yy0uNDg3IDAtLjgxMi4zMjQtLjgxMi44MTJ2My4yNDhjMCAuNDg3LjMyNS44MTIuODEyLjgxMmgzLjI0OGMuNDg3IDAgLjgxMi0uMzI1LjgxMi0uODEyVjE0LjU4YzAtLjQ4OC0uMzI1LS44MTItLjgxMi0uODEyWm04LjEyLTIuMTExLTIuODQyIDIuOTIzLjg5My44OTMgMS4zOC0xLjM4Yy0uMDguODkzLS4yNDMgMS44NjctLjczIDIuMzU0LS4yNDQuMjQ0LS41NjkuNDA2LTEuMDU2LjQwNmgtMy42NTR2MS4yMThoMy41NzNjLjczIDAgMS4zOC0uMjQzIDEuODY3LS43My44MTItLjgxMyAxLjA1Ni0yLjE5MyAxLjEzNy0zLjI0OWwxLjQ2MiAxLjQ2Mi44OTMtLjg5My0yLjkyMy0zLjAwNFptLTkuMDk1LTEuMzhjLjA4Mi0uODk0LjI0NC0xLjg2OC43MzEtMi4zNTYuMzI1LS4zMjQuNjUtLjQ4NyAxLjA1Ni0uNDg3aDMuNjU0VjYuMjk4SDkuNzA4Yy0uNzMxIDAtMS4zOC4yNDMtMS44NjguNzMtLjgxMi44MTItMS4wNTUgMi4xOTMtMS4xMzYgMy4yNDhsLTEuNDYyLTEuMzgtLjgxMi44MTIgMi44NDIgMi45MjMgMi44NDItMi45MjMtLjgxMi0uODEyLTEuMzggMS4zOFoiLz48L3N2Zz4=',
				);

				add_submenu_page(
					'newspack-content-converter',
					__( 'Converter' ),
					__( 'Converter' ),
					'manage_options',
					'newspack-content-converter',
					function () {
						echo '<div id="ncc-conversion"></div>';
					}
				);

				add_submenu_page(
					'newspack-content-converter',
					__( 'Restore content' ),
					__( 'Restore content' ),
					'manage_options',
					'newspack-content-converter-restore',
					function () {
						echo '<div id="ncc-restore"></div>';
					}
				);

				add_submenu_page(
					'newspack-content-converter',
					__( 'Settings' ),
					__( 'Settings' ),
					'manage_options',
					'newspack-content-converter-settings',
					function () {
						echo '<div id="ncc-settings"></div>';
					}
				);
			}
		);
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
			[
				'wp-components',
			],
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

		return isset( $current_screen ) && ( false !== strpos( $current_screen->id, 'newspack-content-converter' ) );
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
			isset( $_GET['newspack-content-converter'] ) // phpcs:ignore
		);
	}
}
