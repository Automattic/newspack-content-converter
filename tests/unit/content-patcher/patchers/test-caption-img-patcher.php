<?php
/**
 * Test class for the NewspackContentConverter\ContentPatcher\Patchers\CaptionImgPatcher.
 *
 * @package Newspack
 */

use NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract;
use NewspackContentConverter\ContentPatcher\Patchers\CaptionImgPatcher;

/**
 * Class TestCaptionImgPatcher
 */
class TestCaptionImgPatcher extends WP_UnitTestCase {

	/**
	 * CaptionImgPatcher.
	 *
	 * @var PatcherAbstract
	 */
	private $patcher;

	/**
	 * DataProviderCaptionImgPatcher.
	 *
	 * @var DataProviderCaptionImgPatcher
	 */
	private $data_provider;

	/**
	 * Override setUp.
	 */
	public function setUp() {
		$this->fixtures_dir = dirname( __FILE__ ) . '/../../../fixtures/unit/content-patcher/patchers/';

		require_once $this->fixtures_dir . 'class-dataprovidercaptionimgpatcher.php';

		$this->patcher       = new CaptionImgPatcher();
		$this->data_provider = new DataProviderCaptionImgPatcher();
	}

	/**
	 * The patcher should patch the lost caption from the [caption caption="this"]...[/caption] element's `caption` attribute.
	 */
	public function test_should_patch_caption_attribute() {
		$html                   = $this->data_provider->get_lost_caption_attribute_from_caption_element_html();
		$blocks_before_patching = $this->data_provider->get_lost_caption_attribute_from_caption_element_blocks_before_patching();
		$expected               = $this->data_provider->get_lost_caption_attribute_from_caption_element_blocks_patched_expected();

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
	 * Another example just like one above, only with "more crowded sources" (have more attributes, code in general).
	 */
	public function test_should_patch_caption_attribute_second_example() {
		$html                   = $this->data_provider->get_lost_caption_attribute_from_caption_element_html_2();
		$blocks_before_patching = $this->data_provider->get_lost_caption_attribute_from_caption_element_blocks_before_patching_2();
		$expected               = $this->data_provider->get_lost_caption_attribute_from_caption_element_blocks_patched_expected_2();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * This test will become important when Gutenberg fixes conversion of this particular attribute, so we need to make sure
	 * we won't patch it twice.
	 */
	public function test_should_skip_patching_captions_for_imagesimages_that_already_have_the_figcaption() {
		$html                   = $this->data_provider->get_some_img_captions_ok_html();
		$blocks_before_patching = $this->data_provider->get_some_img_captions_ok_blocks_before_patching();
		$expected               = $this->data_provider->get_some_img_captions_ok_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Captions may sometimes have extra spaces on the left or right of the captions. Here making sure those caption strings are
	 * properly filtered and captions not added twice.
	 */
	public function test_should_skip_patching_captions_for_imagesimages_that_already_have_the_figcaption_even_with_extra_spaces_on_left() {
		$html                   = $this->data_provider->get_some_img_captions_ok_even_with_extra_spaces_on_left_html();
		$blocks_before_patching = $this->data_provider->get_some_img_captions_ok_even_with_extra_spaces_on_left_blocks_before_patching();
		$expected               = $this->data_provider->get_some_img_captions_ok_even_with_extra_spaces_on_left_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * The patcher should also patch the lost caption from the inner text of the caption element, like this [caption]this[/caption].
	 */
	public function test_should_patch_caption_element_innertext() {
		$html                   = $this->data_provider->get_lost_caption_innertext();
		$blocks_before_patching = $this->data_provider->get_lost_caption_innertext_blocks_before_patching();
		$expected               = $this->data_provider->get_lost_caption_innertext_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should patch both caption element attribute and innertext.
	 */
	public function test_should_patch_both_caption_element_attribute_and_innertext() {
		$html                   = $this->data_provider->get_lost_caption_from_multiple_sources_innertext();
		$blocks_before_patching = $this->data_provider->get_lost_caption_from_multiple_sources_blocks_before_patching();
		$expected               = $this->data_provider->get_lost_caption_from_multiple_sources_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * This is a case where not all images have the height attribute. Patcher should not patch those.
	 */
	public function test_should_skip_patching_captions_where_value_not_defined() {
		$html                   = $this->data_provider->get_some_skipped_captions_html();
		$blocks_before_patching = $this->data_provider->get_some_skipped_captions_blocks_before_patching();
		$expected               = $this->data_provider->get_some_skipped_captions_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Runs a comprehensive example and checks for validity.
	 */
	public function test_should_correctly_patch_a_comprehensive_conversion_example() {
		$html                   = $this->data_provider->get_comprehensive_html();
		$blocks_before_patching = $this->data_provider->get_comprehensive_blocks_before_patching();
		$expected               = $this->data_provider->get_comprehensive_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should detect autoformatting changes in caption characters and not caption twice.
	 */
	public function test_should_detect_autoformatting_changes_in_caption_characters_and_not_caption_twice() {
		$html                   = $this->data_provider->get_autoformatting_changes_html();
		$blocks_before_patching = $this->data_provider->get_autoformatting_changes_blocks_before_patching();
		$expected               = $this->data_provider->get_autoformatting_changes_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}
}
