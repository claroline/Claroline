const path = require('path')

// distribution package directory
const distribution = () => path.resolve(__dirname, '..', '..',  '..', '..', '..')

// platform root directory
const root = () => path.resolve(distribution(), '..', '..', '..')

// bower directory (currently main third-party modules dir instead of node_modules)
const bower = () => path.resolve(root(), 'web', 'packages')

// platform web directory
const web = () => path.resolve(root(), 'web')

// output directory (compiled entries)
const output = () => path.resolve(web(), 'dist')

module.exports = {distribution, root, web, bower, output}
