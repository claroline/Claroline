const crypto = require('crypto')
const fs = require('fs')
const path = require('path')
const shell = require('shelljs')

const paths = require('../paths')
const entries = require('./entries')
const compile = require('./compile')

const BUILD_DIR = path.resolve(paths.web(), 'themes')
const BUILD_FILE = path.resolve(paths.root(), 'theme-assets.json')

// Get styles for installed packages
const registeredPackages = entries.collectEntries()
const registeredPackagesNames = Object.keys(registeredPackages)

/**
 * Builds themes.
 *
 * @param {Theme[]} themes  - the themes to build
 * @param {boolean} noCache - if true, all files will be forced recompiled without checking cache
 */
function build(themes, noCache) {
  const previousBuild = getBuildState()

  if (!fs.existsSync(BUILD_DIR)) {
    fs.mkdirSync(BUILD_DIR)
  }

  Promise.all(
    themes.map(theme => {
      if (!previousBuild[theme.name] || noCache) {
        previousBuild[theme.name] = {}
      }

      return buildTheme(theme, previousBuild[theme.name])
    })
  ).then(() => {
    dumpBuildState(previousBuild)

    shell.echo('Rebuild themes: END')
    shell.echo('Enjoy your fresh themes !')
  })
}

/**
 * Builds a theme.
 *
 * @param {Theme}  theme
 * @param {object} themeState
 */
function buildTheme(theme, themeState) {
  shell.echo('')
  shell.echo('---------------------------------')
  shell.echo(`| Theme: ${theme.name}`)
  shell.echo('---------------------------------')
  shell.echo('')

  const errors = theme.validate()
  if (0 !== errors.length) {
    shell.echo('ATTENTION:')
    errors.forEach(error => shell.echo('  - ' + error))
  }

  if (theme.canCompile()) {
    // Create build dir for the theme
    const themeDir = path.join(BUILD_DIR, theme.name)
    if (!fs.existsSync(themeDir)) {
      fs.mkdirSync(themeDir)
    }

    return Promise.all([
      // 1. Build theme root file
      createAsset(
        theme.getRoot(),
        path.resolve(themeDir, 'bootstrap.css'),
        themeState['bootstrap.css']
      ).then(
        newVersion => themeState['bootstrap.css'] = newVersion
      ),

      // 2. Build plugins styles using theme vars
      ...registeredPackagesNames.map(packageAssets => createAsset(
        registeredPackages[packageAssets], // src file path
        path.resolve(themeDir, packageAssets+'.css'), // destination file path
        themeState[packageAssets+'.css'],
        theme.getVars() // global vars
      ).then(
        newVersion => themeState[packageAssets+'.css'] = newVersion)
      )
    ]).then(
      result => onThemeSuccess(theme, result),
      reject => onThemeError(theme, reject)
    )
  } else {
    return Promise.reject('Theme is not valid. Compilation aborted.')
  }
}

function onThemeSuccess(theme, result) {
  shell.echo(`[SUCCESS] Theme '${theme.name}' has been successfully updated.`)

  return result
}

function onThemeError(theme, reject) {
  shell.echo(`[ERROR] Theme '${theme.name}' has not been updated.`)
  shell.echo(reject)

  return reject
}

function createAsset(asset, outputFile, currentVersion, globalVars) {
  return compile.compile(asset, outputFile, globalVars).then(output => {
    // Check if the result has change since last compilation
    const newVersion = crypto.createHash('md5').update(output.css).digest('hex')

    if (currentVersion !== newVersion || !fs.existsSync(outputFile)) {
      // Content has changed => update the build
      shell.echo(`+++ ${outputFile}.`)

      // Post process
      return compile.optimize(output.css).then(optimized => {
        // Write new css file
        fs.writeFileSync(outputFile, optimized)

        // Write new map file
        if (output.map) {
          fs.writeFileSync(outputFile + '.map', output.map)
        }

        return Promise.resolve(newVersion)
      })
    } else {
      shell.echo(`    ${outputFile}.`)

      return Promise.resolve(newVersion)
    }
  })
}

/**
 * Dump the current build state into file.
 *
 * @param {object} state
 */
function dumpBuildState(state) {
  shell.echo('Dump current build state: ')
  shell.echo(state)

  fs.writeFileSync(BUILD_FILE, JSON.stringify(state, null, 2))
}

/**
 * Get the build state of the previous build if any.
 *
 * @returns {object}
 */
function getBuildState() {
  shell.echo('Retrieving previous build state...')
  if (shell.test('-e', BUILD_FILE)) {
    // Themes have already been built once
    shell.echo('Build state FOUND.')

    return JSON.parse(shell.cat(BUILD_FILE)) || {}
  } else {
    shell.echo('Build state NOT FOUND.')

    return {}
  }
}

module.exports = {
  build
}
