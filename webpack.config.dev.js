// webpack.config.js
var Encore = require('@symfony/webpack-encore')

const entries = require('./webpack/entries')
const libraries = require('./webpack/libraries')
const webpack = require('webpack')
const plugins = require('./webpack/plugins')
const paths = require('./webpack/paths')
const shared = require('./webpack/shared')
const collectedEntries = entries.collectEntries()

Encore
  .setOutputPath(paths.output())
  //localhost***1080 doesn't work properly
  .setPublicPath('/dist')
  .autoProvidejQuery()
  .enableReactPreset()
  .setManifestKeyPrefix('/dist')
  .enableSourceMaps(true)//false si plus rapide
  //.cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableVersioning(true)
  .configureManifestPlugin(options => options.fileName = 'manifest.lib.json')
  .addPlugin(plugins.assetsInfoFile())
  .addPlugin(plugins.distributionShortcut())
  .addPlugin(plugins.reactDllReference())
  .addPlugin(plugins.angularDllReference())
  .addPlugin(plugins.configShortcut())
  .addPlugin(plugins.commonsChunk())

  //fixes performance issues
  .configureUglifyJsPlugin(uglifyJsPluginOptionsCallback = (options) => {
      options.compress = false
      options.beautify = false
  })
  .configureBabel(babelConfig => {
      babelConfig.compact = false
  })
  .addLoader({test: /\.html$/, loader: 'html-loader'})

Object.keys(collectedEntries).forEach(key => Encore.addEntry(key, collectedEntries[key]))

var config = Encore.getWebpackConfig()

config.watchOptions = {
  poll: 2000,
  ignored: /web\/packages|node_modules/
}

config.resolve.modules = ['./node_modules', './web/packages']
//in that order it solves some issues... if we start with bower.json, many packages don't work
config.resolve.descriptionFiles = ['package.json', '.bower.json', 'bower.json']
config.resolve.mainFields = ['main', 'browser']
config.resolve.aliasFields = ['browser']
config.resolve.alias = shared.aliases()
config.externals = shared.externals()

// export the final configuration
module.exports = config
