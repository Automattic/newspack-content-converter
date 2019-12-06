/**
 * WordPress dependencies.
 */
import { render } from '@wordpress/element';

/**
 * Internal dependencies.
 */
import ContentConverter from './content-converter';
import ContentRepatcher from './content-repatcher';
import Settings from './settings';
import Conversion from './conversion';
import Patchers from './patchers';
import './style.css';

function nccHideElement(element) {
	element.style.display = 'none';
}

function nccInsertRootAdjacentToElement(element) {
	element.insertAdjacentHTML('afterend', '<div id="root"></div>');
}

// Wrapper function which enables retrying a callback after a timeout interval and for a defined maxAttempts (useful to retry
// actions for elements which haven't yet been injected into DOM).
function nccCallbackWithRetry( callback, maxAttempts = 5, timeout = 1000 ) {
	return new Promise(function(resolve, reject) {
		var doCallback = function(attempt) {
			try {
				callback();
				Promise(resolve, reject);
			} catch (e) {
				if (0 == attempt) {
					reject(e);
				} else {
					setTimeout(function() {
						doCallback(attempt - 1);
					}, timeout);
				}
			}
		};
		doCallback(maxAttempts);
	});
}

window.onload = function() {
	const div_settings = document.getElementById('ncc-settings');
	const div_conversion = document.getElementById('ncc-conversion');
	const div_patchers = document.getElementById('ncc-patchers');
	const div_content_repatcher = document.getElementById('ncc-content-repatcher');

	if (typeof div_settings != 'undefined' && div_settings != null) {
		render(<Settings />, div_settings);
	} else if (typeof div_conversion != 'undefined' && div_conversion != null) {
		render(<Conversion />, div_conversion);
	} else if (typeof div_patchers != 'undefined' && div_patchers != null) {
		render(<Patchers />, div_patchers);
	} else if (typeof div_content_repatcher != 'undefined' && div_content_repatcher != null) {
		render(<ContentRepatcher />, div_content_repatcher);
	} else {
		// Converter app sits on top of the Gutenberg Block Editor.
		nccCallbackWithRetry( nccHideElement( document.getElementsByClassName( 'edit-post-header' )[ 0 ] ) );
		nccCallbackWithRetry( nccHideElement( document.getElementsByClassName( 'edit-post-layout__content' )[ 0 ] ) );
		nccCallbackWithRetry( nccHideElement( document.getElementsByClassName( 'edit-post-sidebar' )[ 0 ] ) );
		nccCallbackWithRetry( nccInsertRootAdjacentToElement( document.getElementsByClassName( 'edit-post-header' )[ 0 ] ) );

		window.onbeforeunload = function() {};

		render(<ContentConverter />, document.getElementById('root'));
	}
};
