<?php
/**
 * Patcher for decoding encoded GB blocks.
 *
 * @package Newspack
 */

namespace NewspackContentConverter\ContentPatcher\Patchers;

/**
 * Patcher that decodes base64 encoded blocks in (see BlockEncodePatcher).
 *
 * @package NewspackContentConverter\ContentPatcher\Patchers
 */
class BlockDecodePatcher extends PatcherAbstract {

	/**
	 * Patch blocks contents.
	 *
	 * @param string $block_content Block content after conversion to blocks.
	 * @param string $html_content  HTML source, original content before conversion.
	 * @param int    $post_id       Post ID.
	 */
	public function patch_blocks_contents( $block_content, $html_content, $post_id ) {
		return $this->decode_post_content( $block_content );
	}

	/**
	 * Decode blocks in string from base64.
	 *
	 * @param string $html_content String to decode.
	 *
	 * @return string The string with all blocks decoded.
	 */
	private function decode_post_content( string $html_content ): string {
		if ( ! str_contains( $html_content, BlockEncodePatcher::ENCODED_ANCHOR ) ) {
			return $html_content;
		}
		$blocks         = parse_blocks( $html_content );
		$encoded_blocks = array_filter( $blocks, fn( $block ) => str_contains( $block['innerHTML'], BlockEncodePatcher::ENCODED_ANCHOR ) );

		if ( empty( $encoded_blocks ) ) {
			return $html_content;
		}
		foreach ( $encoded_blocks as $idx => $encoded ) {
			$decoded = $this->decode_block( $encoded['innerHTML'] );
			if ( ! empty( $decoded ) ) {
				$blocks[ $idx ] = $decoded;
			}
		}

		return serialize_blocks( $blocks );
	}

	/**
	 * Decode a block from base64.
	 *
	 * @param string $encoded_block Block to decode.
	 *
	 * @return array The decoded block.
	 */
	private function decode_block( string $encoded_block ): array {
		$pattern = '/\\' . BlockEncodePatcher::ENCODED_ANCHOR . '([A-Za-z0-9+\\/=]+)\]/';
		// See https://base64.guru/learn/base64-characters for chars in base64.
		preg_match( $pattern, $encoded_block, $matches );
		if ( empty( $matches[1] ) ) {
			return [];
		}

		$parsed = parse_blocks( base64_decode( $matches[1], true ) );
		if ( ! empty( $parsed[0]['blockName'] ) ) {
			return $parsed[0];
		}

		return [];
	}
}
