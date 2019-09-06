<?php
/**
 * Data Provider for tests.
 *
 * @package Newspack
 */

/**
 * Class DataProviderParagraphPatcher
 *
 * Data Provider for tests concerning unit/content-patcher/patchers/test-paragraph-patcher.php
 */
class DataProviderAudioPatcher {

	/**
	 * HTML source for a comprehensive example, containing multiple elements and different cases.
	 *
	 * @return string HTML
	 */
	public static function get_comprehensive_html() {
		return <<<CONTENT
<p>Some content before</p>
[audio mp3="https://host/audio1.mp3"][/audio]
<p>Following audio does not have a valid source</p>
[audio][/audio]
<p>Following audio will already be properly converted</p>
[audio mp3="https://host/audio2.mp3"][/audio]
<p>Following is not a valid audio element</p>
[audio]
<p>One more audio</p>
[audio mp3="https://host/audio3.mp3"][/audio]
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

<!-- wp:audio -->
<figure class="wp-block-audio"></figure>
<!-- /wp:audio -->

<!-- wp:paragraph -->
<p>Following audio does not have a valid source</p>
<!-- /wp:paragraph -->

<!-- wp:audio -->
<figure class="wp-block-audio"></figure>
<!-- /wp:audio -->

<!-- wp:paragraph -->
<p>Following audio will already be properly converted</p>
<!-- /wp:paragraph -->

<!-- wp:audio -->
<figure class="wp-block-audio"><audio controls src="https://host/audio2.mp3"></audio></figure>
<!-- /wp:audio -->

<!-- wp:paragraph -->
<p>Following is not a valid audio element</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
[audio]
<!-- /wp:html -->

<!-- wp:paragraph -->
<p>One more audio</p>
<!-- /wp:paragraph -->

<!-- wp:audio -->
<figure class="wp-block-audio"></figure>
<!-- /wp:audio -->

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

<!-- wp:audio -->
<figure class="wp-block-audio"><audio controls src="https://host/audio1.mp3"></audio></figure>
<!-- /wp:audio -->

<!-- wp:paragraph -->
<p>Following audio does not have a valid source</p>
<!-- /wp:paragraph -->

<!-- wp:audio -->
<figure class="wp-block-audio"></figure>
<!-- /wp:audio -->

<!-- wp:paragraph -->
<p>Following audio will already be properly converted</p>
<!-- /wp:paragraph -->

<!-- wp:audio -->
<figure class="wp-block-audio"><audio controls src="https://host/audio2.mp3"></audio></figure>
<!-- /wp:audio -->

<!-- wp:paragraph -->
<p>Following is not a valid audio element</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
[audio]
<!-- /wp:html -->

<!-- wp:paragraph -->
<p>One more audio</p>
<!-- /wp:paragraph -->

<!-- wp:audio -->
<figure class="wp-block-audio"><audio controls src="https://host/audio3.mp3"></audio></figure>
<!-- /wp:audio -->

<!-- wp:paragraph -->
<p>Some content in the end</p>
<!-- /wp:paragraph -->
CONTENT;
	}

	/**
	 * HTML source for lost audio.
	 *
	 * @return string HTML
	 */
	public static function get_lost_audio_html() {
		return <<<CONTENT
[audio mp3="https://host/wp-content/uploads/2019/08/audio.mp3"][/audio]
CONTENT;
	}

	/**
	 * Blocks contents before patching for lost audio.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_lost_audio_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:audio -->
<figure class="wp-block-audio"></figure>
<!-- /wp:audio -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching for lost audio.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_lost_audio_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:audio -->
<figure class="wp-block-audio"><audio controls src="https://host/wp-content/uploads/2019/08/audio.mp3"></audio></figure>
<!-- /wp:audio -->
CONTENT;
	}

	/**
	 * HTML source for inconsistent sources.
	 *
	 * @return string HTML
	 */
	public static function get_inconsistent_sources_html() {
		return <<<CONTENT
[audio mp3="https://host/wp-content/uploads/2019/08/audio.mp3"][/audio]
CONTENT;
	}

	/**
	 * Blocks contents before patching for inconsistent sources.
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
	 * HTML source for
	 *
	 * @return string HTML
	 */
	public static function get_html_is_non_pertinent_html() {
		return <<<CONTENT
<p>This has nothing to do with the patcher</p>
CONTENT;
	}

	/**
	 * Blocks contents before patching for
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
	 * Expected blocks contents after patching for
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

	/**
	 * HTML source with multiple paragraphs.
	 *
	 * @return string HTML
	 */
	public static function get_multiple_audios_html() {
		return <<<CONTENT
[audio mp3="https://host/audio1.mp3"][/audio]
[audio mp3="https://host/audio2.mp3"][/audio]
[audio mp3="https://host/audio3.mp3"][/audio]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for an example containing multiple paragraphs.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_multiple_audio_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:audio -->
<figure class="wp-block-audio"></figure>
<!-- /wp:audio -->

<!-- wp:audio -->
<figure class="wp-block-audio"></figure>
<!-- /wp:audio -->

<!-- wp:audio -->
<figure class="wp-block-audio"></figure>
<!-- /wp:audio -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for an example containing multiple paragraphs.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_multiple_audio_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:audio -->
<figure class="wp-block-audio"><audio controls src="https://host/audio1.mp3"></audio></figure>
<!-- /wp:audio -->

<!-- wp:audio -->
<figure class="wp-block-audio"><audio controls src="https://host/audio2.mp3"></audio></figure>
<!-- /wp:audio -->

<!-- wp:audio -->
<figure class="wp-block-audio"><audio controls src="https://host/audio3.mp3"></audio></figure>
<!-- /wp:audio -->
CONTENT;
	}

	/**
	 * HTML source with multiple audios out of which some don't have a valid src attribute.
	 *
	 * @return string HTML
	 */
	public static function get_some_skipped_audios_html() {
		return <<<CONTENT
[audio mp3="https://host/audio1.mp3"][/audio]
[audio][/audio]
[audio mp3="https://host/audio3.mp3"][/audio]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for an example where some audios don't have a valid src.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_some_skipped_audios_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:audio -->
<figure class="wp-block-audio"></figure>
<!-- /wp:audio -->

<!-- wp:audio -->
<figure class="wp-block-audio"></figure>
<!-- /wp:audio -->

<!-- wp:audio -->
<figure class="wp-block-audio"></figure>
<!-- /wp:audio -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for an example where some audios don't have a valid src.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_some_skipped_audios_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:audio -->
<figure class="wp-block-audio"><audio controls src="https://host/audio1.mp3"></audio></figure>
<!-- /wp:audio -->

<!-- wp:audio -->
<figure class="wp-block-audio"></figure>
<!-- /wp:audio -->

<!-- wp:audio -->
<figure class="wp-block-audio"><audio controls src="https://host/audio3.mp3"></audio></figure>
<!-- /wp:audio -->
CONTENT;
	}

	/**
	 * HTML source with multiple audios out of which some aren't properly formatted -- are in fact not valid audios.
	 *
	 * @return string HTML
	 */
	public static function get_some_invalid_audios_html() {
		return <<<CONTENT
[audio mp3="https://host/audio1.mp3"][/audio]
[audio]
[audio mp3="https://host/audio3.mp3"][/audio]
CONTENT;
	}

	/**
	 * Blocks contents before patching, for an example where some audios are invalid.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_some_invalid_audios_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:audio -->
<figure class="wp-block-audio"></figure>
<!-- /wp:audio -->

<!-- wp:html -->
[audio]
<!-- /wp:html -->

<!-- wp:audio -->
<figure class="wp-block-audio"></figure>
<!-- /wp:audio -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, for an example where some audios are invalid.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_some_invalid_audios_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:audio -->
<figure class="wp-block-audio"><audio controls src="https://host/audio1.mp3"></audio></figure>
<!-- /wp:audio -->

<!-- wp:html -->
[audio]
<!-- /wp:html -->

<!-- wp:audio -->
<figure class="wp-block-audio"><audio controls src="https://host/audio3.mp3"></audio></figure>
<!-- /wp:audio -->
CONTENT;
	}

	/**
	 * HTML source with multiple audios where some will not have their content lost.
	 *
	 * @return string HTML
	 */
	public static function get_some_audios_ok_html() {
		return <<<CONTENT
[audio mp3="https://host/audio1.mp3"][/audio]
[audio mp3="https://host/audio2.mp3"][/audio]
[audio mp3="https://host/audio3.mp3"][/audio]
CONTENT;
	}

	/**
	 * Blocks contents before patching, multiple audios where some will not have their content lost.
	 *
	 * @return string Gutenberg blocks contents before patching.
	 */
	public static function get_some_audios_ok_blocks_before_patching() {
		return <<<CONTENT
<!-- wp:audio -->
<figure class="wp-block-audio"></figure>
<!-- /wp:audio -->

<!-- wp:audio -->
<figure class="wp-block-audio"><audio controls src="https://host/audio2.mp3"></audio></figure>
<!-- /wp:audio -->

<!-- wp:audio -->
<figure class="wp-block-audio"></figure>
<!-- /wp:audio -->
CONTENT;
	}

	/**
	 * Expected blocks contents after patching, multiple audios where some will not have their content lost.
	 *
	 * @return string Expected Gutenberg blocks contents after patching.
	 */
	public static function get_some_audios_ok_blocks_patched_expected() {
		return <<<CONTENT
<!-- wp:audio -->
<figure class="wp-block-audio"><audio controls src="https://host/audio1.mp3"></audio></figure>
<!-- /wp:audio -->

<!-- wp:audio -->
<figure class="wp-block-audio"><audio controls src="https://host/audio2.mp3"></audio></figure>
<!-- /wp:audio -->

<!-- wp:audio -->
<figure class="wp-block-audio"><audio controls src="https://host/audio3.mp3"></audio></figure>
<!-- /wp:audio -->
CONTENT;
	}
}
