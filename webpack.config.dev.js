/**
 * Webpack configuration for DEV environments.
 */

const Encore = require('@symfony/webpack-encore')

const entries = require('./webpack/entries')
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
  .configureManifestPlugin(options => {
    options.fileName = 'manifest.lib.json'
  })
  .configureUglifyJsPlugin(options => {
    options.compress = false
    options.beautify = false
  })
  .addPlugin(plugins.nodeEnvironment('development'))
  .addPlugin(plugins.assetsInfoFile())
  .addPlugin(plugins.distributionShortcut())
  .addPlugin(plugins.scaffoldingDllReference())
  .addPlugin(plugins.reactDllReference())
  .addPlugin(plugins.angularDllReference())
  .addPlugin(plugins.configShortcut())
  .addPlugin(plugins.commonsChunk())

  // Babel configuration
  .configureBabel(babelConfig => {
    babelConfig.compact = false
  })
  .enableReactPreset()

  // todo : this loader will no longer be required when angular will be fully removed
  .addLoader({
    test: /\.html$/,
    loader: 'html-loader'
  })

// grab plugins entries
const collectedEntries = entries.collectEntries()
Object.keys(collectedEntries).forEach(key => Encore.addEntry(key, collectedEntries[key]))

const config = Encore.getWebpackConfig()

config.watchOptions = {
  poll: 2000,
  ignored: /web\/packages|node_modules/
}

config.resolve.modules = ['./node_modules', './web/packages']
//in that order it solves some issues... if we start with bower.json, many packages don't work
config.resolve.descriptionFiles = ['package.json', '.bower.json', 'bower.json']
config.resolve.alias = shared.aliases()
config.externals = shared.externals()

// export the final configuration
module.exports = config
