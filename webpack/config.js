const fs = require('fs')
const packages = require('./packages')
const paths = require('./paths')

function collectConfig() {
  const registeredPackages = packages.collect(paths.root())
  let config = []

  registeredPackages.forEach(el => {
    if (packages.isMetaPackage(el.path)) {
      config = config.concat(getMetaEntries(el.path))
    } else {
      console.log('No implementation for client configuration file for the usual package.')
    }
  })

  return config
}

function getMetaEntries(targetDir) {
  const config = []

  packages.getMetaBundles(targetDir).forEach(bundle => {
    let configFile = `${bundle}/Resources/modules/plugin.js`
    // Fixes path in windows (back slashes are not escaped)
    configFile = configFile.replace(/\\/g, '/')

    if (fs.existsSync(configFile)) {
      config.push(configFile)
    }
  })

  return config
}

module.exports = {
  collectConfig
}
