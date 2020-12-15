/**
 * Webpack configuration for DEV environments.
 */

const entries = require('./webpack/entries')
const config = require('./webpack/config')
const paths = require('./webpack/paths')
const shared = require('./webpack/shared')

const assetsFile = require('./webpack/plugins/assets-file')
const hashedModuleIds = require('./webpack/plugins/hashed-module-ids')
const vendorDistributionShortcut = require('./webpack/plugins/vendor-shortcut')
const distributionShortcut = require('./webpack/plugins/distribution-shortcut')

// dev
const circularDependency = require('./webpack/plugins/dev/circular-dependency')
const hotModuleReplacement = require('./webpack/plugins/dev/hot-module-replacement')
const notifier = require('./webpack/plugins/dev/notifier')

const babel = require('./webpack/rules/babel')

module.exports = {
  mode: 'development',
  // configure webpack logs
  stats: {
    colors: true,
    errorDetails: true
  },
  devServer: {
    hot: true,
    port: 8080,
    contentBase: paths.output(),
    headers: {
      'Access-Control-Allow-Origin': '*'
    }
  },
  output: {
    path: paths.output(),
    publicPath: '/dist',
    // webpack-dev-server requires to use the hash of the build
    // it doesn't accept [contenthash] like in prod
    filename: '[name].[hash].js', // this is for static entries declared in assets.json
    chunkFilename: '[name].[hash].js' // this is for dynamic entries declared in modules/plugin.js
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
    hashedModuleIds(),
    vendorDistributionShortcut(),
    distributionShortcut(),

    // dev tools
    hotModuleReplacement(),
    notifier(),
    //circularDependency()
  ],
  optimization: {
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
    modules: ['./node_modules', './public/packages'],
    extensions: ['.js', '.jsx']
  },
  externals: shared.externals()
}
