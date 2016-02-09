const path = require('path')
const webpack = require('webpack')
const config = require('./app/config/frontend.json')

const Resolver = webpack.ResolverPlugin
const Replacement = webpack.NormalModuleReplacementPlugin
const Description = Resolver.DirectoryDescriptionFilePlugin
const Commons = webpack.optimize.CommonsChunkPlugin

/**
 * Tries to convert a request for a module starting with "#/"
 * (i.e by convention, a module located in a claroline bundle)
 * into a request with an absolute path.
 */
function resolveBundle(request) {
  const target = request.request.substr(2)
  const parts =  target.split('/')

  if (config.bundles[parts[0]]) {
    request.request = path.resolve(
      config.bundles[parts[0]],
      ...parts.slice(1)
    )
  }
}

module.exports = {
  entry: config.webpack.entry,
  output: {
    path: path.resolve('./web'),
    publicPath: '/',
    filename: 'dist/[name].js'
  },
  resolve: {
    root: path.resolve('./web/packages')
  },
  plugins: [
    new Replacement(/^#\//, resolveBundle),
    new Resolver(new Description('.bower.json', ['main'])),
    new Commons({ name: 'commons', minChunks: 10 }),
    ...config.webpack.commons.map(common => new Commons(common))
  ],
  devServer: {
    proxy: {
      '/app_dev.php*': {
        target: config.proxyTarget,
        secure: false
      }
    }
  }
}
