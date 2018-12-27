const AssetsPlugin = require('assets-webpack-plugin')

/**
 * Outputs information about generated assets in a dedicated file
 * ("webpack-assets.json" by default). This is useful to retrieve assets names
 * when a hash has been used for cache busting.
 */
module.exports = (filename) => new AssetsPlugin({
  fullPath: false,
  prettyPrint: true,
  filename: filename || 'webpack-assets.json'
})
