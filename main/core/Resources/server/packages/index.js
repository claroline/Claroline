const fs = require('fs')

/**
 * Collects information about currently installed
 * claroline packages. Each package will be represented
 * as an object literal with the following attributes:
 *
 *  - name:      name of the package declared in its composer.json file
 *  - path:      path of the package source directory
 *  - assets:    package assets config declared its assets.json file, if any
 */
function collect(rootDir) {
  const stats = fs.statSync(rootDir)

  if (!stats.isDirectory()) {
    throw new Error(`${rootDir} is not a directory`)
  }

  return normalizeNames(getDefinitions(rootDir))
}

function getDefinitions(rootDir) {
  const file = `${rootDir}/vendor/composer/installed.json`
  var data

  try {
    data = fs.readFileSync(file, 'utf8')
  } catch (err) {
    throw new Error('Cannot found package info (composer/installed.json)')
  }

  const packages = JSON.parse(data)

  if (!(packages instanceof Array) || packages.length < 1) {
    throw new Error('Cannot find packages in composer/installed.json')
  }

  const filteredPackages = packages.filter(
    def => def.type === 'claroline-core' || def.type === 'claroline-plugin'
  )

  return filteredPackages.map(extractPackageInfo(rootDir))
}

function extractPackageInfo(rootDir) {
  return def => {
    const targetDir = def['target-dir'] ? `/${def['target-dir']}` : ''
    const path = `${rootDir}/vendor/${def.name}${targetDir}`
    const newDef = {
      name: def.name,
      path,
      assets: false,
      meta: false
    }
    var data

    if (isMetaPackage(path)) {
      newDef.assets = getMetaEntries(path)
      newDef.meta = true
    } else {
      try {
        data = fs.readFileSync(`${path}/assets.json`, 'utf8')
        newDef.assets = JSON.parse(data)
      } catch (err) {}
    }

    return newDef
  }
}

function getMetaEntries (targetDir) {
  var data
  var assets = {}

  getMetaBundles(targetDir).forEach(bundle => {
    try {
      data = JSON.parse(fs.readFileSync(`${bundle}/assets.json`, 'utf8'))

      Object.keys(data).forEach(assetType => {
        Object.keys(data[assetType].entry).forEach(entry => {
          var parts = bundle.split('/')
          const bundleName = parts.pop()
          const lastDir = parts[parts.length - 1]

          if (!assets[assetType]) {
            assets[assetType] = { entry: {} }
          }

          assets[assetType].entry[`${bundleName}-${entry}`] = {
            name: data[assetType].entry[entry],
            prefix: bundle,
            dir: lastDir,
            bundle: bundleName
          }
        })
      })
    } catch(err) {}
  })

  return assets
}

function isMetaPackage(rootDir) {
  return fs.existsSync(rootDir + '/main')
}

function getMetaBundles(targetDir) {
  var bundles = []
  const src = ['main', 'plugin']

  src.filter(dir => fs.existsSync(targetDir + '/' + dir)).forEach(el => {
    var dir = targetDir + '/' + el
    bundles = bundles.concat(fs.readdirSync(dir).map(el => {
      return dir + '/' + el}))
  })

  return bundles
}

/**
 * Removes the "bundle" portion of package names and replaces
 * slashes by hyphens. Example:
 *
 * "foo/bar-bundle" -> "foo-bar"
 */
function normalizeNames(packages) {
  return packages.map(def => {
    def.name = normalizeName(def.name)
    return def
  })
}

/**
 *
 * @param name
 * @returns {*}
 */
function normalizeName(name) {
  var parts = name.split(/\/|\-/)

  if (parts[parts.length - 1] === 'bundle') {
    parts.pop()
  }

  name = parts.join('-')

  return name
}

module.exports = {
  collect,
  isMetaPackage,
  getMetaBundles,
  normalizeNames,
  normalizeName
}
