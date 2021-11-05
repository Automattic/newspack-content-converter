<?php
/**
 * Main plugin class.
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

use \NewspackContentConverter\ConverterController;
use \NewspackContentConverter\Installer;

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
	 * Converter constructor.
	 *
	 * @param Installer           $installer The installer service.
	 * @param ConverterController $controller The main Controller.
	 */
	public function __construct( Installer $installer, ConverterController $controller ) {
		$this->installer  = $installer;
		$this->controller = $controller;

		$this->register_installation_hook();
		$this->add_admin_menu();
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'rest_api_init', [ $this->controller, 'register_routes' ] );
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
					__( 'Run Conversion' ),
					__( 'Content Converter' ),
					'manage_options',
					'newspack-content-converter',
					function () {
						echo '<div id="ncc-conversion"></div>';
					},
					'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgd2lkdGg9IjI0Ij48cGF0aCBkPSJNMTIsMkM2LjQ4LDIsMiw2LjQ4LDIsMTJjMCw1LjUyLDQuNDgsMTAsMTAsMTBzMTAtNC40OCwxMC0xMEMyMiw2LjQ4LDE3LjUyLDIsMTIsMnogTTEyLjA2LDE5di0yLjAxYy0wLjAyLDAtMC4wNCwwLTAuMDYsMCBjLTEuMjgsMC0yLjU2LTAuNDktMy41NC0xLjQ2Yy0xLjcxLTEuNzEtMS45Mi00LjM1LTAuNjQtNi4yOWwxLjEsMS4xYy0wLjcxLDEuMzMtMC41MywzLjAxLDAuNTksNC4xM2MwLjcsMC43LDEuNjIsMS4wMywyLjU0LDEuMDEgdi0yLjE0bDIuODMsMi44M0wxMi4wNiwxOXogTTE2LjE3LDE0Ljc2bC0xLjEtMS4xYzAuNzEtMS4zMywwLjUzLTMuMDEtMC41OS00LjEzQzEzLjc5LDguODQsMTIuOSw4LjUsMTIsOC41Yy0wLjAyLDAtMC4wNCwwLTAuMDYsMCB2Mi4xNUw5LjExLDcuODNMMTEuOTQsNXYyLjAyYzEuMy0wLjAyLDIuNjEsMC40NSwzLjYsMS40NUMxNy4yNCwxMC4xNywxNy40NSwxMi44MiwxNi4xNywxNC43NnoiIGZpbGw9IndoaXRlIi8+PC9zdmc+Cg=='
				);

				add_submenu_page(
					'newspack-content-converter',
					__( 'Run Conversion' ),
					__( 'Run Conversion' ),
					'manage_options',
					'newspack-content-converter',
					function () {
						echo '<div id="ncc-conversion"></div>';
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
