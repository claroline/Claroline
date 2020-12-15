const fs = require('fs')
const paths = require('../paths')
const packages = require('../packages')

/**
 * Gets packages styles.
 *
 * Creates an "entry" map containing all the entries declared in claroline
 * packages "assets.json" manifests. Entries are automatically prefixed to
 * avoid name collisions across bundles.
 */
function collectEntries() {
  const registeredPackages = packages.collect(paths.root())
  const stylesPackages = registeredPackages.filter(def => def.assets && def.assets.styles)

  return extractEntries(stylesPackages)
}

/**
 * Merges "entry" sections of package configs into one object,
 * prefixing entry names and paths with package names/paths.
 */
function extractEntries(packages) {
  return packages
    .filter(def => def.assets.styles && def.assets.styles.entry)
    .reduce((entries, def) => {
      Object.keys(def.assets.styles.entry).forEach(entry => {
        def.meta ?
          entries[`${def.name}-${def.assets.styles.entry[entry].dir}-${entry}`] =
            `${def.assets.styles.entry[entry].prefix}/Resources/styles/${def.assets.styles.entry[entry].name}` :
          entries[`${def.name}-${entry}`] = `${def.path}/Resources/styles/${def.assets.styles.entry[entry]}`
      })

      return entries
    }, {})
}

module.exports = {
  collectEntries
}
