<?php
/**
 * Test class for the NewspackContentConverter\ContentPatcher\Patchers\ShortcodeModulePatcher.
 *
 * @package Newspack
 */

namespace NewspackContentConverterTest;

use WP_UnitTestCase;
use NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract;
use NewspackContentConverter\ContentPatcher\Patchers\ShortcodeModulePatcher;
use NewspackContentConverterTest\DataProviderShortcodeModulePatcher;

/**
 * Class ShortcodeModulePatcher
 */
class TestModuleShortcodePatcher extends WP_UnitTestCase {

	/**
	 * ShortcodeModulePatcher.
	 *
	 * @var PatcherAbstract
	 */
	private $patcher;

	/**
	 * DataProviderShortcodeModulePatcher.
	 *
	 * @var DataProviderShortcodeModulePatcher
	 */
	private $data_provider;

	/**
	 * Override setUp.
	 */
	public function setUp() {
		$this->patcher       = new ShortcodeModulePatcher();
		$this->data_provider = new DataProviderShortcodeModulePatcher();
	}

	/**
	 * Test a left-aligned module shortcode conversion.
	 */
	public function test_patch_module_shortcode_left() {
		$blocks_before_patching = $this->data_provider->get_unpatched_block_left();
		$expected               = $this->data_provider->get_patched_block_left_expected();

		$actual = $this->patcher->patch_blocks_contents( '', $blocks_before_patching );
		$this->assertSame( $expected, $actual );
	}

	/**
	 * Test a right-aligned module shortcode conversion.
	 */
	public function test_patch_module_shortcode_right() {
		$blocks_before_patching = $this->data_provider->get_unpatched_block_right();
		$expected               = $this->data_provider->get_patched_block_right_expected();

		$actual = $this->patcher->patch_blocks_contents( '', $blocks_before_patching );
		$this->assertSame( $expected, $actual );
	}

	/**
	 * Test center/non-aligned module shortcode conversion.
	 */
	public function test_patch_module_shortcode_center() {
		$blocks_before_patching = $this->data_provider->get_unpatched_blocks_center();
		$expected               = $this->data_provider->get_patched_blocks_center_expected();

		$actual = $this->patcher->patch_blocks_contents( '', $blocks_before_patching );
		$this->assertSame( $expected, $actual );
	}

	/**
	 * Test a module shortcode conversion where the source module has unsupported HTML elements in it.
	 */
	public function test_patch_module_unsupported_tags() {
		$blocks_before_patching = $this->data_provider->get_unpatched_block_unsupported_tags();
		$expected               = $this->data_provider->get_patched_block_unsupported_tags_expected();

		$actual = $this->patcher->patch_blocks_contents( '', $blocks_before_patching );
		$this->assertSame( $expected, $actual );
	}

	/**
	 * Test a module shortcode conversion on data that doesn't need converting.
	 */
	public function test_patch_module_non_pertinent() {
		$blocks_before_patching = $this->data_provider->get_unpatched_blocks_non_pertinent();
		$expected               = $this->data_provider->get_patched_blocks_non_pertinent_expected();

		$actual = $this->patcher->patch_blocks_contents( '', $blocks_before_patching );
		$this->assertSame( $expected, $actual );
	}
}
