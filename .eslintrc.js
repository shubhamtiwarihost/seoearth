/**
 * ESLint configuration: the @wordpress/scripts defaults, plus the classic JSX
 * pragma used by babel.config.js so `createElement` counts as used.
 */
module.exports = {
	extends: [ 'plugin:@wordpress/eslint-plugin/recommended' ],
	settings: {
		react: {
			pragma: 'createElement',
			fragment: 'Fragment',
		},
	},
	rules: {
		'react/jsx-uses-react': 'error',
	},
	overrides: [
		{
			files: [ '**/test/**/*.js', '**/?(*.)test.js' ],
			extends: [ 'plugin:@wordpress/eslint-plugin/test-unit' ],
		},
	],
};
