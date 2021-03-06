const VueSSRServerPlugin = require( 'vue-server-renderer/server-plugin' );
const VueSSRClientPlugin = require( 'vue-server-renderer/client-plugin' );
const URL = require( 'url' ).URL;
const TARGET_NODE = process.env.WEBPACK_TARGET === 'node';
const DEV_MODE = process.env.WEBPACK_TARGET === 'dev';
const filePrefix = 'wikibase.termbox.';

const target = TARGET_NODE
	? 'server'
	: 'client';

let repoHost;
let repoScriptPath;
let repoProtocol;

if ( process.env.WIKIBASE_REPO ) {
	const repoUrl = new URL( process.env.WIKIBASE_REPO );
	repoHost = repoUrl.host;
	repoScriptPath = repoUrl.pathname;
	repoProtocol = repoUrl.protocol;
}

module.exports = {
	outputDir: TARGET_NODE ? 'serverDist' : 'dist',
	configureWebpack: () => ( {
		entry: DEV_MODE ? [ './src/dev-entry.ts', `./src/${target}-entry.ts` ] : `./src/${target}-entry.ts`,
		target: TARGET_NODE ? 'node' : 'web',
		node: TARGET_NODE ? undefined : false,
		plugins: [
			TARGET_NODE
				? new VueSSRServerPlugin()
				: new VueSSRClientPlugin(),
		],
		output: {
			libraryTarget: TARGET_NODE
				? 'commonjs2'
				: undefined,
			filename: `${filePrefix}[name].js`,
		},
		optimization: {
			splitChunks: undefined,
			minimize: !TARGET_NODE, // needed for comparison of `.constructor.name`s
		},
	} ),
	chainWebpack: ( config ) => {
		config.optimization.delete( 'splitChunks' );

		if ( process.env.NODE_ENV === 'production' ) {
			config.plugin( 'extract-css' )
				.tap( ( [ options, ...args ] ) => [
					Object.assign( {}, options, { filename: `${filePrefix}[name].css` } ),
					...args,
				] );

			// ResourceLoader has access to /assets/* and /dist/*.css - use assets directly
			config.module
				.rule( 'images' )
				.test( /\.(png|jpe?g|gif|svg)(\?.*)?$/ )
				.use( 'url-loader' )
				.loader( 'url-loader' )
				.options( {
					limit: -1,
					name: '[path]/[name].[ext]',
				} );
		}

		config.module
			.rule( 'vue' )
			.use( 'vue-loader' )
			.tap( ( options ) =>
				Object.assign( options, {
					optimizeSSR: false,
				} ) );
	},
	css: {
		loaderOptions: {
			sass: {
				data: '@import "@/styles/_main.scss";',
			},
		},
	},
	devServer: {
		proxy: {
			'^/csrMWProxy': {
				target: `${repoProtocol}//${repoHost}`,
				pathRewrite: { '^/csrMWProxy': repoScriptPath },
			},
		},
	},
};
