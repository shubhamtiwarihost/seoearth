/**
 * Babel configuration for the editor build.
 *
 * @wordpress/scripts compiles JSX with React's automatic runtime, which makes the
 * bundle depend on the "react-jsx-runtime" script that only exists from
 * WordPress 6.6. We support WordPress 6.4, so JSX is compiled first (project
 * plugins run before preset plugins) with the classic runtime to
 * `createElement` / `Fragment` from @wordpress/element. Every JSX file must
 * import those two names.
 */
module.exports = ( api ) => {
	api.cache( true );
	return {
		presets: [ '@wordpress/babel-preset-default' ],
		plugins: [
			[
				'@babel/plugin-transform-react-jsx',
				{
					runtime: 'classic',
					pragma: 'createElement',
					pragmaFrag: 'Fragment',
				},
			],
		],
	};
};
