/**
 * WordPress dependencies.
 */
import apiFetch from '@wordpress/api-fetch';
import { createBlock, getBlockContent, rawHandler } from '@wordpress/blocks';
import { dispatch, select } from '@wordpress/data';

const NEWSPACK_CONVERTER_API_BASE_URL = '/newspack-content-converter';

/**
 * Runs conversion of multiple Posts.
 *
 * @param string postIdsCsv CSV string of Post IDs.
 * @returns {Promise<void>}
 */
export function runMultiplePosts( postIdsCsv ) {
	const postIds = postIdsCsv.split( ',' );

	var result = Promise.resolve();
	postIds.forEach( ( postId, key ) => {
		result = result.then( () => {
			console.log( `converting ${ postId }, ${ key + 1 }/${ postIds.length } ` );
			return runSinglePost( postId );
		} );
	} );

	return result;
}

/**
 * Conversion of a single Post.
 * Uses promise chaining to ensure sequential execution of async operations.
 *
 * @param postId
 * @returns {Promise<void | never>}
 */
export function runSinglePost( postId ) {
	return Promise.resolve()
		.then( () => removeAllBlocks() )
		.then( () => getPostContentById( postId ) )
		.then( html => insertClassicBlockWithContent( html ) )
		.then( html => dispatchConvertClassicToBlocks( html ) )
		.then( html => getAllBlocksContents( postId, html ) )
		.then( ( [ blocks, html ] ) => updatePost( postId, blocks, html ) );
}

/**
 * Clears all blocks from the Block Editor.
 * @returns {Promise<any> | Promise}
 */
export function removeAllBlocks() {
	return new Promise( function( resolve, reject ) {
		dispatch( 'core/block-editor' ).resetBlocks( [] );
		return resolve();
	} );
}

/**
 * Fetches contents of a single Post.
 *
 * @param id
 * @returns string
 */
export function getPostContentById( id ) {
	return (
		apiFetch( {
			path: '/wp/v2/posts?include=' + id,
			method: 'GET',
		} )
			// currently fetching 1 post only ; could also .resolve( JSON.stringify( response ) )
			.then( response => Promise.resolve( response[ 0 ].content.rendered ) )
	);
}

/**
 * Prepares a Classic Block with Post's data loaded as content, and inserts it into the Block Editor.
 *
 * @param String html HTML source before conversion.
 * @returns {Promise<any> | Promise}
 */
export function insertClassicBlockWithContent( html ) {
	return new Promise( function( resolve, reject ) {
		const block = createBlock( 'core/freeform' );
		block.attributes.content = html;
		dispatch( 'core/block-editor' ).insertBlocks( block );
		// --- OR: let block = wp.blocks.createBlock( "core/freeform", { content: 'test' } );
		resolve( html );
	} );
}

/**
 * Triggers conversion of all Classic Blocks found in the Block Editor into Gutenberg Blocks.
 *
 * @param String html HTML source before conversion.
 * @returns {Promise<any> | Promise}
 */
export function dispatchConvertClassicToBlocks( html ) {
	return new Promise( function( resolve, reject ) {
		select( 'core/block-editor' )
			.getBlocks()
			.forEach( function( block, blockIndex ) {
				if ( block.name === 'core/freeform' ) {
					dispatch( 'core/editor' ).replaceBlocks(
						block.clientId,
						rawHandler( {
							HTML: getBlockContent( block ),
						} )
					);
				}
			} );
		resolve( html );
	} );
}

/**
 * Fetches all blocks' contents from the Block Editor.
 *
 * TODO: getEditedPostContent() works only on select("core/editor") but not on select("core/block-editor") -- might be deprecated; find another approach to do this.
 *
 * @param Int postId
 * @param String html HTML source before conversion.
 * @returns {Promise<any> | Promise}
 */
export function getAllBlocksContents( postId, html ) {
	return new Promise( function( resolve, reject ) {
		const allBlocksContents = select( 'core/editor' ).getEditedPostContent();
		resolve( [ allBlocksContents, html ] );
	} );
}

/**
 * Updates Post content.
 *
 * @param int postId
 * @param string blocks Blocks Post content.
 * @param string html   HTML source before conversion.
 * @returns {*}
 */
export function updatePost( postId, blocks, html ) {
	return apiFetch( {
		path: NEWSPACK_CONVERTER_API_BASE_URL + '/update-post',
		method: 'POST',
		headers: {
			Accept: 'application/json, text/javascript, */*; q=0.01',
			'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
		},
		data: {
			post_id: postId,
			content_blocks: blocks,
			content_html: html
		},
	} ).then( response => Promise.resolve( response ) );
}

export default {
	runSinglePost,
	runMultiplePosts,
};
