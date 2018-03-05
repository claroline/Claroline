/**
 * Webpack configuration for PROD environments.
 */

const Encore = require('@symfony/webpack-encore')

const entries = require('./webpack/entries')
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
  .configureManifestPlugin(options => {
    options.fileName = 'manifest.lib.json'
  })
  .configureUglifyJsPlugin(options => {
    options.compress = true
    options.beautify = true
  })
  .addPlugin(plugins.nodeEnvironment('production'))
  .addPlugin(plugins.distributionShortcut())
  .addPlugin(plugins.assetsInfoFile())
  .addPlugin(plugins.scaffoldingDllReference())
  .addPlugin(plugins.reactDllReference())
  .addPlugin(plugins.angularDllReference())
  .addPlugin(plugins.configShortcut())
  .addPlugin(plugins.commonsChunk())

  // Babel configuration
  .configureBabel(babelConfig => {
    babelConfig.compact = true
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

config.resolve.modules = ['./node_modules', './web/packages']
//in that order it solves some issues... if we start with bower.json, many packages don't work
config.resolve.descriptionFiles = ['package.json', '.bower.json', 'bower.json']
config.resolve.alias = shared.aliases()
config.externals = shared.externals()

// export the final configuration
module.exports = config
