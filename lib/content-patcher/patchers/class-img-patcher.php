<?php

namespace NewspackContentConverter\ContentPatcher\Patchers;

use NewspackContentConverter\ContentPatcher\PatcherInterface;
use NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract;

/**
 * Class ImgPatcher
 *
 * @package NewspackContentConverter\ContentPatcher\Patchers
 */
class ImgPatcher extends PatcherAbstract implements PatcherInterface {

	/**
	 * @see \NewspackContentConverter\ContentPatcher\Patchers\PatcherAbstract::$patterns
	 *
	 * @var array
	 */
	protected $patterns = [
		'match_html_element'     => '/<img.*?>/im',
		'match_blocks_element'   => '/<img.*?>/im',
		'match_attribute_value'  => '/.*?height\s*=\s*["\\\'+]?(.+)["\\\'+].*?/im',
		'replace_blocks_element' => '|\/>|',
	];

	/**
	 * @see \NewspackContentConverter\ContentPatcher\PatcherInterface::patch_blocks_contents
	 *
	 * @param string $source_html   HTML source, original content being converted.
	 * @param string $source_blocks Block content as result of Gutenberg "conversion to blocks".
	 *
	 * @return string|null
	 */
	public function patch_blocks_contents( string $source_html, string $source_blocks ): ?string {

		$matches_html = $this->match_all_elements( $this->patterns[ 'match_html_element' ], $source_html );
		if ( ! $matches_html ) {
			// TODO: log 'no elements matched in HTML'
			return false;
		}

		$matches_blocks = $this->match_all_elements( $this->patterns[ 'match_blocks_element' ], $source_blocks );
		if ( ! $this->validate_html_and_block_matches( $matches_html[ 0 ], $matches_blocks[ 0 ] ) ) {
			// TODO: log 'HJTML and block matches do not correspond'
			return false;
		}

		foreach ( $matches_html[ 0 ] as $key => $match_html ) {

			$html_element = $match_html[ 0 ];
			$position_html_element = $match_html[ 1 ];

			if ( $attribute_value = $this->element_match_attribute_value( $this->patterns[ 'match_attribute_value' ], $html_element ) ) {

				$blocks_element = $matches_blocks[ 0 ][ $key ][ 0 ];
				$position_blocks_element = $matches_blocks[ 0 ][ $key ][ 1 ];

				$attribute_patch = sprintf( ' height="%s" />', $attribute_value );
				$patched_block_element = $this->apply_patch_block_element( $this->patterns[ 'replace_blocks_element' ], $attribute_patch, $blocks_element );

				$source_blocks = $this->replace_block_element( $patched_block_element, $position_blocks_element, $source_blocks );
			}
		}

		return $source_blocks;
	}
}
