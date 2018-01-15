const path = require('path')
const paths = require('../paths')
const libraries = require('./libraries')

const externals = () => ({
  'jquery': 'jQuery',
  'angular': 'angular'
})

const aliases = () => ({
  modernizr$: path.resolve(paths.distribution(), '.modernizrrc'),
  'ng-table': 'ng-table/dist/ng-table'
})

const dllManifests = () => {
  return Object.keys(libraries).map(name => {
    return require(`${paths.output()}/${name}.manifest.json`)
  })
}

module.exports = {
  externals,
  aliases,
  dllManifests
}
