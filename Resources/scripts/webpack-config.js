const path = require('path')
const webpack = require('webpack')

const Resolver = webpack.ResolverPlugin
const Replacement = webpack.NormalModuleReplacementPlugin
const Description = Resolver.DirectoryDescriptionFilePlugin
const Commons = webpack.optimize.CommonsChunkPlugin

/**
 * Builds a webpack configuration suitable for export.
 *
 * @param frontendConfig  The configuration from app/config/frontend.json
 * @param rootDir         The path of the root directory of the application
 * @returns Object
 */
function configure(frontendConfig, rootDir) {
  return {
    entry: frontendConfig.webpack.entry,
    output: {
      path: path.resolve(rootDir, 'web'),
      publicPath: '/',
      filename: 'dist/[name].js'
    },
    resolve: {
      root: path.resolve(rootDir, 'web', 'packages')
    },
    plugins: [
      new Replacement(/^#\//, makeBundleResolver(frontendConfig.bundles)),
      new Resolver(new Description('.bower.json', ['main'])),
      new Commons({ name: 'commons', minChunks: 10 }),
      ...frontendConfig.webpack.commons.map(common => new Commons(common))
    ],
    module: {
      loaders: [
        {
          test: /\.js$/,
          exclude: /(node_modules|packages)/,
          loader: 'babel',
          query: {
            cacheDirectory: true,
            presets: ['es2015'],
            plugins: ['transform-runtime']
          }
        }
      ]
    },
    devServer: {
      proxy: {
        '/app_dev.php*': {
          target: frontendConfig.webpack.proxyTarget,
          secure: false
        }
      }
    }
  }
}

/**
 * Builds a function that will try to convert a internal webpack request
 * for a module starting with "#/" (i.e by convention, a module located
 * in a claroline bundle) into a request with an absolute path.
 *
 * @param availableBundles A list of available bundles
 */
function makeBundleResolver(availableBundles) {
  return request => {
    const target = request.request.substr(2)
    const parts = target.split('/')

    if (availableBundles[parts[0]]) {
      request.request = path.resolve(
        availableBundles[parts[0]],
        ...parts.slice(1)
      )
    }
  }
}

module.exports = configure
