const path = require('path')
const webpack = require('webpack')
const failPlugin = require('webpack-fail-plugin')

/**
 * Builds a webpack configuration suitable for export.
 *
 * @param rootDir         The path of the root directory of the application
 * @param packages        An array of bundles configs
 * @param isWatchMode     Whether webpack is to be run in watch mode
 * @returns Object
 */
function configure(rootDir, packages, isWatchMode) {
  const isProd = !isWatchMode

  // first we must parse the webpack configs of each bundle
  // and prefix/normalize them to avoid name collisions
  const webpackPackages = packages.filter(def => def.assets && def.assets.webpack)
  const bundles = webpackPackages.map(def => def.name)
  const normalizedPackages = normalizeNames(webpackPackages)
  const normalizedBundles = normalizedPackages.map(def => def.name)
  const entries = extractEntries(normalizedPackages)
  const commons = extractCommons(normalizedPackages)

  // all entries are compiled in the web/dist directory
  const output = {
    path: path.resolve(rootDir, 'web/dist'),
    publicPath: 'http://localhost:8080/dist',
    filename: '[name].js'
  }

  // third-party modules are taken from the web/packages directory,
  // instead of node_modules (which stores only dev dependencies)
  const root = path.resolve(rootDir, 'web', 'packages')

  // in every environment, plugins are needed for things like bower
  // modules support, bundle resolution, common chunks extraction, etc.
  const plugins = [
    makeBundleResolverPlugin(normalizedBundles),
    makeBowerPlugin(),
    //makeBaseCommonsPlugin(),
    ...makeBundleCommonsPlugins(commons)
  ]

  // prod build has additional constraints
  // TODO: use tree-shaking when webpack 2.0 is stable!
  if (isProd) {
    plugins.push(
      //makeUglifyJsPlugin(),
      makeDedupePlugin(),
      makeDefinePlugin(),
      makeNoErrorsPlugin(),
      makeFailOnErrorPlugin()
    )
  }

  const loaders = [
    makeJsLoader(isProd),
    makeRawLoader()
  ]

  return {
    entry: entries,
    output: output,
    resolve: { root: root },
    plugins: plugins,
    module: { loaders: loaders },
    devServer: {
      headers: { "Access-Control-Allow-Origin": "*" }
    },
    _debug: {
      'Detected webpack configs': bundles,
      'Compiled entries': entries,
      'Compiled common chunks': commons
    }
  }
}

/**
 * Removes the "bundle" portion of package names and replaces
 * slashes by hyphens. Example:
 *
 * "foo/bar-bundle" -> "foo-bar"
 */
function normalizeNames(packages) {
  return packages.map(def => {
    var parts = def.name.split(/\/|\-/)

    if (parts[parts.length - 1] === 'bundle') {
      parts.pop()
    }

    def.name = parts.join('-')

    return def
  })
}

/**
 * Merges "entry" sections of package configs into one object,
 * prefixing entry names and paths with package names/paths.
 */
function extractEntries(packages) {
  return packages
    .filter(def => def.assets.webpack && def.assets.webpack.entry)
    .reduce((entries, def) => {
      Object.keys(def.assets.webpack.entry).forEach(entry => {
         def.meta ?
           entries[`${def.name}-${def.assets.webpack.entry[entry].dir}-${def.assets.webpack.entry[entry].bundle}-${entry}`] = `${def.assets.webpack.entry[entry].prefix}/Resources/${def.assets.webpack.entry[entry].name}`:
           entries[`${def.name}-${entry}`] = `${def.path}/Resources/${def.assets.webpack.entry[entry]}`
      })

      return entries
    }, {})
}

/**
 * TODO: Implement this function
 *
 * Merges the "commons" sections of package configs.
 *
 */
function extractCommons(packages) {
  return []
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
  })
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
 * This plugin makes webpack exit with a non-zero status code
 * in case of error when not in watch mode.
 *
 * @see https://github.com/webpack/webpack/issues/708
 */
function makeFailOnErrorPlugin() {
  return failPlugin
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
      plugins: ['transform-runtime']
    }
  }
}

/**
 * This loader returns the file content as plain string,
 * without any transformation.
 */
function makeRawLoader() {
  return {
    test: /\.html$/,
    loader: 'raw'
  }
}

module.exports = configure
