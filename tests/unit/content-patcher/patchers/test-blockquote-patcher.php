<?php
/**
 * Test class for the NewspackContentConverter\ContentPatcher\Patchers\BlockquotePatcher.
 *
 * @package Newspack
 */

namespace NewspackContentConverterTest;

use WP_UnitTestCase;
use NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract;
use NewspackContentConverter\ContentPatcher\Patchers\BlockquotePatcher;

/**
 * Class TestBlockquotePatcher.
 */
class TestBlockquotePatcher extends WP_UnitTestCase {

	/**
	 * BlockquotePatcher.
	 *
	 * @var PatcherAbstract
	 */
	private $patcher;

	/**
	 * DataProviderBlockquotePatcher.
	 *
	 * @var DataProviderBlockquotePatcher
	 */
	private $data_provider;

	/**
	 * Override setUp.
	 */
	public function setUp(): void {
		$this->fixtures_dir = dirname( __FILE__ ) . '/../../../fixtures/unit/content-patcher/patchers/';

		require_once $this->fixtures_dir . 'class-dataproviderblockquotepatcher.php';

		$this->patcher       = new BlockquotePatcher();
		$this->data_provider = new DataProviderBlockquotePatcher();
	}

	/**
	 * The patcher shloud patch the lost data-lang attribute.
	 */
	public function test_should_patch_datalang_attribute() {
		$html                   = '<blockquote data-lang="es">AAA</blockquote>';
		$blocks_before_patching = '<blockquote>AAA</blockquote>';
		$expected               = '<blockquote data-lang="es">AAA</blockquote>';

		$actual  = $this->patcher->patch_blocks_contents( $blocks_before_patching, $html, 1 );

		$this->assertSame( $expected, $actual );

	}

	/**
	 * Tests that blocks code will not get modified if running into an inconsistency between HTML and non-patched blocks code.
	 */
	public function test_should_not_modify_source_if_html_is_html_code_inconsistent_with_blocks_code() {
		$html                   = $this->data_provider->get_inconsistent_sources_html();
		$blocks_before_patching = $this->data_provider->get_inconsistent_sources_before_patching();
		$expected               = $this->data_provider->get_inconsistent_sources_blocks_patched_expected();

		$actual  = $this->patcher->patch_blocks_contents( $blocks_before_patching, $html, 1 );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Tests that blocks code will not get modified if HTML code is not supposed to be patched by this patcher.
	 */
	public function test_should_not_modify_source_if_html_not_pertinent_to_this_patcher() {
		$html                   = $this->data_provider->get_html_is_non_pertinent_html();
		$blocks_before_patching = $this->data_provider->get_html_is_non_pertinent_before_patching();
		$expected               = $this->data_provider->get_html_is_non_pertinent_blocks_patched_expected();

		$actual  = $this->patcher->patch_blocks_contents( $blocks_before_patching, $html, 1 );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should patch multiple blockquotes.
	 */
	public function test_should_patch_datalang_attribute_on_multiple_paragraph_elements() {
		$html                   = $this->data_provider->get_multiple_blockquotes_html();
		$blocks_before_patching = $this->data_provider->get_multiple_blockquotes_blocks_before_patching();
		$expected               = $this->data_provider->get_multiple_blockquotes_blocks_patched_expected();

		$actual  = $this->patcher->patch_blocks_contents( $blocks_before_patching, $html, 1 );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * This is a case where not all blockquotes have the data-lang attribute. Patcher should not patch those that don't.
	 */
	public function test_should_skip_patching_blockquotess_which_dont_have_the_datalang_attribute() {
		$html                   = $this->data_provider->get_some_skipped_blockquotes_html();
		$blocks_before_patching = $this->data_provider->get_some_skipped_blockquotes_blocks_before_patching();
		$expected               = $this->data_provider->get_some_skipped_blockquotes_blocks_patched_expected();

		$actual  = $this->patcher->patch_blocks_contents( $blocks_before_patching, $html, 1 );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * This test will become important when Gutenberg fixes conversion of this particular case, so we need to make sure
	 * we won't patch it twice.
	 */
	public function test_should_skip_patching_blockquotes_that_already_have_the_datalang_attribute() {
		$html                   = $this->data_provider->get_some_blockquotes_ok_html();
		$blocks_before_patching = $this->data_provider->get_some_blockquotes_ok_blocks_before_patching();
		$expected               = $this->data_provider->get_some_blockquotes_ok_blocks_patched_expected();

		$actual  = $this->patcher->patch_blocks_contents( $blocks_before_patching, $html, 1 );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Runs a comprehensive example and checks for validity.
	 */
	public function test_should_correctly_patch_a_comprehensive_blockquote_conversion_example() {
		$html                   = $this->data_provider->get_comprehensive_html();
		$blocks_before_patching = $this->data_provider->get_comprehensive_blocks_before_patching();
		$expected               = $this->data_provider->get_comprehensive_blocks_patched_expected();

		$actual  = $this->patcher->patch_blocks_contents( $blocks_before_patching, $html, 1 );

		$this->assertSame( $expected, $actual );
	}
}
