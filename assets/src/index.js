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

window.onload = function() {
	const div_settings = document.getElementById( 'ncc-settings' );
	const div_conversion = document.getElementById( 'ncc-conversion' );

	if ( typeof div_settings != 'undefined' && div_settings != null ) {
		render( <Settings />, div_settings );
	} else if ( typeof div_conversion != 'undefined' && div_conversion != null ) {
		render( <Conversion />, div_conversion );
	} else {
		document
			.querySelector( '.block-editor' )
			.insertAdjacentHTML( 'afterend', '<div id="root"></div>' );

		window.onbeforeunload = function() {};

		const getParams = queryString.parse( window.location.search );
		render(
			<ContentConverter retryFailedConversions={ 'retry-failed' in getParams } />,
			document.getElementById( 'root' )
		);
	}
};
