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
			// --- All plugin's options.
			'option_conversion_post_types_csv'           => 'ncc-convert_post_types_csv',
			'option_conversion_post_statuses_csv'        => 'ncc-convert_post_statuses_csv',
			'option_conversion_batch_size'               => 'ncc-conversion_batch_size',
			'option_is_queued_conversion'                => 'ncc-is_queued_conversion',
			'option_is_queued_retry_failed_conversion'   => 'ncc-is_queued_retry_failed_conversion',
			'option_conversion_queued_batches'           => 'ncc-conversion_queued_batches_csv',
			'option_retry_conversion_failed_queued_batches' => 'ncc-retry_conversion_failed_queued_batches_csv',
			'option_conversion_max_batches'              => 'ncc-conversion_max_batches',
			'option_retry_conversion_failed_max_batches' => 'ncc-retry_conversion_failed_max_batches',
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
