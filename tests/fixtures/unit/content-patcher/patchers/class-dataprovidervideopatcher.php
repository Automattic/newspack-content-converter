<?php
/**
 * Data Provider for tests.
 *
 * @package Newspack
 */

namespace NewspackContentConverterTest;

/**
 * Class DataProviderParagraphPatcher
 *
 * Data Provider for tests concerning unit/content-patcher/patchers/test-paragraph-patcher.php
 */
class DataProviderVideoPatcher {

	/**
	 * HTML source for a comprehensive example, containing multiple elements and different cases.
	 *
	 * @return string HTML
	 */
	public static function get_comprehensive_html() {
		return <<<CONTENT
<p>Some content before</p>
[video mp4="https://host/video1.mp4"][/video]
<p>Following video does not have a valid source</p>
[video][/video]
<p>Following video will already be properly converted</p>
[video mp4="https://host/video2.mp4"][/video]
<p>Following is not a valid video element</p>
[video]
<p>One more video</p>
[video mp4="https://host/video3.mp4"][/video]
<p>Some content in the end</p>
CONTENT;
	}

	/**
	 * Blocks contents before patching, for a comprehensive example, containing multiple elements and different cases.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_comprehensive_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p>Some content before</p>
<!-- /wp:paragraph -->

<!-- wp:video -->
<figure class="wp-block-video"></figure>
<!-- /wp:video -->

<!-- wp:paragraph -->
<p>Following video does not have a valid source</p>
<!-- /wp:paragraph -->

<!-- wp:video -->
<figure class="wp-block-video"></figure>
<!-- /wp:video -->

<!-- wp:paragraph -->
<p>Following video will already be properly converted</p>
<!-- /wp:paragraph -->

<!-- wp:video -->
<figure class="wp-block-video"><video controls src="https://host/video2.mp4"></video></figure>
<!-- /wp:video -->

<!-- wp:paragraph -->
<p>Following is not a valid video element</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
[video]
<!-- /wp:html -->

<!-- wp:paragraph -->
<p>One more video</p>
<!-- /wp:paragraph -->

<!-- wp:video -->
<figure class="wp-block-video"></figure>
<!-- /wp:video -->

<!-- wp:paragraph -->
<p>Some content in the end</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for a comprehensive example, containing multiple elements and different cases.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_comprehensive_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p>Some content before</p>
<!-- /wp:paragraph -->

<!-- wp:video -->
<figure class="wp-block-video"><video controls src="https://host/video1.mp4"></video></figure>
<!-- /wp:video -->

<!-- wp:paragraph -->
<p>Following video does not have a valid source</p>
<!-- /wp:paragraph -->

<!-- wp:video -->
<figure class="wp-block-video"></figure>
<!-- /wp:video -->

<!-- wp:paragraph -->
<p>Following video will already be properly converted</p>
<!-- /wp:paragraph -->

<!-- wp:video -->
<figure class="wp-block-video"><video controls src="https://host/video2.mp4"></video></figure>
<!-- /wp:video -->

<!-- wp:paragraph -->
<p>Following is not a valid video element</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
[video]
<!-- /wp:html -->

<!-- wp:paragraph -->
<p>One more video</p>
<!-- /wp:paragraph -->

<!-- wp:video -->
<figure class="wp-block-video"><video controls src="https://host/video3.mp4"></video></figure>
<!-- /wp:video -->

<!-- wp:paragraph -->
<p>Some content in the end</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * HTML source for lost video.
	 *
	 * @return string HTML
	 */
	public static function get_lost_video_html() {
		return <<<CONTENT
[video width="640" height="360" mp4="https://host/wp-content/uploads/2019/08/video.mp4"][/video]
CONTENT;
	}

	/**
	 * Blocks contents before patching for lost video.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_lost_video_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:video -->
<figure class="wp-block-video"></figure>
<!-- /wp:video -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching for lost video.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_lost_video_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:video -->
<figure class="wp-block-video"><video controls src="https://host/wp-content/uploads/2019/08/video.mp4"></video></figure>
<!-- /wp:video -->
CONTENT;
	}

	/**
	 * HTML source with multiple paragraphs.
	 *
	 * @return string HTML
	 */
	public static function get_multiple_videos_html() {
		return <<<CONTENT
[video width="640" height="360" mp4="https://host/video1.mp4"][/video]
[video width="640" height="360" mp4="https://host/video2.mp4"][/video]
[video width="640" height="360" mp4="https://host/video3.mp4"][/video]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for an example containing multiple paragraphs.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_multiple_video_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:video -->
<figure class="wp-block-video"></figure>
<!-- /wp:video -->

<!-- wp:video -->
<figure class="wp-block-video"></figure>
<!-- /wp:video -->

<!-- wp:video -->
<figure class="wp-block-video"></figure>
<!-- /wp:video -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for an example containing multiple paragraphs.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_multiple_video_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:video -->
<figure class="wp-block-video"><video controls src="https://host/video1.mp4"></video></figure>
<!-- /wp:video -->

<!-- wp:video -->
<figure class="wp-block-video"><video controls src="https://host/video2.mp4"></video></figure>
<!-- /wp:video -->

<!-- wp:video -->
<figure class="wp-block-video"><video controls src="https://host/video3.mp4"></video></figure>
<!-- /wp:video -->
CONTENT;
	}

	/**
	 * HTML source with multiple videos out of which some don't have a valid src attribute.
	 *
	 * @return string HTML
	 */
	public static function get_some_skipped_videos_html() {
		return <<<CONTENT
[video width="640" height="360" mp4="https://host/video1.mp4"][/video]
[video][/video]
[video width="640" height="360" mp4="https://host/video3.mp4"][/video]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for an example where some videos don't have a valid src.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_some_skipped_videos_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:video -->
<figure class="wp-block-video"></figure>
<!-- /wp:video -->

<!-- wp:video -->
<figure class="wp-block-video"></figure>
<!-- /wp:video -->

<!-- wp:video -->
<figure class="wp-block-video"></figure>
<!-- /wp:video -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for an example where some videos don't have a valid src.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_some_skipped_videos_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:video -->
<figure class="wp-block-video"><video controls src="https://host/video1.mp4"></video></figure>
<!-- /wp:video -->

<!-- wp:video -->
<figure class="wp-block-video"></figure>
<!-- /wp:video -->

<!-- wp:video -->
<figure class="wp-block-video"><video controls src="https://host/video3.mp4"></video></figure>
<!-- /wp:video -->
CONTENT;
	}

	/**
	 * HTML source with multiple videos out of which some aren't properly formatted -- are in fact not valid videos.
	 *
	 * @return string HTML
	 */
	public static function get_some_invalid_videos_html() {
		return <<<CONTENT
[video width="640" height="360" mp4="https://host/video1.mp4"][/video]
[video]
[video width="640" height="360" mp4="https://host/video3.mp4"][/video]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for an example where some videos are invalid.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_some_invalid_videos_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:video -->
<figure class="wp-block-video"></figure>
<!-- /wp:video -->

<!-- wp:html -->
[video]
<!-- /wp:html -->

<!-- wp:video -->
<figure class="wp-block-video"></figure>
<!-- /wp:video -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for an example where some videos are invalid.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_some_invalid_videos_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:video -->
<figure class="wp-block-video"><video controls src="https://host/video1.mp4"></video></figure>
<!-- /wp:video -->

<!-- wp:html -->
[video]
<!-- /wp:html -->

<!-- wp:video -->
<figure class="wp-block-video"><video controls src="https://host/video3.mp4"></video></figure>
<!-- /wp:video -->
CONTENT;
	}

	/**
	 * HTML source with multiple videos where some will not have their content lost.
	 *
	 * @return string HTML
	 */
	public static function get_some_videos_ok_html() {
		return <<<CONTENT
[video width="640" height="360" mp4="https://host/video1.mp4"][/video]
[video width="640" height="360" mp4="https://host/video2.mp4"][/video]
[video width="640" height="360" mp4="https://host/video3.mp4"][/video]
CONTENT;
	}

	/**
	 * Blocks contents before patching, multiple videos where some will not have their content lost.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_some_videos_ok_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:video -->
<figure class="wp-block-video"></figure>
<!-- /wp:video -->

<!-- wp:video -->
<figure class="wp-block-video"><video controls src="https://host/video2.mp4"></video></figure>
<!-- /wp:video -->

<!-- wp:video -->
<figure class="wp-block-video"></figure>
<!-- /wp:video -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, multiple videos where some will not have their content lost.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_some_videos_ok_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:video -->
<figure class="wp-block-video"><video controls src="https://host/video1.mp4"></video></figure>
<!-- /wp:video -->

<!-- wp:video -->
<figure class="wp-block-video"><video controls src="https://host/video2.mp4"></video></figure>
<!-- /wp:video -->

<!-- wp:video -->
<figure class="wp-block-video"><video controls src="https://host/video3.mp4"></video></figure>
<!-- /wp:video -->
CONTENT;
	}

	/**
	 * HTML source for for inconsistent sources.
	 *
	 * @return string HTML
	 */
	public static function get_inconsistent_sources_html() {
		return <<<CONTENT
[video width="640" height="360" mp4="https://host/video1.mp4"][/video]
CONTENT;
	}

	/**
	 * Blocks contents before patching for for inconsistent sources.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_inconsistent_sources_before_patching() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p>Some inconsistent blocks content, not supposed to get modified</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching for inconsistent sources.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_inconsistent_sources_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p>Some inconsistent blocks content, not supposed to get modified</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * HTML source for HTML is non pertinent.
	 *
	 * @return string HTML
	 */
	public static function get_html_is_non_pertinent_html() {
		return <<<CONTENT
<p>This has nothing to do with the patcher</p>
CONTENT;
	}

	/**
	 * Blocks contents before patching for HTML is non pertinent.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_html_is_non_pertinent_before_patching() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p>This has nothing to do with the patcher</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching for HTML is non pertinent.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_html_is_non_pertinent_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:paragraph -->
<p>This has nothing to do with the patcher</p>
<!-- /wp:paragraph -->
CONTENT;
	}
}
