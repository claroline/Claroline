/**
 * Webpack configuration for common external dependencies.
 * This bundles main project dependencies using DLL plugin.
 */

const Encore = require('@symfony/webpack-encore')

const libraries = require('./webpack/libraries')
const plugins = require('./webpack/plugins')

Encore
  .configureRuntimeEnvironment('production')
  .setOutputPath('web/dist')
  .setPublicPath('/dist')
  //.cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(false)

  // Plugins
  .configureManifestPlugin(options => {
    options.fileName = 'manifest.dll.json'
  })
  .addPlugin(plugins.nodeEnvironment('production'))
  .addPlugin(plugins.dlls())
  .addPlugin(plugins.assetsInfoFile('webpack-dlls.json'))
  .addPlugin(plugins.clarolineConfiguration())

  // Babel configuration
  .configureBabel(babelConfig => {
    babelConfig.compact = true
  })

// registers project libraries
Object.keys(libraries).forEach(key => Encore.addEntry(key, libraries[key]))

const config = Encore.getWebpackConfig()

config.resolve.modules = ['./web/packages', './node_modules']
//in that order it solves some issues... if we start with bower.json, many packages don't work
config.resolve.descriptionFiles = ['package.json', '.bower.json', 'bower.json']

// export the final configuration
module.exports = config
