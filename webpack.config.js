const path = require('path')
const colors = require('colors/safe')
const scriptPath = './vendor/claroline/distribution/main/core/Resources/scripts/lib'
const collectPackages = require(path.resolve(scriptPath, 'collect-packages'))
const buildConfig = require(path.resolve(scriptPath, 'webpack'))

const isWatchMode = process.argv.indexOf('--claro-tgt=watch') !== -1
const config = buildConfig(__dirname, collectPackages(__dirname), isWatchMode)

console.log(colors.yellow(JSON.stringify(config._debug, null, 2) + '\n'))

module.exports = config
