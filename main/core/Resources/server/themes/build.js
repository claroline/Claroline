const path = require('path')
const shell = require('shelljs')

const entries = require('./entries')
const themeConf = require('./config/theme')

const BUILD_DIR = path.resolve(__dirname, '../../../../../../../../web/themes')

// Get styles for installed packages
const registeredPackages = entries.collectEntries()

/**
 * Builds all installed themes.
 */
function run() {
  shell.echo(`Rebuild themes: START`)

  // Build default themes
  shell.ls(themeConf.DEFAULT_THEMES_PATH).forEach(theme => {
    buildTheme(
      new themeConf.Theme(path.basename(theme, '.less'), themeConf.DEFAULT_THEMES_PATH)
    )
  })

  // TODO : rebuild custom themes

  shell.echo(`Rebuild themes: END`)
  shell.echo(`Enjoy your fresh themes !`)
}

/**
 * Builds a theme.
 *
 * @param {Theme} theme
 */
function buildTheme(theme) {
  shell.echo(`- Theme '${theme.name}'`)

  const themeOutput = `${path.join(BUILD_DIR, theme.name)}/bootstrap.css`

  if (theme.validate()) {
    // Build theme file
    createAsset(theme.getRoot(), themeOutput)

    // Build plugins styles
    buildPlugins(theme)
  }
}

function buildPlugins(theme) {
  Object.keys(registeredPackages).forEach(packageAssets => createAsset(
    // src file path
    registeredPackages[packageAssets],
    // destination file path
    `${path.join(BUILD_DIR, theme.name)}/${packageAssets}.css`,
    // global vars
    theme.hasVars() ? theme.getVars() : null
  ))
}

function createAsset(asset, output, globalVars) {
  compileSrcFile(asset, output, globalVars)
}

/**
 * Compiles less files in css
 *
 * @todo disable source-map on prod env
 */
function compileSrcFile(lessFile, cssFile, themeVars) {
  const lessCmd = [
    /*'node_modules/less/bin/lessc',*/
    'lessc',
    '--verbose',
    //'--modify-var=', // override vars with the current theme ones
    '--source-map',
    '--source-map-less-inline', // avoid to have to give access to the original less src files
    `--source-map-basepath=${path.resolve(__dirname, '../../../../../../../../')}`, // fixme
    lessFile,
    cssFile
  ].join(' ')

  shell.exec(lessCmd, code => {
    assertSuccess('Lessc', lessFile, code)

    //optimizeCompiledFile(cssFile)
  })
}

/**
 * Applies some css cleaning
 */
function optimizeCompiledFile(cssFile) {
  const postCmd = [
    'node_modules/postcss-cli/bin/postcss',
    '-u autoprefixer -u cssnano',
    '--autoprefixer.browsers "last 2 versions"',
    '--cssnano.safe true',
    '-o',
    cssFile,
    cssFile
  ].join(' ')

  shell.exec(postCmd, code => {
    assertSuccess('PostCSS', cssFile, code)
  })
}

function assertSuccess(pgm, file, code) {
  if (code !== 0) {
    throw new Error(`${pgm} failed on ${file} with code ${code}`)
  }
}

// Run command
run()
