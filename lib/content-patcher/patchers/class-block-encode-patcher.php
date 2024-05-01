<?php
/**
 * A pre-conversion Patcher that encodeds existing GB blocks so they don't get mangled in conversion.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher\Patchers;

/**
 * Pre-conversion Patcher that base64 encodes GB blocks in the post content, so they don't get mangled by the conversion process.
 *
 * @package NewspackContentConverter\ContentPatcher\Patchers
 */
class BlockEncodePatcher extends PreconversionPatcherAbstract {

	public const ENCODED_ANCHOR = '[BLOCK-ENCODED:';

	/**
	 * Patch HTML source.
	 *
	 * @param string $html_content  HTML source, original content before conversion.
	 * @param int    $post_id       Post ID.
	 */
	public function patch_html_source( $html_content, $post_id ) {
		return $this->encode_post_content( $html_content );
	}

	/**
	 * Encode Gutenberg blocks in given string as base64.
	 *
	 * @param string $html The string content to encode.
	 *
	 * @return string The string with all blocks base64 encoded.
	 */
	private function encode_post_content( $html ) {
		$blocks        = parse_blocks( $html );
		$actual_blocks = array_filter( $blocks, fn( $block ) => ! empty( $block['blockName'] ) && ! str_contains( $block['innerHTML'], self::ENCODED_ANCHOR ) );

		if ( empty( $actual_blocks ) ) {
			return $html;
		}

		$encoded_blocks = array_map( fn( $block ) => $this->encode_block( $block ), $actual_blocks );
		foreach ( $encoded_blocks as $idx => $encoded ) {
			$blocks[ $idx ] = $encoded;
		}

		return serialize_blocks( $blocks );
	}

	/**
	 * Encode a block's content as base64 string inside a block.
	 *
	 * @param array $block block to encode.
	 *
	 * @return array "Empty" block with the encoded block as innerHTML.
	 */
	private function encode_block( array $block ): array {
		$as_string = serialize_block( $block );

		$anchor = self::ENCODED_ANCHOR . base64_encode( $as_string ) . ']';
		$content = '<pre class="wp-block-preformatted">' . $anchor . '</pre>' . str_repeat( PHP_EOL, 2 );

		return [
			'blockName'    => null, // On purpose.
			'attrs'        => [],
			'innerBlocks'  => [],
			'innerHTML'    => $content,
			'innerContent' => [ $content ],
		];
	}
}
