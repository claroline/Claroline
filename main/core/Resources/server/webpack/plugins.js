const webpack = require('webpack')
const AssetsPlugin = require('assets-webpack-plugin')
const FailPlugin = require('webpack-fail-plugin')
const CircularDependencyPlugin = require('circular-dependency-plugin')
const paths = require('../paths')
const ConfigurationPlugin = require('./build/configuration/plugin')

/**
 * Allows webpack to discover entry files of modules stored in the bower
 * web/packages directory by inspecting their bower config (default is to
 * look in package.json).
 */
const bowerFileLookup = () => {
  return new webpack.ResolverPlugin(
    new webpack.ResolverPlugin.DirectoryDescriptionFilePlugin(
      '.bower.json',
      ['main']
    )
  )
}

/**
 * Adds a custom resolver that will try to convert internal webpack requests for
 * modules starting with "#/" (i.e by convention, modules located in the
 * distribution package) into requests with a resolved absolute path. Modules
 * are expected to live in the "Resources/modules" directory of each bundle,
 * so that part must be omitted from the import statement.
 *
 * Example:
 *
 * import baz from '#/main/core/foo/bar'
 *
 * will be resolved to:
 *
 * /path/to/vendor/claroline/distribution/main/core/Resources/modules/foo/bar
 */
const distributionShortcut = () => {
  return new webpack.NormalModuleReplacementPlugin(/^#\//, request => {
    const parts = request.request.substr(2).split('/')
    const resolved = [...parts.slice(0, 2), 'Resources/modules', ...parts.slice(2)]
    request.request = [paths.root(), 'vendor/claroline/distribution', ...resolved].join('/')
  })
}

/**
 * Adds a custom resolver that will resolve the configuration file path
 *
 * Example:
 *
 * import from 'clarolineconfig'
 */
const configShortcut = () => {
  return new webpack.NormalModuleReplacementPlugin(/^bundle-configs$/, request => {
    request.request = paths.root() + '/web/dist/plugins-config.js'
  })
}


/**
 * Builds a independent bundle for frequently requested modules (might require
 * minChunks adjustments).
 */
const commonsChunk = () => {
  return new webpack.optimize.CommonsChunkPlugin({
    name: 'commons',
    minChunks: 5
  })
}

/**
 * Outputs information about generated assets in a dedicated file
 * ("webpack-assets.json" by default). This is useful to retrieve assets names
 * when a hash has been used for cache busting.
 */
const assetsInfoFile = filename => {
  return new AssetsPlugin({
    fullPath: false,
    prettyPrint: true,
    filename: filename || 'webpack-assets.json'
  })
}

/**
 * Removes equal or similar files from the final output.
 */
const dedupeModules = () => {
  return new webpack.optimize.DedupePlugin()
}

/**
 * Allows to freely define variables inside built files. Here it is used to set
 * the node environment variable to "production", so that libraries that make
 * use of that flag for debug purposes are silent.
 */
const defineProdEnv = () => {
  return new webpack.DefinePlugin({
    'process.env': {
      NODE_ENV: JSON.stringify('production')
    }
  })
}

/**
 * Ensures no assets are emitted that include errors.
 */
const rejectBuildErrors = () => {
  return new webpack.NoErrorsPlugin({
    bail: true
  })
}

/**
 * Makes webpack exit with a non-zero status code in case of error when not
 * in watch mode.
 *
 * @see https://github.com/webpack/webpack/issues/708
 */
const exitWithErrorCode = () => {
  return FailPlugin
}

/**
 * Bundles entries in separate DLLs to improve build performance.
 */
const dlls = () => {
  return new webpack.DllPlugin({
    path: `${paths.output()}/[name].manifest.json`,
    name: '[name]_[hash]'
  })
}

/**
 * Includes references to generated DLLs
 */
const dllReferences = manifests => {
  return manifests.map(manifest => new webpack.DllReferencePlugin({
    context: '.',
    manifest
  }))
}

const clarolineConfiguration = () => {
  return new ConfigurationPlugin()
}

/**
 * Detects circular dependencies in modules and issues warnings or errors.
 * Circular dependencies can be a source of mysterious bugs:
 *
 * @see http://stackoverflow.com/questions/35240716/webpack-import-returns-undefined-depending-on-the-order-of-imports
 */
const noCircularDependencies = () => {
  return new CircularDependencyPlugin({
    exclude: /web\/packages|node_modules/,
    failOnError: false // default: only warnings
  })
}

/**
 * Makes the build crash in case of babel compilation errors. That
 * behaviour is pretty much needed when testing with karma.
 *
 * @see https://github.com/webpack/karma-webpack/issues/49
 */
const rethrowCompilationErrors = () => {
  return function () {
    this.plugin('done', stats => {
      if (stats.compilation.errors.length > 0) {
        if (stats.compilation.errors[0].name === 'ModuleBuildError') {
          // assume it's a babel syntax error and rethrow it
          throw stats.compilation.errors[0].error.error
        }

        throw new Error(stats.compilation.errors[0].message)
      }
    })
  }
}

module.exports = {
  bowerFileLookup,
  distributionShortcut,
  commonsChunk,
  assetsInfoFile,
  dedupeModules,
  defineProdEnv,
  rejectBuildErrors,
  exitWithErrorCode,
  noCircularDependencies,
  rethrowCompilationErrors,
  dllReferences,
  dlls,
  configShortcut,
  clarolineConfiguration
}
