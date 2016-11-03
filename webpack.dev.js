const paths = require('./main/core/Resources/webpack/paths')
const entries = require('./main/core/Resources/webpack/entries')
const shared = require('./main/core/Resources/webpack/shared')
const plugins = require('./main/core/Resources/webpack/plugins')
const loaders = require('./main/core/Resources/webpack/loaders')

module.exports = {
  entry: entries(),
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
    ...plugins.dllReferences(shared.dllManifests())
  ],
  module: {
    loaders: [
      loaders.babel(),
      loaders.rawHtml(),
      loaders.jqueryUiNoAmd(),
      loaders.css(),
      loaders.imageUris(),
      loaders.modernizr()
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
