/**
 * WordPress dependencies.
 */
import { render } from '@wordpress/element';

/**
 * Internal dependencies.
 */
import ContentConverter from './components/src/content-converter';
import * as serviceWorker from './serviceWorker';

window.onload = function() {
  document.getElementsByClassName( 'edit-post-header' )[ 0 ].style.display = 'none';
  document.getElementsByClassName( 'edit-post-layout__content' )[ 0 ].style.display = 'none';
  document.getElementsByClassName( 'edit-post-sidebar' )[ 0 ].style.display = 'none';
  document
    .getElementsByClassName( 'edit-post-header' )[ 0 ]
    .insertAdjacentHTML( 'afterend', '<div id="root"></div>' );

  render( <ContentConverter />, document.getElementById( 'root' ) );
}

// If you want your app to work offline and load faster, you can change
// unregister() to register() below. Note this comes with some pitfalls.
// Learn more about service workers: https://bit.ly/CRA-PWA
serviceWorker.unregister();
