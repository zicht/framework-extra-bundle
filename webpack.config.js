const MergeIntoSingleFilePlugin = require('webpack-merge-and-include-globally');
const FixStyleOnlyEntriesPlugin = require("webpack-fix-style-only-entries");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const path = require('path');
const PolyfillInjectorPlugin = require('webpack-polyfill-injector');
const StyleLintPlugin = require('stylelint-webpack-plugin');
const webpack = require('webpack');
const WebpackBuildNotifierPlugin = require('webpack-build-notifier');

module.exports = function (env, argv) {
    return {
        entry: {
            'json-editor': [
                `webpack-polyfill-injector?${JSON.stringify({modules: ['./src/Zicht/Bundle/FrameworkExtraBundle/Resources/typescript/json-editor.ts']})}!`,
                './src/Zicht/Bundle/FrameworkExtraBundle/Resources/sass/json-editor.scss'
            ],
        },

        output: {
            filename: '[name].js',
            publicPath: '/bundles/zichtframeworkextra/',
            path: path.resolve('./src/Zicht/Bundle/FrameworkExtraBundle/Resources/public')
        },

        resolve: {
            alias: {},

            // Add `.ts` and `.tsx` as a resolvable extension.
            extensions: ['.ts', '.tsx', '.js'] // note if using webpack 1 you'd also need a '' in the array as well
        },

        module: {
            rules: [
                {
                    // Load .js and .ts files, excluding external dependencies
                    test: /\.(t|j)sx?$/,
                    exclude: /node_modules/,
                    use: [{loader: 'ts-loader'}]
                },
                {
                    // Load .ts files, explicitly from external @zicht dependencies
                    // This is needed for libraries that supply typescript code
                    test: /\.ts$/,
                    include: [/node_modules\/@zicht/],
                    use: [{loader: 'ts-loader'}]
                },
                {
                    // Linter for .ts files
                    test: /\.ts$/,
                    exclude: /node_modules|vendor/,
                    enforce: 'pre',
                    use: [{loader: 'tslint-loader', options: {configFile: './node_modules/@zicht/tslint/tslint.json'}}]
                },
                {
                    // Load .scss files
                    test: /\.scss$/,
                    use: [MiniCssExtractPlugin.loader, 'css-loader', 'postcss-loader', 'sass-loader']
                }
            ]
        },

        plugins: [
            new PolyfillInjectorPlugin({
                // Use a single file polyfill, otherwise we will drown in polyfill files
                singleFile: true,
                // The PolyfillInjectorPlugin uses polyfills supplied from the following repository:
                // https://github.com/Financial-Times/polyfill-library/tree/master/polyfills
                // Use at least one polyfill, otherwise the build process breaks
                polyfills: [
                    // Polyfills should be in alphabetical order
                    'Promise',
                ]
            }),
            new MergeIntoSingleFilePlugin({
                // Add a js file that merges both our and the vendor code into one file
                // Note that the build must be run twice, because the second time will pickup the changes from the first build... very annoying
                files: {
                    'json-editor+.js': [
                        './node_modules/@trevoreyre/autocomplete-js/dist/autocomplete.min.js',
                        './src/Zicht/Bundle/FrameworkExtraBundle/Resources/public/json-editor.js'
                    ]
                }
            }),
            new FixStyleOnlyEntriesPlugin(),
            new MiniCssExtractPlugin({
                filename: "[name].css",
                chunkFilename: "[id].css"
            }),
            new OptimizeCssAssetsPlugin({
                // colormin has a bug that transforms rgba into incorrect hsl values
                cssProcessorOptions: {colormin: false}
            }),
            new StyleLintPlugin({
                files: './src/Zicht/Bundle/FrameworkExtraBundle/Resources/sass/**/*.scss'
            }),
            // To see notifications, you need `notify-osd` installed on your system, i.e. `sudo apt install notify-osd`
            new WebpackBuildNotifierPlugin({title: "framework-extra-bundle", sound: false})
        ]
    };
};
