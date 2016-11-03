const webpackConfig = require('./webpack.test')

module.exports = config => {
  config.set({
    basePath: '',
    frameworks: ['mocha'],
    files: ['*/*/Resources/**/*test.js'],
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
    webpack: webpackConfig,
    webpackServer: {
      quiet: true
    }
  })
}
