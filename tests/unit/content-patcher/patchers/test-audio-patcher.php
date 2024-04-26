<?php
/**
 * Test class for the NewspackContentConverter\ContentPatcher\Patchers\AudioPatcher.
 *
 * @package Newspack
 */

namespace NewspackContentConverterTest;

use WP_UnitTestCase;
use NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract;
use NewspackContentConverter\ContentPatcher\Patchers\AudioPatcher;
use NewspackContentConverterTest\DataProviderAudioPatcher;

/**
 * Class TestAudioPatcher
 */
class TestAudioPatcher extends WP_UnitTestCase {

	/**
	 * AudioPatcher.
	 *
	 * @var PatcherAbstract
	 */
	private $patcher;

	/**
	 * DataProviderAudioPatcher.
	 *
	 * @var DataProviderAudioPatcher
	 */
	private $data_provider;

	/**
	 * Override setUp.
	 */
	public function setUp(): void {
		$this->patcher       = new AudioPatcher();
		$this->data_provider = new DataProviderAudioPatcher();
	}

	/**
	 * The patcher should patch the lost audio element.
	 */
	public function test_should_patch_lost_audio_element() {
		$html                   = $this->data_provider->get_lost_audio_html();
		$blocks_before_patching = $this->data_provider->get_lost_audio_blocks_before_patching();
		$expected               = $this->data_provider->get_lost_audio_blocks_patched_expected();

		$actual  = $this->patcher->patch_blocks_contents( $blocks_before_patching, $html, 1 );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Tests that blocks code will not get modified if running into an inconsistency btw. HTML and non-patched blocks code.
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
	 * Should patch multiple audios.
	 */
	public function test_should_patch_multiple_audio_elements() {
		$html                   = $this->data_provider->get_multiple_audios_html();
		$blocks_before_patching = $this->data_provider->get_multiple_audio_blocks_before_patching();
		$expected               = $this->data_provider->get_multiple_audio_blocks_patched_expected();

		$actual  = $this->patcher->patch_blocks_contents( $blocks_before_patching, $html, 1 );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * This is a case where not all audios have the src attribute. Patcher should not patch those that don't.
	 */
	public function test_should_skip_patching_audios_which_dont_have_a_valid_src_attribute() {
		$html                   = $this->data_provider->get_some_skipped_audios_html();
		$blocks_before_patching = $this->data_provider->get_some_skipped_audios_blocks_before_patching();
		$expected               = $this->data_provider->get_some_skipped_audios_blocks_patched_expected();

		$actual  = $this->patcher->patch_blocks_contents( $blocks_before_patching, $html, 1 );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * This is a case where not all audio elements are valid. Patcher should not patch those.
	 */
	public function test_should_skip_patching_invalid_audios() {
		$html                   = $this->data_provider->get_some_invalid_audios_html();
		$blocks_before_patching = $this->data_provider->get_some_invalid_audios_blocks_before_patching();
		$expected               = $this->data_provider->get_some_invalid_audios_blocks_patched_expected();

		$actual  = $this->patcher->patch_blocks_contents( $blocks_before_patching, $html, 1 );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * This test will become important when Gutenberg fixes conversion of this particular case, so we need to make sure
	 * we won't patch it twice.
	 */
	public function test_should_skip_patching_audios_that_already_have_the_dir_attribute() {
		$html                   = $this->data_provider->get_some_audios_ok_html();
		$blocks_before_patching = $this->data_provider->get_some_audios_ok_blocks_before_patching();
		$expected               = $this->data_provider->get_some_audios_ok_blocks_patched_expected();

		$actual  = $this->patcher->patch_blocks_contents( $blocks_before_patching, $html, 1 );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Runs a comprehensive example and checks for validity.
	 */
	public function test_should_correctly_patch_a_comprehensive_audio_conversion_example() {
		$html                   = $this->data_provider->get_comprehensive_html();
		$blocks_before_patching = $this->data_provider->get_comprehensive_blocks_before_patching();
		$expected               = $this->data_provider->get_comprehensive_blocks_patched_expected();

		$actual  = $this->patcher->patch_blocks_contents( $blocks_before_patching, $html, 1 );

		$this->assertSame( $expected, $actual );
	}
}
