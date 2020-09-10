const path = require('path')

// distribution package directory
const distribution = () => path.resolve(__dirname, '..', '..',  '..', '..', '..')

// platform root directory
const root = () => path.resolve(distribution(), '..', '..', '..')

// platform public directory
const web = () => path.resolve(root(), 'public')

// output directory (compiled entries)
const output = () => path.resolve(web(), 'dist')

module.exports = {distribution, root, web, output}
