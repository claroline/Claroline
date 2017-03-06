const path = require('path')
const shell = require('shelljs')

const DEFAULT_THEMES_PATH = path.resolve(__dirname, '../../../less/themes')

const THEME_ROOT_FILE = 'index.less'
const THEME_VARS_FILE = 'variables.less'

/**
 * Theme instance constructor.
 *
 * @param {string} themeName     - the name of the theme
 * @param {string} themeLocation - the path to the theme (the URI does not contain the theme name part)
 */
const Theme = function (themeName, themeLocation) {
  this.name = themeName
  this.location = themeLocation
}

Theme.prototype = {
  /**
   * Validates theme.
   *
   * @returns {boolean}
   */
  validate() {
    if (this.isOld()) {
      // Check theme version
      shell.echo(`[Deprecated] Consider upgrading your theme format.`)
      shell.echo(`**compilation will be incomplete.**`)

      return true
    } else if (!this.hasRoot()) {
      // Check theme root file
      shell.echo(`[Error] Root file not found. Expected ${this.getRoot()}.`)
      shell.echo(`**compilation aborted.**`)

      return false
    } else if (!this.hasVars()) {
      // Check theme vars
      shell.echo(`[Warning] Variables file not found. Expected ${this.getVars()}.`)
      shell.echo(`[Warning] Cannot compile plugins with correct variables set.`)
      shell.echo(`[Warning] Cannot create JSON map.`)
      shell.echo(`**compilation will be incomplete.**`)

      return true
    }
  },

  /**
   * Checks if theme uses an old format.
   * Previous format required only a single root file named after the theme (eg. claroline.less).
   *
   * @return {bool}
   */
  isOld() {
    return shell.test('-e', this.getOldRoot())
  },

  /**
   * Checks if theme has a root file.
   *
   * @returns {bool}
   */
  hasRoot() {
    return shell.test('-e', this.getRoot())
  },

  /**
   * Gets the standard root file for old theme.
   * For retro-compatibility purpose.
   *
   * @returns {string}
   */
  getOldRoot() {
    return `${path.join(this.location, this.name)}.less`
  },

  /**
   * Gets the root file of theme.
   *
   * @returns {string}
   */
  getRoot() {
    return this.isOld() ? this.getOldRoot() : path.join(this.location, this.name, THEME_ROOT_FILE)
  },

  /**
   * Checks if theme exposes a variables file.
   *
   * @returns {bool}
   */
  hasVars() {
    return shell.test('-e', this.getVars())
  },

  /**
   * Gets the variables file of theme.
   *
   * @returns {string}
   */
  getVars() {
    return path.join(this.location, this.name, THEME_VARS_FILE)
  }
}

module.exports = {
  DEFAULT_THEMES_PATH,
  THEME_ROOT_FILE,
  THEME_VARS_FILE,
  Theme
}
