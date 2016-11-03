const paths = require('./main/core/Resources/webpack/paths')
const entries = require('./main/core/Resources/webpack/entries')
const shared = require('./main/core/Resources/webpack/shared')
const plugins = require('./main/core/Resources/webpack/plugins')
const loaders = require('./main/core/Resources/webpack/loaders')

if (process.env.NODE_ENV !== 'production') {
  throw new Error('Production builds must have NODE_ENV=production')
}

module.exports = {
  entry: entries(),
  output: {
    path: paths.output(),
    filename: '[name]-[hash].js'
  },
  resolve: {
    root: paths.bower(),
    alias: shared.aliases()
  },
  plugins: [
    plugins.assetsInfoFile(),
    plugins.bowerFileLookup(),
    plugins.distributionShortcut(),
    plugins.defineProdEnv(),
    plugins.commonsChunk(),
    plugins.dedupeModules(),
    plugins.rejectBuildErrors(),
    plugins.exitWithErrorCode(),
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
  devtool: false
}
