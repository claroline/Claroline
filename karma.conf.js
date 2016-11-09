const webpackConfig = require('./webpack.test')

module.exports = config => {
  const base = {
    basePath: '',
    frameworks: ['mocha'],
    files: [
      {
        pattern: 'main/core/Resources/modules/core-js/index.js',
        watched: false
      },
      '*/*/Resources/**/*test.js'
    ],
    preprocessors: {
      'main/core/Resources/modules/core-js/index.js': ['webpack'],
      './*/*/Resources/**/*test.js': ['webpack']
    },
    reporters: ['progress'],
    port: 9876,
    colors: true,
    logLevel: config.LOG_WARN,
    client: {
      captureConsole: true,
      mocha: {
        bail: true
      }
    },
    customLaunchers: {
      ChromeTravis: {
        base: 'Chrome',
        flags: ['--no-sandbox']
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
  }

  // see https://swizec.com/blog/how-to-run-javascript-tests-in-chrome-on-travis/swizec/6647
  if (process.env.TRAVIS) {
    base.browsers = ['ChromeTravis']
  }

  config.set(base)
}
