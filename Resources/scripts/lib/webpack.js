const path = require('path')
const webpack = require('webpack')

/**
 * Builds a webpack configuration suitable for export.
 *
 * @param frontendConfig  The configuration from app/config/frontend.json
 * @param rootDir         The path of the root directory of the application
 * @param isWatchMode     Whether webpack is to be run in watch mode
 * @returns Object
 */
function configure(frontendConfig, rootDir, isWatchMode) {
  const isProd = !isWatchMode
  const entries = frontendConfig.webpack.entry

  // all entries are compiled in the web/dist directory
  const output = {
    path: path.resolve(rootDir, 'web'),
    publicPath: '/',
    filename: 'dist/[name].js'
  }

  // third-party modules are taken from the web/packages directory,
  // instead of node_modules (which stores only dev dependencies)
  const root = path.resolve(rootDir, 'web', 'packages')

  // in every environment, plugins are needed for things like bower
  // modules support, bundle resolution, common chunks extraction, etc.
  const plugins = [
    makeBundleResolverPlugin(frontendConfig.bundles),
    makeBowerPlugin(),
    makeBaseCommonsPlugin(),
    ...makeBundleCommonsPlugins(frontendConfig.webpack.commons)
  ]

  // prod build has additional constraints
  // TODO: use tree-shaking when webpack 2.0 is stable!
  if (isProd) {
    plugins.push(
      makeUglifyJsPlugin(),
      makeDedupePlugin(),
      makeDefinePlugin(),
      makeNoErrorsPlugin()
    )
  }

  const loaders = [
    makeJsLoader(isProd)
  ]

  return {
    entry: entries,
    output: output,
    resolve: { root: root },
    plugins: plugins,
    module: { loaders: loaders },
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
 * This plugin allows webpack to discover entry files of modules
 * stored in the bower web/packages directory by inspecting their
 * bower config (default is to look in package.json).
 */
function makeBowerPlugin() {
  return new webpack.ResolverPlugin(
    new webpack.ResolverPlugin.DirectoryDescriptionFilePlugin(
      '.bower.json',
      ['main']
    )
  )
}

/**
 * This plugin adds a custom resolver that will try to convert internal
 * webpack requests for modules starting with "#/" (i.e by convention,
 * modules located in a claroline bundle) into requests with a resolved
 * absolute path.
 *
 * @param availableBundles A list of available bundles
 */
function makeBundleResolverPlugin(availableBundles) {
  return new webpack.NormalModuleReplacementPlugin(/^#\//, request => {
    const target = request.request.substr(2)
    const parts = target.split('/')

    if (availableBundles[parts[0]]) {
      request.request = path.resolve(
        availableBundles[parts[0]],
        ...parts.slice(1)
      )
    }
  })
}

/**
 * This plugin builds a common file for the whole platform
 * (might not be necessary or require minChunks adjustments)
 */
function makeBaseCommonsPlugin() {
  return new webpack.optimize.CommonsChunkPlugin({
    name: 'commons',
    minChunks: 10
  })
}

/**
 * These plugins build common files per bundle according to the
 * settings of webpack.commons coming from assets.json files.
 */
function makeBundleCommonsPlugins(commons) {
  return commons.map(config => {
    return new webpack.optimize.CommonsChunkPlugin(config)
  });
}

/**
 * This plugin minifies bundle files using UglifyJS.
 */
function makeUglifyJsPlugin() {
  return new webpack.optimize.UglifyJsPlugin({
    compress: {
      warnings: false
    }
  })
}

/**
 * This plugin removes equal or similar files from the output.
 */
function makeDedupePlugin() {
  return new webpack.optimize.DedupePlugin()
}

/**
 * This plugin allows to freely define variables inside built
 * files. Here it is used to set the node environment variable
 * to "production", so that libraries that make use of that flag
 * for debug purposes are silent.
 */
function makeDefinePlugin() {
  return new webpack.DefinePlugin({
    'process.env': {
      NODE_ENV: JSON.stringify('production')
    }
  })
}

/**
 * This plugin ensures no assets are emitted that include errors.
 */
function makeNoErrorsPlugin() {
  return new webpack.NoErrorsPlugin({
    bail: true
  })
}

/**
 * This loader enables es6 transpilation with babel.
 */
function makeJsLoader(isProd) {
  return {
    test: /\.js$/,
    exclude: /(node_modules|packages)/,
    loader: 'babel',
    query: {
      cacheDirectory: true,
      presets: ['es2015'],
      plugins: isProd ? ['transform-runtime'] : []
    }
  }
}

module.exports = configure
