const paths = require('./main/core/Resources/server/paths')
const entries = require('./main/core/Resources/server/webpack/entries')
const shared = require('./main/core/Resources/server/webpack/shared')
const plugins = require('./main/core/Resources/server/webpack/plugins')
const loaders = require('./main/core/Resources/server/webpack/loaders')

module.exports = {
  entry: entries.collectEntries(),
  output: {
    path: paths.output(),
    publicPath: 'http://localhost:8080/dist',
    filename: '[name].js'
  },
  resolve: {
    root: paths.bower(),
    alias: shared.aliases()
},
  plugins: [
    plugins.assetsInfoFile(),
    plugins.bowerFileLookup(),
    plugins.distributionShortcut(),
    plugins.clarolineConfiguration(),
    plugins.configShortcut(),
    plugins.noCircularDependencies(),
    ...plugins.dllReferences(shared.dllManifests())
  ],
  module: {
    loaders: [
      loaders.babel(),
      loaders.rawHtml(),
      loaders.jqueryUiNoAmd(),
      loaders.css(),
      loaders.imageUris(),
      loaders.modernizr(),
      loaders.json()
    ]
  },
  externals: shared.externals(),
  devtool: 'eval',
  devServer: {
    headers: {
      'Access-Control-Allow-Origin': '*'
    }
  }
}
