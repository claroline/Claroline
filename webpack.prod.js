const paths = require('./main/core/Resources/server/paths')
const entries = require('./main/core/Resources/server/webpack/entries')
const shared = require('./main/core/Resources/server/webpack/shared')
const plugins = require('./main/core/Resources/server/webpack/plugins')
const loaders = require('./main/core/Resources/server/webpack/loaders')

if (process.env.NODE_ENV !== 'production') {
  throw new Error('Production builds must have NODE_ENV=production')
}

module.exports = {
  entry: entries.collectEntries(),
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
    plugins.clarolineConfiguration(),
    plugins.configShortcut(),
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
  devtool: false
}
