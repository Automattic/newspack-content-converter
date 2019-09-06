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
import * as serviceWorker from './service-worker';
import './style.css';

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
		document.getElementsByClassName('edit-post-header')[0].style.display = 'none';
		document.getElementsByClassName('edit-post-layout__content')[0].style.display = 'none';
		// document.getElementsByClassName( 'edit-post-sidebar' )[ 0 ].style.display = 'none';
		document
			.getElementsByClassName('edit-post-header')[0]
			.insertAdjacentHTML('afterend', '<div id="root"></div>');

		window.onbeforeunload = function() {};

		render(<ContentConverter />, document.getElementById('root'));
	}
};

// If you want your app to work offline and load faster, you can change
// unregister() to register() below. Note this comes with some pitfalls.
// Learn more about service workers: https://bit.ly/CRA-PWA
serviceWorker.unregister();
