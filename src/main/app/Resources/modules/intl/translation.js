/* global window */

const DEFAULT_DOMAIN    = 'platform'

// We reuse BazingaJsTranslation Translator object which has been loaded through another script tag in index.html
// (Translator is not bundled by webpack)

/**
 * Exposes standard Translator `trans` function.
 *
 * @param {string} key
 * @param {object} placeholders
 * @param {string} domain
 *
 * @returns {string}
 */
function trans(key, placeholders = {}, domain = DEFAULT_DOMAIN) {
  return window.Translator.trans(key, placeholders, domain)
}

/**
 * Exposes standard Translator `transChoice` function.
 *
 * @param {string} key
 * @param {number} count
 * @param {object} placeholders
 * @param {string} domain
 *
 * @returns {string}
 */
function transChoice(key, count, placeholders = {}, domain= DEFAULT_DOMAIN) {
  return window.Translator.transChoice(key, count, placeholders, domain)
}

/**
 * Shortcut to access `validators` messages.
 *
 * @param {string} message
 * @param {object} placeholders
 *
 * @returns {string}
 *
 * @deprecated use trans(key, placeholders, 'validators')
 */
function tval(message, placeholders = {}) {
  return trans(message, placeholders, 'validators')
}

// reexport translator object
//const Translator = getTranslator()
export {
  //Translator,

  trans,
  transChoice,
  tval
}
