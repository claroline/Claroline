/* global process */

const CircularDependencyPlugin = require('circular-dependency-plugin')

module.exports = () => new CircularDependencyPlugin({
  // exclude detection of files based on a RegExp
  exclude: /public\/packages|node_modules/,
  failOnError: false,
  // allow import cycles that include an asynchronous import,
  // e.g. via import(/* webpackMode: "weak" */ './file.js')
  // (I don't know if we need it)
  allowAsyncCycles: false,
  // set the current working directory for displaying module paths
  cwd: process.cwd()
})
