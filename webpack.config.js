/**
 **** WARNING: No ES6 modules here. Not transpiled! ****
 */
/* eslint-disable import/no-nodejs-modules */

const getBaseWebpackConfig = require( '@automattic/calypso-build/webpack.config.js' );
const path = require( 'path' );

const webpackConfig = getBaseWebpackConfig(
	{ WP: true },
	{
		entry: './assets/src/',
		'output-path': path.join( __dirname, 'assets', 'dist' ),
	}
);

module.exports = webpackConfig;
