<?php
/**
 * Test class for the NewspackContentConverter\ContentPatcher\Patchers\ShortcodePreconversionPatcher.
 *
 * @package Newspack
 */

namespace NewspackContentConverterTest;

use WP_UnitTestCase;
use NewspackContentConverter\ContentPatcher\Patchers\PreconversionPatcherAbstract;
use NewspackContentConverter\ContentPatcher\Patchers\ShortcodePreconversionPatcher;
use NewspackContentConverterTest\DataProviderShortcodePreconversionPatcher;

/**
 * Class ShortcodePreconversionPatcher
 */
class TestShortcodePreconversionPatcher extends WP_UnitTestCase {

	/**
	 * ShortcodePreconversionPatcher.
	 *
	 * @var PreconversionPatcherAbstract
	 */
	private $patcher;

	/**
	 * DataProviderShortcodePreconversionPatcher.
	 *
	 * @var DataProviderShortcodePreconversionPatcher
	 */
	private $data_provider;

	/**
	 * Override setUp.
	 */
	public function setUp() {
		$this->patcher       = new ShortcodePreconversionPatcher();
		$this->data_provider = new DataProviderShortcodePreconversionPatcher();
	}

	/**
	 * If a gallery shortcode is not starting on a new line, break it in to a new line.
	 */
	public function test_prepend_gallery_shortcodes_with_new_line() {
		$html_before_patching = $this->data_provider->get_html_with_gallery_shortcodes_mixed();
		$expected             = $this->data_provider->get_html_with_gallery_shortcodes_mixed_expected();

		$actual = $this->patcher->patch_html_source( $html_before_patching );
		$this->assertSame( $expected, $actual );
	}
}
