/**
 **** WARNING: No ES6 modules here. Not transpiled! ****
 */
/* eslint-disable import/no-nodejs-modules */

const getBaseWebpackConfig = require( 'newspack-scripts/config/getWebpackConfig' );

const webpackConfig = getBaseWebpackConfig(
	{
		entry: './assets/src/',
	}
);

module.exports = webpackConfig;
