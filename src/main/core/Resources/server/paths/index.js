const path = require('path')

// platform root directory
const root = () => path.resolve(__dirname, '..', '..', '..', '..', '..', '..')

// platform public directory
const web = () => path.resolve(root(), 'public')

// output directory (compiled entries)
const output = () => path.resolve(web(), 'dist')

const dirname = path.dirname
const relative = path.relative

module.exports = {
  root,
  web,
  output,
  dirname,
  relative
}
