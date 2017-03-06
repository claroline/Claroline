const path = require('path')

// distribution package directory
const distribution = () => path.resolve(__dirname, '..', '..',  '..', '..', '..')

// platform root directory
const root = () => path.resolve(distribution(), '..', '..', '..')

// bower directory (currently main third-party modules dir instead of node_modules)
const bower = () => path.resolve(root(), 'web', 'packages')

// output directory (compiled entries)
const output = () => path.resolve(root(), 'web', 'dist')

module.exports = {distribution, root, bower, output}
