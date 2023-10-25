<?php
/**
 * Test class for the NewspackContentConverter\ContentPatcher\Patchers\ImgPatcher.
 *
 * @package Newspack
 */

namespace NewspackContentConverterTest;

use WP_UnitTestCase;
use NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract;
use NewspackContentConverter\ContentPatcher\Patchers\ImgPatcher;
use NewspackContentConverterTest\DataProviderImgPatcher;

/**
 * Class TestImgPatcher
 */
class TestImgPatcher extends WP_UnitTestCase {

	/**
	 * ImgPatcher.
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
		$this->img_patcher   = new ImgPatcher();
		$this->data_provider = new DataProviderImgPatcher();
	}

	/**
	 * The patcher should patch the lost height attribute.
	 */
	public function test_should_patch_height_attribute_for_image_element() {
		$html                   = $this->data_provider->get_should_patch_height_attribute_for_image_element_html();
		$blocks_before_patching = $this->data_provider->get_should_patch_height_attribute_for_image_element_blocks_before_patching();
		$expected               = $this->data_provider->get_should_patch_height_attribute_for_image_element_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should patch img height attribute to image block header attribute.
	 */
	public function test_should_patch_img_height_attribute_to_image_block_header_attribute() {
		$html                   = $this->data_provider->get_patch_img_height_attribute_to_image_block_header_attribute_html();
		$blocks_before_patching = $this->data_provider->get_patch_img_height_attribute_to_image_block_header_attribute_before_patching();
		$expected               = $this->data_provider->get_patch_img_height_attribute_to_image_block_header_attribute_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	// TODO: refactor -- this should be a test for block attribute patch!
	/**
	 * Should not alter height attribute to block if already there.
	 */
	public function test_should_not_alter_height_attribute_to_block_if_already_there() {
		$html                   = $this->data_provider->get_not_alter_height_attribute_to_block_if_already_there_html();
		$blocks_before_patching = $this->data_provider->get_not_alter_height_attribute_to_block_if_already_there_before_patching();
		$expected               = $this->data_provider->get_not_alter_height_attribute_to_block_if_already_there_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	// TODO: refactor -- this should be a test for block attribute patch!
	/**
	 * Should update block height attribute to correct value if already there.
	 */
	public function test_should_update_block_height_attribute_to_correct_value_if_already_there() {
		$html                   = $this->data_provider->get_update_block_height_attribute_to_correct_value_if_already_there_html();
		$blocks_before_patching = $this->data_provider->get_update_block_height_attribute_to_correct_value_if_already_there_before_patching();
		$expected               = $this->data_provider->get_update_block_height_attribute_to_correct_value_if_already_there_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	// TODO: refactor -- this should be a test for block attribute patch!
	/**
	 * Should update height attribute to block if no other attributes yet present.
	 */
	public function test_should_update_height_attribute_to_block_if_no_other_attributes_yet_present() {
		$html                   = $this->data_provider->get_update_height_attribute_to_block_if_no_other_attributes_yet_present_html();
		$blocks_before_patching = $this->data_provider->get_update_height_attribute_to_block_if_no_other_attributes_yet_present_before_patching();
		$expected               = $this->data_provider->get_update_height_attribute_to_block_if_no_other_attributes_yet_present_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should patch img width attribute to image block header attribute.
	 */
	public function test_should_patch_img_width_attribute_to_image_block_header_attribute() {
		$html                   = $this->data_provider->get_patch_img_width_attribute_to_image_block_header_attribute_html();
		$blocks_before_patching = $this->data_provider->get_patch_img_width_attribute_to_image_block_header_attribute_before_patching();
		$expected               = $this->data_provider->get_patch_img_width_attribute_to_image_block_header_attribute_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	// TODO: refactor: this should be a test for block attribute patch!
	/**
	 * Should not alter width attribute to block if already there.
	 */
	public function test_should_not_alter_width_attribute_to_block_if_already_there() {
		$html                   = $this->data_provider->get_should_not_alter_width_attribute_to_block_if_already_there_html();
		$blocks_before_patching = $this->data_provider->get_should_not_alter_width_attribute_to_block_if_already_there_before_patching();
		$expected               = $this->data_provider->get_should_not_alter_width_attribute_to_block_if_already_there_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	// TODO: refactor -- this should be a test for block attribute patch!
	/**
	 * Should update block width attribute to correct value if already there.
	 */
	public function test_should_update_block_width_attribute_to_correct_value_if_already_there() {
		$html                   = $this->data_provider->get_update_block_width_attribute_to_correct_value_if_already_there_html();
		$blocks_before_patching = $this->data_provider->get_update_block_width_attribute_to_correct_value_if_already_there_before_patching();
		$expected               = $this->data_provider->get_update_block_width_attribute_to_correct_value_if_already_there_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	// TODO: refactor -- this should be a test for block attribute patch!
	/**
	 * Should update width attribute to block if no other attributes yet present.
	 */
	public function test_should_update_width_attribute_to_block_if_no_other_attributes_yet_present() {
		$html                   = $this->data_provider->get_update_width_attribute_to_block_if_no_other_attributes_yet_present_html();
		$blocks_before_patching = $this->data_provider->get_update_width_attribute_to_block_if_no_other_attributes_yet_present_before_patching();
		$expected               = $this->data_provider->get_update_width_attribute_to_block_if_no_other_attributes_yet_present_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should not set isresized class to figure if neither height nor width were patched.
	 */
	public function test_should_not_set_isresized_class_to_figure_if_neither_height_nor_width_were_patched() {
		$html                   = $this->data_provider->get_should_not_set_isresized_class_to_figure_if_neither_height_nor_width_were_patched_html();
		$blocks_before_patching = $this->data_provider->get_should_not_set_isresized_class_to_figure_if_neither_height_nor_width_were_patched_before_patching();
		$expected               = $this->data_provider->get_should_not_set_isresized_class_to_figure_if_neither_height_nor_width_were_patched_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	// TODO: refactor -- this should be a test for block element attribute patch!
	/**
	 * Figure element should get isresized class.
	 */
	public function test_figure_element_should_get_isresized_class() {
		$html                   = $this->data_provider->get_figure_element_should_get_isresized_class_html();
		$blocks_before_patching = $this->data_provider->get_figure_element_should_get_isresized_class_before_patching();
		$expected               = $this->data_provider->get_figure_element_should_get_isresized_class_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	// TODO: refactor -- this should be a test for block element attribute patch!
	/**
	 * Figure element should get isresized class appended to other classes.
	 */
	public function test_figure_element_should_get_isresized_class_appended_to_other_classes() {
		$html                   = $this->data_provider->get_figure_element_should_get_isresized_class_appended_to_other_classes_html();
		$blocks_before_patching = $this->data_provider->get_figure_element_should_get_isresized_class_appended_to_other_classes_before_patching();
		$expected               = $this->data_provider->get_figure_element_should_get_isresized_class_appended_to_other_classes_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	// TODO: refactor -- this should be a test for block element attribute patch!
	/**
	 * Figure element should not get isresized class if already present.
	 */
	public function test_figure_element_should_not_get_isresized_class_if_already_present() {
		$html                   = $this->data_provider->get_figure_element_should_not_get_isresized_class_if_already_present_html();
		$blocks_before_patching = $this->data_provider->get_figure_element_should_not_get_isresized_class_if_already_present_before_patching();
		$expected               = $this->data_provider->get_figure_element_should_not_get_isresized_class_if_already_present_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Tests that blocks code will not get modified if running into an inconsistency btw. HTML and non-patched blocks code.
	 */
	public function test_should_not_modify_source_if_html_is_html_code_inconsistent_with_blocks_code() {
		$html                   = $this->data_provider->get_inconsistent_sources_html();
		$blocks_before_patching = $this->data_provider->get_inconsistent_sources_before_patching();
		$expected               = $this->data_provider->get_inconsistent_sources_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Tests that blocks code will not get modified if HTML code is not supposed to be patched by this patcher.
	 */
	public function test_should_not_modify_source_if_html_not_pertinent_to_this_patcher() {
		$html                   = $this->data_provider->get_html_is_non_pertinent_html();
		$blocks_before_patching = $this->data_provider->get_html_is_non_pertinent_before_patching();
		$expected               = $this->data_provider->get_html_is_non_pertinent_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should patch multiple images.
	 */
	public function test_should_patch_multiple_image_elements() {
		$html                   = $this->data_provider->get_multiple_imgs_html();
		$blocks_before_patching = $this->data_provider->get_multiple_imgs_blocks_before_patching();
		$expected               = $this->data_provider->get_multiple_imgs_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * This is a case where not all images have the height attribute. Patcher should not patch those.
	 */
	public function test_should_skip_patching_images_which_dont_have_the_height_attribute() {
		$html                   = $this->data_provider->get_some_skipped_imgs_html();
		$blocks_before_patching = $this->data_provider->get_some_skipped_imgs_blocks_before_patching();
		$expected               = $this->data_provider->get_some_skipped_imgs_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * This test will become important when Gutenberg fixes conversion of this particular attribute, so we need to make sure
	 * we won't patch it twice.
	 */
	public function test_should_skip_patching_images_that_already_have_the_attribute() {
		$html                   = $this->data_provider->get_some_imgs_ok_html();
		$blocks_before_patching = $this->data_provider->get_some_imgs_ok_blocks_before_patching();
		$expected               = $this->data_provider->get_some_imgs_ok_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Runs a comprehensive example and checks for validity.
	 */
	public function test_should_correctly_patch_a_comprehensive_conversion_example() {
		$html                   = $this->data_provider->get_comprehensive_html();
		$blocks_before_patching = $this->data_provider->get_comprehensive_blocks_before_patching();
		$expected               = $this->data_provider->get_comprehensive_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * The patcher should patch the lost width attribute.
	 */
	public function test_should_patch_width_attribute() {
		$html                   = $this->data_provider->get_should_patch_width_attribute_html();
		$blocks_before_patching = $this->data_provider->get_should_patch_width_attribute_before_patching();
		$expected               = $this->data_provider->get_should_patch_width_attribute_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Tests if align="left" gets patched correctly.
	 */
	public function test_should_correctly_patch_align_left_attribute() {
		$html                   = $this->data_provider->get_align_left_html();
		$blocks_before_patching = $this->data_provider->get_align_left_blocks_before_patching();
		$expected               = $this->data_provider->get_align_left_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Tests if align="right" gets patched correctly.
	 */
	public function test_should_correctly_patch_align_right_attribute() {
		$html                   = $this->data_provider->get_align_right_html();
		$blocks_before_patching = $this->data_provider->get_align_right_blocks_before_patching();
		$expected               = $this->data_provider->get_align_right_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should correctly patch align right attribute and append to existing block comment header attributes.
	 */
	public function test_should_correctly_patch_align_right_attribute_and_append_to_existing_block_comment_header_attributes() {
		$html                   = $this->data_provider->get_patch_align_and_append_to_existing_block_comment_attributes_html();
		$blocks_before_patching = $this->data_provider->get_patch_align_and_append_to_existing_block_comment_attributes_blocks_before_patching();
		$expected               = $this->data_provider->get_patch_align_and_append_to_existing_block_comment_attributes_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should skip patching unknown align value.
	 */
	public function test_should_skip_patching_unknown_align_value() {
		$html                   = $this->data_provider->get_skip_patching_unknown_align_value_html();
		$blocks_before_patching = $this->data_provider->get_skip_patching_unknown_align_value_blocks_before_patching();
		$expected               = $this->data_provider->get_skip_patching_unknown_align_value_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should skip patching align already in header and figure class.
	 */
	public function test_should_skip_patching_align_already_in_header_and_figure_class() {
		$html                   = $this->data_provider->get_skip_patching_align_already_in_header_and_figure_class_html();
		$blocks_before_patching = $this->data_provider->get_skip_patching_align_already_in_header_and_figure_class_blocks_before_patching();
		$expected               = $this->data_provider->get_skip_patching_align_already_in_header_and_figure_class_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should skip patching align already in figure class.
	 */
	public function test_should_skip_patching_align_already_in_figure_class() {
		$html                   = $this->data_provider->get_skip_patching_align_already_in_figure_class_html();
		$blocks_before_patching = $this->data_provider->get_skip_patching_align_already_in_figure_class_blocks_before_patching();
		$expected               = $this->data_provider->get_skip_patching_align_already_in_figure_class_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should skip patching align already in header.
	 */
	public function test_should_skip_patching_align_already_in_header() {
		$html                   = $this->data_provider->get_skip_patching_align_already_in_header_html();
		$blocks_before_patching = $this->data_provider->get_skip_patching_align_already_in_header_blocks_before_patching();
		$expected               = $this->data_provider->get_skip_patching_align_already_in_header_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should strip double align from div.
	 */
	public function test_should_strip_double_align_from_div() {
		$html                   = $this->data_provider->get_should_strip_double_align_from_div_html();
		$blocks_before_patching = $this->data_provider->get_should_strip_double_align_from_div_before_patching();
		$expected               = $this->data_provider->get_should_strip_double_align_from_div_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should skip patching if inconsistent sources.
	 */
	public function test_should_skip_patching_if_inconsistent_sources() {
		$html                   = $this->data_provider->get_skip_patchgin_if_inconsistent_sources_html();
		$blocks_before_patching = $this->data_provider->get_skip_patchgin_if_inconsistent_sources_blocks_before_patching();
		$expected               = $this->data_provider->get_skip_patchgin_if_inconsistent_sources_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should preserve all other elements existing attributes.
	 */
	public function test_should_preserve_all_other_elements_existing_attributes() {
		$html                   = $this->data_provider->get_patch_align_with_preserved_existing_elements_attributes_html();
		$blocks_before_patching = $this->data_provider->get_patch_align_with_preserved_existing_elements_attributes_blocks_before_patching();
		$expected               = $this->data_provider->get_patch_align_with_preserved_existing_elements_attributes_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );
		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should patch only imgs in img blocks.
	 */
	public function test_should_patch_only_imgs_in_img_blocks() {
		$html                   = $this->data_provider->get_should_patch_only_imgs_in_img_blocks_html();
		$blocks_before_patching = $this->data_provider->get_should_patch_only_imgs_in_img_blocks_before_patching();
		$expected               = $this->data_provider->get_should_patch_only_imgs_in_img_blocks_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Should patch only imgs in img blocks example two.
	 */
	public function test_should_patch_only_imgs_in_img_blocks_example_two() {
		$html                   = $this->data_provider->get_should_patch_only_imgs_in_img_blocks_example_two_html();
		$blocks_before_patching = $this->data_provider->get_should_patch_only_imgs_in_img_blocks_example_two_before_patching();
		$expected               = $this->data_provider->get_should_patch_only_imgs_in_img_blocks_example_two_blocks_patched_expected();

		$actual = $this->img_patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}
}
