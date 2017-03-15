const path = require('path')
const shell = require('shelljs')

const paths = require('../../paths')
const themeConf = require('./theme')

const OLD_THEMES_PATH = path.resolve(paths.distribution(), 'main/core/Resources/less/themes')
const DEFAULT_THEMES_PATH = path.resolve(paths.distribution(), 'main/core/Resources/themes')

/**
 * Gets a theme from a custom path.
 *
 * @param {string} themePath
 *
 * @return {Theme}
 */
function getThemeFromPath(themePath) {
  if (!shell.test('-e', themePath)) {
    throw new Error(`Theme '${themePath}' not found.`)
  }

  return new themeConf.Theme(path.basename(themePath, '.less'), path.dirname(themePath))
}

/**
 * Gets all the themes installed in the platform.
 *
 * @returns {Theme[]}
 */
function getPlatformThemes() {
  return [].concat(
    getDefaultThemes(),
    getCustomThemes()
  )
}

/**
 * Gets claroline default themes.
 *
 * @returns {Theme[]}
 */
function getDefaultThemes() {
  return shell.ls(DEFAULT_THEMES_PATH).map(theme =>
    new themeConf.Theme(path.basename(theme, '.less'), DEFAULT_THEMES_PATH)
  ).concat(shell.ls(OLD_THEMES_PATH).map(theme =>
    new themeConf.Theme(path.basename(theme, '.less'), OLD_THEMES_PATH)
  ))
}

/**
 * Gets custom themes of the platform.
 *
 * @returns {Theme[]}
 */
function getCustomThemes() {
  return []
}

module.exports = {
  DEFAULT_THEMES_PATH,
  getThemeFromPath,
  getPlatformThemes,
  getDefaultThemes,
  getCustomThemes
}
