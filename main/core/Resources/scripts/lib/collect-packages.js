const assert = require('assert')
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
function collectPackages(rootDir) {
  assert.equal(typeof rootDir, 'string', 'Expected string')

  const stats = fs.statSync(rootDir);

  if (!stats.isDirectory()) {
    throw new Error(`${rootDir} is not a directory`)
  }

  return getPackageDefinitions(rootDir)
}

function getPackageDefinitions(rootDir) {
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

  const filteredPackages = packages.filter(def =>
    def.type === 'claroline-core' || def.type === 'claroline-plugin'
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
      assets = getMetaEntries(path)
      newDef.assets = assets
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

function getMetaEntries(targetDir) {
  var data
  var metadata = { webpack: { entry: {} } }
  const src = ['main', 'plugin']

  src.filter(dir => fs.existsSync(targetDir + '/' + dir)).forEach(function(el) {
    var dir = targetDir + '/' + el
    var bundles = fs.readdirSync(dir)
    bundles.forEach(function(bundle) {
      try {
        data = JSON.parse(fs.readFileSync(`${dir}/${bundle}/assets.json`, 'utf8'))
        Object.keys(data.webpack.entry).forEach(entry => {
          var parts = dir.split("/");
          var lastDir = parts[parts.length - 1];
          metadata.webpack.entry[entry] = {
            name: data.webpack.entry[entry],
            prefix: `${dir}/${bundle}`,
            dir: lastDir,
            bundle: bundle
          }
        })
      } catch(err) {}
    })
  })

  return metadata
}

function isMetaPackage(rootDir) {
  return fs.existsSync(rootDir + '/main')
}

module.exports = collectPackages
