/**
 * Webpack configuration for PROD environments.
 */

const Encore = require('@symfony/webpack-encore')

const entries = require('./webpack/entries')
const config = require('./webpack/config')
const plugins = require('./webpack/plugins')
const paths = require('./webpack/paths')
const shared = require('./webpack/shared')

Encore
  .configureRuntimeEnvironment('dev') // todo : find why we need to use the dev env
  .setOutputPath(paths.output())
  .setPublicPath('/dist')
  .autoProvidejQuery()
  .setManifestKeyPrefix('/dist')
  .enableSourceMaps(false)
  //.cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  // enables files versioning for browser cache busting
  .enableVersioning(true)

  // Plugins
  .configureDefinePlugin(options => {
    options['process.env'] = {
      NODE_ENV: JSON.stringify('production')
    }
  })
  .configureManifestPlugin(options => {
    options.fileName = 'manifest.lib.json'
  })
  .configureUglifyJsPlugin(options => {
    options.compress = true
    options.beautify = true
  })
  .addPlugin(plugins.assetsInfoFile())
  .addPlugin(plugins.distributionShortcut())
  .addPlugin(plugins.scaffoldingDllReference())
  .addPlugin(plugins.reactDllReference())
  .addPlugin(plugins.angularDllReference())
  .addPlugin(plugins.commonsChunk())

  // Babel configuration
  .configureBabel(babelConfig => {
    babelConfig.compact = true

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
