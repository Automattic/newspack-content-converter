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
			isset( $_GET['newspack-content-converter'] ) // phpcs:ignore
		);
	}
}
