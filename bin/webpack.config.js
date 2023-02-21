const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		'habit-streak': './src/index.js',
		'habit-streak-options': './src/options.js',
	},
}

