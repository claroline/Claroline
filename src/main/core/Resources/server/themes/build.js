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
 * @param {Theme}   theme   - the themes to build
 * @param {boolean} noCache - if true, all files will be forced recompiled without checking cache
 */
function build(theme, noCache) {
  const previousBuild = getBuildState()

  if (!previousBuild[theme.name] || noCache) {
    previousBuild[theme.name] = {}
  }

  buildTheme(theme, previousBuild[theme.name]).then((results) => {
    previousBuild[theme.name] = results.reduce((acc, result) => Object.assign(acc, result), {})

    dumpBuildState(previousBuild)

    console.log(`[SUCCESS] Rebuild theme "${theme.name}" finished.`)
  })
}

/**
 * Builds a theme.
 *
 * @param {Theme}  theme
 * @param {object} themeState
 */
function buildTheme(theme, themeState) {
  const errors = theme.validate()
  if (0 !== errors.length) {
    errors.forEach(error => console.error(error))
  }

  if (theme.canCompile()) {
    // Create build dir for the theme
    const themeDir = path.join(BUILD_DIR, theme.name)
    if (!fs.existsSync(themeDir)) {
      fs.mkdirSync(themeDir)
    }

    // Copy static assets
    if (theme.hasStaticAssets()) {
      console.log(`Copy static files :`)

      theme.getStaticAssets().map(assetDir =>
        copyStatic(
          // src
          path.resolve(theme.location, theme.name, assetDir),
          // destination
          path.resolve(themeDir),
          // asset dir
          assetDir
        )
      )
    }

    console.log(`Compile styles :`)

    return Promise.all([
      // 1. Build theme root file
      createAsset(
        theme.getRoot(),
        path.resolve(themeDir, 'bootstrap.css'),
        themeState['bootstrap.css']
      ).then(
        newVersion => ({'bootstrap.css' : newVersion})
      ),

      // 2. Build plugins styles using theme vars
      ...registeredPackagesNames.map(packageAssets => createAsset(
        registeredPackages[packageAssets], // src file path
        path.resolve(themeDir, packageAssets+'.css'), // destination file path
        themeState[packageAssets+'.css'],
        theme.getVars() // global vars
      ).then(
        //newVersion => themeState[packageAssets+'.css'] = newVersion
        newVersion => ({[`${packageAssets}.css`] : newVersion})
      ))
    ])
  } else {
    return Promise.reject('Theme is not valid. Compilation aborted.')
  }
}

function createAsset(asset, outputFile, currentVersion, globalVars) {
  const output = compile.compile(asset, outputFile, globalVars)

  // Check if the result has changed since last compilation
  const newVersion = crypto.createHash('md5').update(output.css).digest('hex')

  if (currentVersion !== newVersion || !fs.existsSync(outputFile)) {
    // Content has changed => update the build
    console.info(`+++ ${outputFile}.`)

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
}

/**
 * Recursively copies static files directories (eg. images, fonts)
 * @param {string} src
 * @param {string} destination
 * @param {string} assetDir
 */
function copyStatic(src, destination, assetDir) {
  const pathToRemove = path.join(destination, assetDir)
  console.log(`    ${src} => ${pathToRemove}`)
  shell.rm('-rf', pathToRemove)
  shell.cp('-R', src, destination)
}

/**
 * Dump the current build state into file.
 *
 * @param {object} state
 */
function dumpBuildState(state) {
  shell.echo('Dump theme build state.')
  fs.writeFileSync(BUILD_FILE, JSON.stringify(state, null, 2))
}

/**
 * Get the build state of the previous build if any.
 *
 * @returns {object}
 */
function getBuildState() {
  if (shell.test('-e', BUILD_FILE)) {
    // Themes have already been built once
    return JSON.parse(shell.cat(BUILD_FILE)) || {}
  }

  return {}
}

module.exports = {
  build
}
