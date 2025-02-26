/**
 * WordPress dependencies.
 */
import { render } from '@wordpress/element';

/**
 * Internal dependencies.
 */
import ContentConverter from './content-converter';
import Settings from './settings';
import Restore from './restore';
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

// Check if the root div is loaded, and if not alerts the user.
const nccCheckRootDivIsLoaded = function() {
	let retries = 0;
	let maxRetries = 15;
	let retryInterval = 1000;
	const interval = setInterval(() => {
		const root = document.getElementById("root");
		if (root) {
			// Stop if found.
			clearInterval(interval);
		} else {
			retries++;
			if (retries > (maxRetries - 1)) {
				alert('It looks like something may be preventing the Newspack Content Converter from loading. Please try refreshing the page. If the problem persists, temporarily deactivate all other active plugins, and then try refreshing the page.');
				// Stop.
				clearInterval(interval);
			}
		}
	}, retryInterval);
}
// If this is the conversion batch page (/wp-admin/post-new.php?newspack-content-converter), check if the root div is loaded.
const isConversionPage = window.location.pathname === '/wp-admin/post-new.php' && window.location.search === '?newspack-content-converter';
if ( isConversionPage ) {
	nccCheckRootDivIsLoaded();
}

window.onload = function() {
	const div_settings = document.getElementById( 'ncc-settings' );
	const div_restore = document.getElementById( 'ncc-restore' );
	const div_conversion = document.getElementById( 'ncc-conversion' );

	if ( typeof div_settings != 'undefined' && div_settings != null ) {
		render( <Settings />, div_settings );
	} else if ( typeof div_restore != 'undefined' && div_restore != null ) {
		render( <Restore />, div_restore );
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
