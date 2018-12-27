const libraries = require('./libraries')

const externals = () => ({
  // get jQuery instance from the script included in the HTML document
  // do not bundle it. Will be removed soon.
  'jquery': 'jQuery'
})

module.exports = {
  externals
}
