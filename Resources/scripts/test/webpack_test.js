const assert = require('assert')
const buildConfig = require('./../lib/webpack')

describe('buildConfig()', () => {
  it('should prefix entries by bundle short name and full path', () => {
    const packages = [
      {
        name: 'foo/bar-bundle',
        path: '/home/bob/src/foo/bar-bundle',
        assets: {
          webpack: {
            entry: {
              mod1: 'modules/mod1/main'
            }
          }
        }
      },
      {
        name: 'baz/quz-123-bundle',
        path: '/home/bob/src/baz/quz-123-bundle',
        assets: {
          webpack: {
            entry: {
              comp1: 'components/comp1/index',
              comp2: 'components/comp2/main'
            }
          }
        }
      }
    ]
    const config = buildConfig(__dirname, packages)
    assert.deepEqual(config.entry, {
      'foo-bar-mod1': '/home/bob/src/foo/bar-bundle/modules/mod1/main',
      'baz-quz-123-comp1': '/home/bob/src/baz/quz-123-bundle/components/comp1/index',
      'baz-quz-123-comp2': '/home/bob/src/baz/quz-123-bundle/components/comp2/main'
    })
  })
})
