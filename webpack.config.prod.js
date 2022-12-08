/**
 * Webpack configuration for PROD environments.
 */

const webpack = require('webpack')

const entries = require('./webpack/entries')
const config = require('./webpack/config')
const paths = require('./webpack/paths')

const assetsFile = require('./webpack/plugins/assets-file')
const vendorDistributionShortcut = require('./webpack/plugins/vendor-shortcut')
const distributionShortcut = require('./webpack/plugins/distribution-shortcut')

const babel = require('./webpack/rules/babel')

module.exports = {
  mode: 'production',
  // configure webpack logs (show minimal info)
  stats: {
    colors: true,
    all: false,
    modules: true,
    errors: true,
    warnings: false,
    moduleTrace: true,
    errorDetails: true
  },
  // configure output files
  output: {
    path: paths.output(),
    publicPath: '/dist',
    // use content hash in the name of generated file for proper caching
    filename: '[name].[contenthash].js', // this is for static entries declared in assets.json
    chunkFilename: '[name].[contenthash].js' // this is for dynamic entries declared in modules/plugin.js
  },
  module: {
    rules: [
      babel()
    ]
  },
  // grab entries to compile
  entry: Object.assign({},
    // get the one defined in assets.json file (static entries)
    entries.collectEntries(),
    // get the one defined in modules/plugin.js file (dynamic entries)
    {plugins: config.collectConfig()}
  ),
  plugins: [
    assetsFile('webpack-prod.json'),
    vendorDistributionShortcut(),
    distributionShortcut(),
    // this is required by swagger-ui-react
    new webpack.ProvidePlugin({
      Buffer: ['buffer', 'Buffer']
    })
  ],
  optimization: {
    moduleIds: 'deterministic',
    // bundle webpack runtime code into a single chunk file
    // it avoids having it embed in each generated chunk
    runtimeChunk: 'single',
    splitChunks: {
      // just use a more agnostic char for chunk names generation (default is ~)
      automaticNameDelimiter: '-',
      cacheGroups: {
        // bundle common vendors
        vendor: {
          name: 'vendor',
          test: /[\\/]node_modules[\\/]/,
          chunks: 'all',
          minChunks: 4,
          priority: -10,
          reuseExistingChunk: true
        },
        app: {
          name: 'app',
          test: /[\\/]src[\\/]main[\\/]app/,
          minChunks: 4,
          priority: -20,
          chunks: 'all',
          reuseExistingChunk: true
        },
        // bundle common modules to decrease generated file size
        default: {
          minChunks: 5,
          priority: -30,
          reuseExistingChunk: true
        }
      }
    }
  },
  resolve: {
    modules: ['./node_modules'],
    extensions: ['.js', '.jsx']
  }
}
