const webpack = require('webpack')
const paths = require('./../paths')

/**
 * Adds a custom resolver that will try to convert internal webpack requests for
 * modules starting with "~/" (i.e by convention, modules located in a distribution in the
 * vendor packages) into requests with a resolved absolute path. Modules
 * are expected to live in the "Resources/modules" directory of each bundle,
 * so that part must be omitted from the import statement.
 *
 * Attention : this does not work for vendor packages which contains only one plugin Bundle.
 *
 * Example:
 *
 * import bar from '~/formalibre/customer-bundle/plugin/culture-courses/foo/bar'
 *
 * will be resolved to:
 *
 * /path/to/vendor/formalibre/customer-bundle/plugin/culture-courses/Resources/modules/foo/bar
 */
module.exports = () => {
  return new webpack.NormalModuleReplacementPlugin(/^~\//, request => {
    const parts = request.request.substr(2).split('/')
    const resolved = [...parts.slice(0, 4), 'Resources/modules', ...parts.slice(4)]
    request.request = [paths.root(), 'vendor', ...resolved].join('/')
  })
}
