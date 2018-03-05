const paths = require('./main/core/Resources/server/paths')
const webpackConfig = require('./webpack.config.test')

module.exports = config => {
  const base = {
    basePath: '',
    frameworks: ['mocha', 'sinon'],
    files: [
      '*/*/Resources/modules/**/*\.test.js'
    ],
    preprocessors: {
      './*/*/Resources/modules/**/[^.]+.js': ['coverage'],
      './*/*/Resources/modules/**/*.js': ['webpack']
    },
    reporters: ['dots'],
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
    coverageReporter: {
      includeAllSources: true,
      reporters:[
        {
          type: 'html',
          dir: `${paths.root()}/coverage`
        }
      ]
    },
    autoWatch: true,
    browsers: ['Chrome'],
    singleRun: false,
    failOnEmptyTestSuite: false,
    concurrency: Infinity,
    webpack: webpackConfig,
    webpackMiddleware: {
      stats: 'errors-only'
    }
  }

  // see https://swizec.com/blog/how-to-run-javascript-tests-in-chrome-on-travis/swizec/6647
  if (process.env.TRAVIS) {
    base.browsers = ['ChromeTravis']
  }

  config.set(base)
}
