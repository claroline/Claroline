const fs = require('fs')
const paths = require('../paths')
const packages = require('../packages')

/**
 * Creates an "entry" map containing all the entries declared in claroline
 * packages "assets.json" manifests. Entries are automatically prefixed to
 * avoid name collisions across bundles.
 */
function collectEntries() {
  const registeredPackages = packages.collect(paths.root())
  const webpackPackages = registeredPackages.filter(def => def.assets && def.assets.webpack)

  return extractEntries(webpackPackages)
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
          entries[`${def.name}-${def.assets.webpack.entry[entry].dir}-${entry}`] =
            `${def.assets.webpack.entry[entry].prefix}/Resources/modules/${def.assets.webpack.entry[entry].name}` :
          entries[`${def.name}-${entry}`] = `${def.path}/Resources/modules/${def.assets.webpack.entry[entry]}`
      })

      return entries
    }, {})
}

module.exports = {
  collectEntries
}
