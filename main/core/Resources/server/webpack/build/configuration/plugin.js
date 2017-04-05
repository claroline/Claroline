const paths = require('../../../paths')
const packages = require('../../../packages')
const fs = require('fs')
const validator = require('./validator')

function ConfigurationPlugin() {
}

ConfigurationPlugin.prototype.apply = function (compiler) {
  var generated = false
  compiler.plugin('compile', function () {
    if (!generated) {
      console.log('\nGenerating claroline configuration file...')
      str = `module.exports = {${getConfigurations()}}`
      fs.writeFileSync(paths.root() + '/web/dist/plugins-config.js', str)
      generated = true
    }
  })
}

function getConfigurations() {
  const registeredPackages = packages.collect(paths.root())
  var str = ''

  registeredPackages.forEach(el => {
    if (packages.isMetaPackage(el.path)) {
      str += getMetaEntries(el.path)
    } else {
      console.log('No implementation for client configuration file for the usual package.')
    }
  })

  return str
}

function getMetaEntries(targetDir) {
  var requirements = []

  packages.getMetaBundles(targetDir).forEach(bundle => {
    var configFile = `${bundle}/Resources/config/config.js`
    // Fixes path in windows (back slashes are not escaped)
    configFile = configFile.replace(/\\/g, '/')

    if (fs.existsSync(configFile)) {
      var plugin = require(configFile)
      validator.validate(plugin)
      var mod = bundle.split('/').pop()
      requirements.push(`    ${mod}: require('${configFile}')`)
    }
  })

  return requirements.join(',\n')
}

module.exports = ConfigurationPlugin
