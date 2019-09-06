<?php
/**
 * Test class for the NewspackContentConverter\ContentPatcher\Patchers\ParagraphPatcher.
 *
 * @package Newspack
 */

use NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract;
use NewspackContentConverter\ContentPatcher\Patchers\ParagraphPatcher;

/**
 * Class TestParagraphPatcher
 */
class TestParagraphPatcher extends WP_UnitTestCase {

	/**
	 * Paragraph Patcher.
	 *
	 * @var PatcherAbstract
	 */
	private $patcher;

	/**
	 * DataProviderParagraphPatcher.
	 *
	 * @var DataProviderParagraphPatcher
	 */
	private $data_provider;

	/**
	 * Override setUp.
	 */
	public function setUp() {
		$this->fixtures_dir = dirname( __FILE__ ) . '/../../../fixtures/unit/content-patcher/patchers/';

		require_once $this->fixtures_dir . 'class-dataproviderparagraphpatcher.php';

		$this->patcher       = new ParagraphPatcher();
		$this->data_provider = new DataProviderParagraphPatcher();
	}

	/**
	 * The patcher shoud patch the lost dir attribute.
	 */
	public function test_should_patch_dir_attribute() {
		$html                   = '<p dir="ltr">AAA</p>';
		$blocks_before_patching = '<p>AAA</p>';
		$expected               = '<p dir="ltr">AAA</p>';

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );

	}

	/**
	 * Tests that blocks code will not get modified if running into an inconsistency between HTML and non-patched blocks code.
	 */
	public function test_should_not_modify_source_if_html_is_html_code_inconsistent_with_blocks_code() {
		$html                   = $this->data_provider->get_inconsistent_sources_html();
		$blocks_before_patching = $this->data_provider->get_inconsistent_sources_before_patching();
		$expected               = $this->data_provider->get_inconsistent_sources_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Tests that blocks code will not get modified if HTML code is not supposed to be patched by this patcher.
	 */
	public function test_should_not_modify_source_if_html_not_pertinent_to_this_patcher() {
		$html                   = $this->data_provider->get_html_is_non_pertinent_html();
		$blocks_before_patching = $this->data_provider->get_html_is_non_pertinent_before_patching();
		$expected               = $this->data_provider->get_html_is_non_pertinent_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should patch multiple paragraphs.
	 */
	public function test_should_patch_dir_attribute_on_multiple_paragraph_elements() {
		$html                   = $this->data_provider->get_multiple_paragraphs_html();
		$blocks_before_patching = $this->data_provider->get_multiple_paragraphs_blocks_before_patching();
		$expected               = $this->data_provider->get_multiple_paragraphs_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * This is a case where not all paragraphs have the dir attribute. Patcher should not patch those that don't.
	 */
	public function test_should_skip_patching_paragraphs_which_dont_have_the_dir_attribute() {
		$html                   = $this->data_provider->get_some_skipped_paragraphs_html();
		$blocks_before_patching = $this->data_provider->get_some_skipped_paragraphs_blocks_before_patching();
		$expected               = $this->data_provider->get_some_skipped_paragraphs_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * This test will become important when Gutenberg fixes conversion of this particular case, so we need to make sure
	 * we won't patch it twice.
	 */
	public function test_should_skip_patching_paragraphs_that_already_have_the_dir_attribute() {
		$html                   = $this->data_provider->get_some_paragraphs_ok_html();
		$blocks_before_patching = $this->data_provider->get_some_paragraphs_ok_blocks_before_patching();
		$expected               = $this->data_provider->get_some_paragraphs_ok_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Runs a comprehensive example and checks for validity.
	 */
	public function test_should_correctly_patch_a_comprehensive_paragraph_conversion_example() {
		$html                   = $this->data_provider->get_comprehensive_html();
		$blocks_before_patching = $this->data_provider->get_comprehensive_blocks_before_patching();
		$expected               = $this->data_provider->get_comprehensive_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}
}
