const path = require('path')
const webpack = require('webpack')
const failPlugin = require('webpack-fail-plugin')
const assetsPlugin = require('assets-webpack-plugin')

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

  // see https://github.com/facebookincubator/create-react-app/blob/master/config/webpack.config.prod.js#L21
  // (note: the whole config should probably be refactored to match that format)
  if (isProd && process.env.NODE_ENV !== 'production') {
    throw new Error('Production builds must have NODE_ENV=production.');
  }

  // first we must parse the webpack configs of each bundle
  // and prefix/normalize them to avoid name collisions
  const webpackPackages = packages.filter(def => def.assets && def.assets.webpack)
  const packageNames = webpackPackages.map(def => def.name)
  const normalizedPackages = normalizeNames(webpackPackages)
  const entries = extractEntries(normalizedPackages)

  // all entries are compiled in the web/dist directory
  const output = {
    path: path.resolve(rootDir, 'web/dist'),
    publicPath: 'http://localhost:8080/dist',
    filename: isProd ? '[name]-[hash].js' : '[name].js'
  }

  // third-party modules are taken from the web/packages directory,
  // instead of node_modules (which stores only dev dependencies)
  const root = path.resolve(rootDir, 'web', 'packages')

  // plugins needed in every environment
  const plugins = [
    makeBundleResolverPlugin(rootDir),
    makeBowerPlugin(),
    makeAssetsPlugin()
  ]

  // prod build has additional constraints
  //
  // TODO: use tree-shaking when webpack 2.0 is stable
  // NOTE: uglify plugin isn't included due to problems with already minified
  //       files (see https://github.com/webpack/webpack/issues/537).
  //       Minification is handled as a separate step in the main package.json.
  if (isProd) {
    plugins.push(
      makeDedupePlugin(),
      makeDefinePlugin(),
      makeNoErrorsPlugin(),
      makeFailOnErrorPlugin(),
      makeCommonsPlugin()
    )
  }

  const loaders = [
    makeJsLoader(isProd),
    makeRawLoader(),
    makeJqueryUiLoader(),
    makeCssLoader()
  ]

  return {
    entry: entries,
    output: output,
    resolve: {
      root: root,
      alias: { jquery: __dirname + '/../../modules/jquery' }
    },
    plugins: plugins,
    module: { loaders: loaders },
    devtool: isProd ? false : 'cheap-module-eval-source-map',
    devServer: {
      headers: { 'Access-Control-Allow-Origin': '*' }
    },
    _debug: {
      'Detected webpack configs': packageNames,
      'Compiled entries': entries
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
           entries[`${def.name}-${def.assets.webpack.entry[entry].dir}-${entry}`] = `${def.assets.webpack.entry[entry].prefix}/Resources/modules/${def.assets.webpack.entry[entry].name}`:
           entries[`${def.name}-${entry}`] = `${def.path}/Resources/modules/${def.assets.webpack.entry[entry]}`
      })

      return entries
    }, {})
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
 * modules located in the distribution package) into requests with a resolved
 * absolute path. Modules are expected to live in the "Resources/modules"
 * directory of each bundle, so that part must be omitted from the import
 * statement.
 *
 * Example:
 *
 * import baz from '#/main/core/foo/bar'
 *
 * will be resolved to:
 *
 * /path/to/vendor/claroline/distribution/main/core/Resources/modules/foo/bar
 */
function makeBundleResolverPlugin(rootDir) {
  return new webpack.NormalModuleReplacementPlugin(/^#\//, request => {
    const parts = request.request.substr(2).split('/')
    const resolved = [...parts.slice(0, 2), 'Resources/modules', ...parts.slice(2)]
    request.request = [rootDir, 'vendor/claroline/distribution', ...resolved].join('/')
  })
}

/**
 * This plugin builds a common file for the whole platform
 * (might require minChunks adjustments)
 */
function makeCommonsPlugin() {
  return new webpack.optimize.CommonsChunkPlugin({
    name: 'commons',
    minChunks: 3
  })
}

/**
 * This plugin outputs information about generated assets in a dedicated file
 * ("webpack-assets.json" by default). This is useful to retrieve assets names
 * when a hash has been used for cache busting.
 */
function makeAssetsPlugin() {
  return new assetsPlugin({
    fullPath: false,
    prettyPrint: true
  });
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
    test: /\.jsx?$/,
    exclude: /(node_modules|packages)/,
    loader: 'babel',
    query: {
      cacheDirectory: true,
      presets: ['es2015', 'react'],
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

/**
 * This loader disables AMD for jQuery UI modules. The reason is that these
 * modules try to load jQuery via AMD first but get a version of jQuery which
 * isn't the one made globally available, causing several issues. This loader
 * could probably be removed when jQuery is required only through module
 * imports.
 */
function makeJqueryUiLoader() {
  return {
    test: /jquery-ui/,
    loader: 'imports?define=>false'
  }
}

/**
 * This loader loads CSS files.
 */
function makeCssLoader() {
  return {
    test: /\.css$/,
    loader: 'style!css'
  }
}

module.exports = configure
