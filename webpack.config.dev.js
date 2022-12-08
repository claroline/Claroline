/**
 * Webpack configuration for DEV environments.
 */

const webpack = require('webpack')

const entries = require('./webpack/entries')
const config = require('./webpack/config')
const paths = require('./webpack/paths')

const assetsFile = require('./webpack/plugins/assets-file')
const vendorDistributionShortcut = require('./webpack/plugins/vendor-shortcut')
const distributionShortcut = require('./webpack/plugins/distribution-shortcut')
// dev
const circularDependency = require('./webpack/plugins/circular-dependency')

const babel = require('./webpack/rules/babel')

module.exports = {
  mode: 'development',
  // configure webpack logs
  stats: {
    colors: true,
    errorDetails: true
  },
  devServer: {
    // HMR is broken in our env and is tricky to enable
    // https://webpack.js.org/guides/hot-module-replacement/#gotchas
    hot: false,
    client: {
      // display compile errors in browser
      overlay: {
        errors: true,
        warnings: false, // hide warning (there are circular deps I can't remove)
      },
    },
    port: 8080,
    static: paths.output(),
    headers: {
      'Access-Control-Allow-Origin': '*'
    }
  },
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
    assetsFile('webpack-dev.json'),
    vendorDistributionShortcut(),
    distributionShortcut(),
    // this is required by swagger-ui-react
    new webpack.ProvidePlugin({
      Buffer: ['buffer', 'Buffer']
    }),
    // dev tools
    circularDependency()
  ],
  optimization: {
    moduleIds: 'deterministic',
    // bundle webpack runtime code into a single chunk file
    // it avoids having it embed in each generated chunk
    runtimeChunk: 'single',
    splitChunks: {
      // just use a more agnostic char for chunk names generation (default if ~)
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
          minChunks: 4,
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
