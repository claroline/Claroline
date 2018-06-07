/**
 * Webpack configuration for DEV environments.
 */

const Encore = require('@symfony/webpack-encore')

const entries = require('./webpack/entries')
const config = require('./webpack/config')
const plugins = require('./webpack/plugins')
const paths = require('./webpack/paths')
const shared = require('./webpack/shared')

Encore
  .configureRuntimeEnvironment('dev')
  .setOutputPath(paths.output())
  .setPublicPath('/dist')
  .autoProvidejQuery()
  .setManifestKeyPrefix('/dist')
  .enableSourceMaps(true)
  //.cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  // enables files versioning for browser cache busting
  .enableVersioning(true)

  // Plugins
  .configureDefinePlugin(options => {
    options['process.env'] = {
      NODE_ENV: JSON.stringify('development')
    }
  })
  .configureManifestPlugin(options => {
    options.fileName = 'manifest.lib.json'
  })
  .configureUglifyJsPlugin(options => {
    options.compress = false
    options.beautify = false
  })
  .addPlugin(plugins.assetsInfoFile())
  .addPlugin(plugins.distributionShortcut())
  .addPlugin(plugins.scaffoldingDllReference())
  .addPlugin(plugins.reactDllReference())
  .addPlugin(plugins.angularDllReference())
  .addPlugin(plugins.commonsChunk())

  // Babel configuration
  .configureBabel(babelConfig => {
    babelConfig.compact = false

    // for webpack dynamic import (compile `import()` and generate targeted chunks)
    babelConfig.plugins.push('syntax-dynamic-import')
  })
  .enableReactPreset()

  // todo : this loader will no longer be required when angular will be fully removed
  .addLoader({
    test: /\.html$/,
    loader: 'html-loader'
  })

  // configuration from plugins
  .addEntry('plugins', config.collectConfig())

// grab plugins entries
const collectedEntries = entries.collectEntries()
Object.keys(collectedEntries).forEach(key => Encore.addEntry(key, collectedEntries[key]))

const webpackConfig = Encore.getWebpackConfig()

webpackConfig.watchOptions = {
  poll: 2000,
  ignored: /web\/packages|node_modules/
}

webpackConfig.resolve.modules = ['./node_modules', './web/packages']
//in that order it solves some issues... if we start with bower.json, many packages don't work
webpackConfig.resolve.descriptionFiles = ['package.json', '.bower.json', 'bower.json']
webpackConfig.resolve.alias = shared.aliases()
webpackConfig.externals = shared.externals()

// for webpack dynamic import
// override name for non entry chunk files
// todo : find a way to use versioning
webpackConfig.output.chunkFilename = '[name].js'

// export the final configuration
module.exports = webpackConfig
