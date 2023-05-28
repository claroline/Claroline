const fs = require('fs')
const path = require('path')

const THEME_ROOT_FILE    = 'index.scss'
const THEME_VARS_FILE    = '_variables.scss'

/**
 * Theme instance constructor.
 *
 * @param {string} themeName     - the name of the theme
 * @param {string} themeLocation - the path to the theme (the URI does not contain the theme name part)
 */
const Theme = function (themeName, themeLocation) {
  this.name = themeName
  this.location = themeLocation

  // Get root file of the theme
  const rootFile = path.join(this.location, this.name, THEME_ROOT_FILE)
  const oldRootFile = path.join(this.location, this.name+'.scss') // retro-compatibility : support single file themes

  try {
    fs.accessSync(oldRootFile, fs.constants.F_OK);

    // It's an old theme
    this.old = true
    this.root = oldRootFile
  } catch (err) {
    try {
      fs.accessSync(rootFile, fs.constants.F_OK);

      // It's a new theme
      this.old = false
      this.root = rootFile
    } catch (err) {
      this.root = null
    }
  }

  // Get global variables
  const globalVarsFile = path.join(this.location, this.name, THEME_VARS_FILE)
  try {
    fs.accessSync(globalVarsFile, fs.constants.F_OK);
    this.globalVars = globalVarsFile
  } catch (err) {
    this.globalVars = null
  }

  // Get static assets
  this.staticAssets = [];
  ['fonts', 'images'].map(assetType => {
    try {
      fs.accessSync(path.join(this.location, this.name, assetType), fs.constants.F_OK);
      this.staticAssets.push(assetType)
    } catch (err) {}
  })
}

Theme.prototype = {
  /**
   * Validates theme.
   *
   * @returns {boolean}
   *
   * @return {Array}
   */
  validate() {
    const errors = []

    if (this.isOld()) {
      // Check theme version
      errors.push('[Deprecated] Consider upgrading your theme format (theme will work but styles will be incomplete).')
    } else if (!this.hasRoot()) {
      // Check theme root file
      errors.push(`[Error] Root file not found. Expected '${THEME_ROOT_FILE}' (theme CAN NOT BE COMPILED).`)
    } else if (!this.hasVars()) {
      // Check theme vars
      errors.push(`[Warning] Variables file not found. Expected '${THEME_VARS_FILE}' (theme will work but styles may be incomplete).`)
    }

    return errors
  },

  /**
   * Checks is the theme can be compiled
   */
  canCompile() {
    return this.hasRoot()
  },

  /**
   * Checks if theme uses an old format.
   * Previous format required only a single root file named after the theme (eg. claroline.scss).
   *
   * @return {boolean}
   */
  isOld() {
    return this.old
  },

  /**
   * Checks if theme has a root file.
   *
   * @returns {boolean}
   */
  hasRoot() {
    return !!this.root
  },

  /**
   * Gets the root file of theme.
   *
   * @returns {string}
   */
  getRoot() {
    return this.root
  },

  /**
   * Checks if theme exposes variables.
   *
   * @returns {bool}
   */
  hasVars() {
    return !!this.globalVars
  },

  /**
   * Gets the variables of the theme.
   *
   * @returns {Array}
   */
  getVars() {
    const vars = []

    if (this.hasVars()) {
      vars.push(this.globalVars)
    }

    return vars
  },

  /**
   * Checks if theme as some static assets (eg. fonts, images).
   *
   * @returns {boolean}
   */
  hasStaticAssets() {
    return !!this.staticAssets
  },

  /**
   * Gets the static assets of the themes (eg. fonts, images).
   *
   * @returns {Array}
   */
  getStaticAssets() {
    return this.staticAssets
  }
}

module.exports = {
  THEME_ROOT_FILE,
  THEME_VARS_FILE,
  Theme
}
