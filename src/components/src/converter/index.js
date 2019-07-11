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
	postIds.forEach( postId => {
		result = result.then( () => runSinglePost( postId ) );
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
		.then( () => {
			console.log( ` ---- ${ postId } ----` );
		} )
		.then( () => {
			return removeAllBlocks();
		} )
		.then( () => {
			return getPostContentById( postId );
		} )
		.then( data => {
			return insertClassicBlockWithContent( data );
		} )
		.then( () => {
			return dispatchConvertClassicToBlocks();
		} )
		.then( () => {
			return getAllBlocksContents( postId );
		} )
		.then( data => {
			return updatePost( data, postId );
		} )
		.then( () => {
			console.log( ' ---- done ----' );
		} );
}

/**
 * Clears all blocks from the Block Editor.
 * @returns {Promise<any> | Promise}
 */
export function removeAllBlocks() {
	return new Promise( function( resolve, reject ) {
		wp.data.dispatch( 'core/block-editor' ).resetBlocks( [] );
		resolve();
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
		wp
			.apiFetch( {
				path: '/wp/v2/posts?include=' + id,
				method: 'GET',
			} )
			// currently fetching 1 post only ; could also .resolve( JSON.stringify( response ) )
			.then( response => Promise.resolve( response[ 0 ] ) )
	);
}

/**
 * Prepares a Classic Block with Post's data loaded as content, and inserts it into the Block Editor.
 *
 * @param data
 * @returns {Promise<any> | Promise}
 */
export function insertClassicBlockWithContent( data ) {
	return new Promise( function( resolve, reject ) {
		const html = data.content.rendered;
		var block = wp.blocks.createBlock( 'core/freeform' );
		block.attributes.content = html;
		wp.data.dispatch( 'core/block-editor' ).insertBlocks( block );
		// --- OR: let block = wp.blocks.createBlock( "core/freeform", { content: 'test' } );
		resolve();
	} );
}

/**
 * Triggers conversion of all Classic Blocks found in the Block Editor into Gutenberg Blocks.
 *
 * @returns {Promise<any> | Promise}
 */
export function dispatchConvertClassicToBlocks() {
	return new Promise( function( resolve, reject ) {
		wp.data
			.select( 'core/block-editor' )
			.getBlocks()
			.forEach( function( block, blockIndex ) {
				if ( block.name === 'core/freeform' ) {
					wp.data.dispatch( 'core/editor' ).replaceBlocks(
						block.clientId,
						wp.blocks.rawHandler( {
							HTML: wp.blocks.getBlockContent( block ),
						} )
					);
				}
			} );
		resolve();
	} );
}

/**
 * Fetches all blocks' contents from the Block Editor.
 *
 * TODO: works only with "core/editor" but not with "core/block-editor" -- might be deprecated; find another approach to do this.
 *
 * @returns {Promise<any> | Promise}
 */
export function getAllBlocksContents() {
	return new Promise( function( resolve, reject ) {
		const allBlocksContents = wp.data.select( 'core/editor' ).getEditedPostContent();
		resolve( allBlocksContents );
	} );
}

/**
 * Updates Post content.
 *
 * @param string data Post content.
 * @param id
 * @returns {*}
 */
export function updatePost( data, id ) {
	const dataEncoded = encodeURIComponent( data );

	return wp
		.apiFetch( {
			path: NEWSPACK_CONVERTER_API_BASE_URL + '/update-post',
			method: 'POST',
			headers: {
				Accept: 'application/json, text/javascript, */*; q=0.01',
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
			},
			body: `id=${ id }&content=${ dataEncoded }`,
		} )
		.then( response => Promise.resolve( response ) );
}

export default {
	runSinglePost,
	runMultiplePosts,
};
