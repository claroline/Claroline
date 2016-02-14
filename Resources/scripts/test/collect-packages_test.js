const assert = require('assert')
const collectPackages = require('../lib/collect-packages')
const dataRoot = `${__dirname}/data`

describe('collectPackages()', () => {
  it('should err if the root directory doesn\'t exist', () => {
    assert.throws(
      () => collectPackages('does/not/exist'),
      /no such file or directory/
    )
  })

  it('should err if the root is not a directory', () => {
    assert.throws(
      () => collectPackages(`${dataRoot}/invalid/empty/.gitkeep`),
      `${dataRoot}/invalid/empty/.gitkeep is not a directory`
    )
  })

  it('should err if composer/installed.json doesn\'t exist', () => {
    assert.throws(
      () => collectPackages(`${dataRoot}/invalid/empty`),
      'Cannot found package info (composer/installed.json)'
    )
  })

  it('should err if no packages are found in installed.json', () => {
    assert.throws(
      () => collectPackages(`${dataRoot}/invalid/no-packages`),
      'Cannot find packages in composer/installed.json'
    )
  })

  it('should keep only claroline packages', () => {
    assert.equal(collectPackages(`${dataRoot}/valid`).length, 3)
  })

  it('should extract useful attributes and load configs if any', () => {
    const packages = collectPackages(`${dataRoot}/valid`)
    assert.deepEqual(packages, [
      {
        name: 'foo/bar-bundle',
        path: `${dataRoot}/valid/vendor/foo/bar-bundle/Foo/BarBundle`,
        assets: {
          webpack: {
            entry: {
              mod1: 'modules/mod1/main',
              mod2: 'modules/mod2/index'
            }
          }
        }
      },
      {
        name: 'baz/quz-bundle',
        path: `${dataRoot}/valid/vendor/baz/quz-bundle`,
        assets: {
          webpack: {
            entry: {
              fooScript: 'js/foo.js'
            }
          }
        }
      },
      {
        name: 'ext/ext-bundle',
        path: `${dataRoot}/valid/vendor/ext/ext-bundle`,
        assets: false
      },
    ])
  })
})
