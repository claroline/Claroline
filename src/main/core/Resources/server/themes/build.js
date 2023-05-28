const crypto = require('crypto')
const fs = require('fs')
const path = require('path')

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
    return compile.optimize(output.css, outputFile).then(optimized => {
      const baseName = path.basename(outputFile, '.css')

      // Write new css file
      fs.writeFileSync(outputFile, '/*# sourceMappingURL=./'+baseName+'.css.map */\n' +optimized)

      // Write new map file
      if (output.sourceMap) {
        fs.writeFileSync(outputFile+'.map', JSON.stringify(output.sourceMap))
      }

      return Promise.resolve(newVersion)
    })
  } else {
    console.info(`    ${outputFile}.`)

    return Promise.resolve(newVersion)
  }
}

/**
 * Recursively copies static files directories (eg. images, fonts)
 * @param {string} src
 * @param {string} themeDir
 * @param {string} assetDir
 */
function copyStatic(src, themeDir, assetDir) {
  const destination = path.join(themeDir, assetDir)
  console.log(`    ${src} => ${destination}`)

  fs.rmSync(destination, { recursive: true, force: true })
  fs.cpSync(src, destination, { recursive: true })
}

/**
 * Dump the current build state into file.
 *
 * @param {object} state
 */
function dumpBuildState(state) {
  console.info('Dump theme build state.')
  fs.writeFileSync(BUILD_FILE, JSON.stringify(state, null, 2))
}

/**
 * Get the build state of the previous build if any.
 *
 * @returns {object}
 */
function getBuildState() {
  try {
    return JSON.parse(fs.readFileSync(BUILD_FILE))
  } catch (err) {
    return {}
  }
}

module.exports = {
  build
}
