const collectPackages = require('./main/core/Resources/scripts/lib/collect-packages')
const buildConfig = require('./main/core/Resources/scripts/lib/webpack')

const rootDir = __dirname + '/../../..'
const webpack = buildConfig(rootDir, collectPackages(rootDir), true)

module.exports = config => {
  config.set({
    basePath: '',
    frameworks: ['mocha'],
    files: [
      '*/*/Resources/**/*test.js'
    ],
    exclude: [
      // tmp excludes (legacy/node tests)
      'main/core/Resources/scripts/test/**/*',
      'main/core/Resources/public/js/tests/**/*',
      'plugin/result/**/*'
    ],
    preprocessors: {
      './*/*/Resources/**/*test.js': ['webpack']
    },
    reporters: ['progress'],
    port: 9876,
    colors: true,
    logLevel: config.LOG_DEBUG,
    client: {
      captureConsole: true,
      mocha: {
        bail: true
      }
    },
    autoWatch: true,
    browsers: ['Chrome'],
    singleRun: false,
    failOnEmptyTestSuite: false,
    concurrency: Infinity,
    webpack,
    webpackServer: {
      quiet: true
    }
  })
}
