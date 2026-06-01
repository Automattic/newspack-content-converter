<?php
/**
 * A pre-conversion Patcher for shortcode elements.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher\Patchers;

use DOMDocument;
use NewspackContentConverter\ContentPatcher\Patchers\PreconversionPatcherAbstract;
use WP_HTML_Tag_Processor;

/**
 * Pre-conversion Patcher class for the shortcode elements.
 *
 * @package NewspackContentConverter\ContentPatcher\Patchers
 */
class SocialEmbedsPatcher extends PreconversionPatcherAbstract {
	/**
	 * See the \NewspackContentConverter\ContentPatcher\Patchers\PreconversionPatcherInterface::patch_html_source for description.
	 *
	 * @param string $html HTML source before conversion to blocks.
	 * @param int    $post_id Post ID.
	 *
	 * @return string Patched HTML source before conversion to blocks.
	 */
	public function patch_html_source( $html, $post_id ) {
		$html_patched = $this->convert_instagram_embeds_to_html_blocks( $html );

		return $html_patched;
	}

	/**
	 * Detects certain shortcode elements, and if they're inline with preceeding text, adds a line break so that the shortcode
	 * is at the beginning of the line. This is necessary for Gutenberg to convert these shortcodes properly.
	 *
	 * @param string $html HTML source before conversion to blocks.
	 *
	 * @return string Patched HTML source before conversion to blocks.
	 */
	private function convert_instagram_embeds_to_html_blocks( $html ) {
        $doc = new DOMDocument();

        libxml_use_internal_errors( true ); // Suppress warnings for invalid HTML

        $doc->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

        foreach ( $doc->getElementsByTagName( 'blockquote' ) as $blockquote ) {
            if ( $blockquote->getAttribute( 'class' ) !== 'instagram-media' ) {
                continue;
            }

            $opening_html_block_tag = $doc->createComment( ' wp:html ' );
            $closing_html_block_tag = $doc->createComment( ' /wp:html ' );

            $next_sibling = $blockquote->nextSibling;

            if ( ( $next_sibling?->nodeName === 'script' ) && str_contains( $next_sibling?->getAttribute( 'src' ), 'instagram.com/embed.js' ) ) {
                $blockquote->parentNode->insertBefore( $opening_html_block_tag, $blockquote );

                if ( $next_sibling->nextSibling ) {
                    $blockquote->parentNode->insertBefore( $closing_html_block_tag, $next_sibling->nextSibling );
                } else {
                    $blockquote->parentNode->appendChild( $closing_html_block_tag );
                }
            }
        }

        return $doc->saveHTML();
	}
}
