const frontendConfig = require('./app/config/frontend.json')
const configure = require('./vendor/claroline/core-bundle/Resources/scripts/webpack-config.js')

module.exports = configure(frontendConfig, __dirname)
