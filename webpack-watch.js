const frontendConfig = require('./app/config/frontend.json')
const configure = require('./vendor/claroline/core-bundle/Resources/scripts/lib/webpack.js')

module.exports = configure(frontendConfig, __dirname, true)
