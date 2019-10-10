<?php
/**
 * Test class for the NewspackContentConverter\ContentPatcher\Patchers\VideoPatcher.
 *
 * @package Newspack
 */

use NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract;
use NewspackContentConverter\ContentPatcher\Patchers\VideoPatcher;

/**
 * Class TestVideoPatcher
 */
class TestVideoPatcher extends WP_UnitTestCase {

	/**
	 * VideoPatcher.
	 *
	 * @var PatcherAbstract
	 */
	private $patcher;

	/**
	 * DataProviderVideoPatcher.
	 *
	 * @var DataProviderVideoPatcher
	 */
	private $data_provider;

	/**
	 * Override setUp.
	 */
	public function setUp() {
		$this->fixtures_dir = dirname( __FILE__ ) . '/../../../fixtures/unit/content-patcher/patchers/';

		require_once $this->fixtures_dir . 'class-dataprovidervideopatcher.php';

		$this->patcher       = new VideoPatcher();
		$this->data_provider = new DataProviderVideoPatcher();
	}

	/**
	 * The patcher should patch the lost video element.
	 */
	public function test_should_patch_lost_video_element() {
		$html                   = $this->data_provider->get_lost_video_html();
		$blocks_before_patching = $this->data_provider->get_lost_video_blocks_before_patching();
		$expected               = $this->data_provider->get_lost_video_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Tests that blocks code will not get modified if running into an inconsistency btw. HTML and non-patched blocks code.
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
	 * Should patch multiple videos.
	 */
	public function test_should_patch_multiple_video_elements() {
		$html                   = $this->data_provider->get_multiple_videos_html();
		$blocks_before_patching = $this->data_provider->get_multiple_video_blocks_before_patching();
		$expected               = $this->data_provider->get_multiple_video_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * This is a case where not all videos have the src attribute. Patcher should not patch those that don't.
	 */
	public function test_should_skip_patching_videos_which_dont_have_a_valid_src_attribute() {
		$html                   = $this->data_provider->get_some_skipped_videos_html();
		$blocks_before_patching = $this->data_provider->get_some_skipped_videos_blocks_before_patching();
		$expected               = $this->data_provider->get_some_skipped_videos_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * This is a case where not all video elements are valid. Patcher should not patch those.
	 */
	public function test_should_skip_patching_invalid_videos() {
		$html                   = $this->data_provider->get_some_invalid_videos_html();
		$blocks_before_patching = $this->data_provider->get_some_invalid_videos_blocks_before_patching();
		$expected               = $this->data_provider->get_some_invalid_videos_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * This test will become important when Gutenberg fixes conversion of this particular case, so we need to make sure
	 * we won't patch it twice.
	 */
	public function test_should_skip_patching_videos_that_already_have_the_dir_attribute() {
		$html                   = $this->data_provider->get_some_videos_ok_html();
		$blocks_before_patching = $this->data_provider->get_some_videos_ok_blocks_before_patching();
		$expected               = $this->data_provider->get_some_videos_ok_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}

	/**
	 * Runs a comprehensive example and checks for validity.
	 */
	public function test_should_correctly_patch_a_comprehensive_video_conversion_example() {
		$html                   = $this->data_provider->get_comprehensive_html();
		$blocks_before_patching = $this->data_provider->get_comprehensive_blocks_before_patching();
		$expected               = $this->data_provider->get_comprehensive_blocks_patched_expected();

		$actual = $this->patcher->patch_blocks_contents( $html, $blocks_before_patching );

		$this->assertSame( $expected, $actual );
	}
}
