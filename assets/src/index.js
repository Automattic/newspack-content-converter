/**
 * WordPress dependencies.
 */
import { render } from '@wordpress/element';
import queryString from 'query-string';

/**
 * Internal dependencies.
 */
import ContentConverter from './content-converter';
import Settings from './settings';
import Conversion from './conversion';
import './style.scss';

const nccGetElementByClassName = function( className ) {
	const elements = document.getElementsByClassName( className );
	if ( 'undefined' == typeof elements || ! elements.length )
		throw 'Not found element by class name ' + className;
	return elements[ 0 ];
};

const nccHideElementByClass = function( className ) {
	nccGetElementByClassName( className ).style.display = 'none';
};

const nccInsertRootAdjacentToElementByClass = function( className ) {
	nccGetElementByClassName( className ).insertAdjacentHTML( 'afterend', '<div id="root"></div>' );
};

const nccRenderRoot = function() {
	window.onbeforeunload = function() {};
	render(
		<ContentConverter />,
		document.getElementById( 'root' )
	);
};

// Wrapper function which enables retrying a callback after a timeout interval and for a defined maxAttempts (useful to retry
// actions for elements which haven't yet been injected into DOM).
function nccCallbackWithRetry( callback, callbackParam, maxAttempts = 10, timeout = 1000 ) {
	return new Promise( function( resolve, reject ) {
		const doCallback = function( attempt ) {
			try {
				callback( callbackParam );
				resolve();
			} catch ( e ) {
				if ( 0 == attempt ) {
					console.log( 'Final CSS warning: ' + e );
				} else {
					setTimeout( function() {
						doCallback( attempt - 1 );
					}, timeout );
					console.log( e );
				}
			}
		};
		doCallback( maxAttempts );
	} );
}

window.onload = function() {
	const div_settings = document.getElementById( 'ncc-settings' );
	const div_conversion = document.getElementById( 'ncc-conversion' );

	if ( typeof div_settings != 'undefined' && div_settings != null ) {
		render( <Settings />, div_settings );
	} else if ( typeof div_conversion != 'undefined' && div_conversion != null ) {
		render( <Conversion />, div_conversion );
	} else {
		// Converter app sits on top of the Gutenberg Block Editor.
		nccCallbackWithRetry( nccHideElementByClass, 'edit-post-header' );
		nccCallbackWithRetry( nccHideElementByClass, 'edit-post-layout__content' );
		nccCallbackWithRetry( nccHideElementByClass, 'edit-post-sidebar' );
		nccCallbackWithRetry( nccInsertRootAdjacentToElementByClass, 'edit-post-header' );
		nccCallbackWithRetry( nccRenderRoot );
	}
};
