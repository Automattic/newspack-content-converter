<?php
/**
 * Test class for the NewspackContentConverter\ContentPatcher\Patchers\ShortcodePullquotePatcher.
 *
 * @package Newspack
 */

namespace NewspackContentConverterTest;

use WP_UnitTestCase;
use NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract;
use NewspackContentConverter\ContentPatcher\Patchers\ShortcodePullquotePatcher;
use NewspackContentConverterTest\DataProviderShortcodePullquotePatcher;

/**
 * Class ShortcodePullquotePatcher
 */
class TestPullquoteShortcodePatcher extends WP_UnitTestCase {

	/**
	 * ShortcodePullquotePatcher.
	 *
	 * @var PatcherAbstract
	 */
	private $patcher;

	/**
	 * DataProviderShortcodePullquotePatcher.
	 *
	 * @var DataProviderShortcodePullquotePatcher
	 */
	private $data_provider;

	/**
	 * Override setUp.
	 */
	public function setUp() {
		$this->patcher       = new ShortcodePullquotePatcher();
		$this->data_provider = new DataProviderShortcodePullquotePatcher();
	}

	/**
	 * Test a full pullquote shortcode conversion.
	 */
	public function test_patch_pullquote_shortcode() {
		$blocks_before_patching = $this->data_provider->get_unpatched_block();
		$expected               = $this->data_provider->get_patched_block_expected();

		$actual = $this->patcher->patch_blocks_contents( '', $blocks_before_patching );
		$this->assertSame( $expected, $actual );
	}

	/**
	 * Test a pullquote shortcode conversion where no author is defined.
	 */
	public function test_patch_pullquote_shortcode_no_author() {
		$blocks_before_patching = $this->data_provider->get_unpatched_block_no_author();
		$expected               = $this->data_provider->get_patched_block_no_author_expected();

		$actual = $this->patcher->patch_blocks_contents( '', $blocks_before_patching );
		$this->assertSame( $expected, $actual );
	}

	/**
	 * Test a pullquote shortcode conversion on data that doesn't need converting.
	 */
	public function test_patch_module_non_pertinent() {
		$blocks_before_patching = $this->data_provider->get_unpatched_blocks_non_pertinent();
		$expected               = $this->data_provider->get_patched_blocks_non_pertinent_expected();

		$actual = $this->patcher->patch_blocks_contents( '', $blocks_before_patching );
		$this->assertSame( $expected, $actual );
	}
}
