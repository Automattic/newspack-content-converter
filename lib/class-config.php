<?php
/**
 * Main config class, holding config parameters.
 *
 * @package Newspack
 */

namespace NewspackContentConverter;

/**
 * Class Config
 *
 * @package NewspackContentConverter
 */
class Config {

	/**
	 * Singleton instance.
	 *
	 * @var Config
	 */
	private static $instance;

	/**
	 * The config array.
	 *
	 * @var array
	 */
	private static $config;

	/**
	 * Config constructor.
	 */
	private function __construct() {
		self::$config = array(
			// --- Default param values.
			'table_name'                                 => 'ncc_wp_posts',
			'post_statuses'                              => array( 'publish' ),
			'conversion_batch_size'                      => 100,
		);
	}

	/**
	 * Singleton public getter.
	 *
	 * @return Config
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get option value by key.
	 *
	 * @param string $key Config key.
	 *
	 * @return mixed|null Option value, or null.
	 */
	public function get( $key ) {
		return isset( self::$config[ $key ] ) ? self::$config[ $key ] : null;
	}
}
