const paths = require('./main/core/Resources/webpack/paths')
const plugins = require('./main/core/Resources/webpack/plugins')
const libraries = require('./main/core/Resources/webpack/libraries')

module.exports = {
  entry: libraries,
  output: {
    path: paths.output(),
    filename: '[name]-[hash].js',
    library: '[name]_[hash]'
  },
  resolve: {
    root: paths.bower()
  },
  plugins: [
    plugins.assetsInfoFile('webpack-dlls.json'),
    plugins.bowerFileLookup(),
    plugins.dlls()
  ],
  devtool: false
}
