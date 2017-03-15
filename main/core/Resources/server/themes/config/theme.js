const path = require('path')
const shell = require('shelljs')

const paths = require('../../paths')

const DEFAULT_THEMES_PATH = path.resolve(paths.distribution(), 'main/core/Resources/less/themes')

const THEME_ROOT_FILE    = 'index.less'
const THEME_VARS_FILE    = 'variables.less'
const THEME_PLUGINS_FILE = 'variables.plugins.less'

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
  const oldRootFile = path.join(this.location, this.name+'.less') // retro-compatibility : support single file themes
  if (shell.test('-e', oldRootFile)) {
    // It's an old theme
    this.old = true
    this.root = oldRootFile
  } else if (shell.test('-e', rootFile)) {
    // It's a new theme
    this.old = false
    this.root = rootFile
  }

  // Get global variables
  const globalVarsFile = path.join(this.location, this.name, THEME_VARS_FILE)
  if (shell.test('-e', globalVarsFile)) {
    this.globalVars = globalVarsFile
  }

  // Get plugins variables
  const pluginsVarsFile = path.join(this.location, this.name, THEME_PLUGINS_FILE)
  if (shell.test('-e', pluginsVarsFile)) {
    this.pluginsVars = pluginsVarsFile
  }

  // Get static assets
  this.staticAssets = [];
  ['fonts', 'images'].map(assetType => {
    if (shell.test('-e', path.join(this.location, this.name, assetType))) {
      this.staticAssets.push(assetType)
    }
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
    } else if (!this.hasGlobalVars()) {
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
   * Previous format required only a single root file named after the theme (eg. claroline.less).
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
    return this.hasGlobalVars() || this.hasPluginVars()
  },

  /**
   * Checks if theme exposes global variables.
   *
   * @returns {boolean}
   */
  hasGlobalVars() {
    return !!this.globalVars
  },

  /**
   * Checks if theme exposes overrides for plugins variables.
   *
   * @returns {boolean}
   */
  hasPluginVars() {
    return !!this.pluginsVars
  },

  /**
   * Gets the variables of the theme.
   *
   * @returns {Array}
   */
  getVars() {
    const vars = []

    if (this.hasGlobalVars()) {
      vars.push(this.globalVars)
    }

    if (this.hasPluginVars()) {
      vars.push(this.pluginsVars)
    }

    return vars
  },

  /**
   * Gets the global variables of the theme.
   *
   * @returns {Array}
   */
  getGlobalVars() {
    return this.globalVars
  },

  /**
   * Gets the plugins variables of the theme.
   *
   * @returns {Array}
   */
  getPluginsVars() {
    return this.pluginsVars
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
  DEFAULT_THEMES_PATH,
  THEME_ROOT_FILE,
  THEME_VARS_FILE,
  THEME_PLUGINS_FILE,
  Theme
}
