/* global window */

const DEFAULT_DOMAIN    = 'message'
const PLATFORM_DOMAIN   = 'platform'
const VALIDATION_DOMAIN = 'validators'

import {Translator as BaseTranslator} from './translator'

/**
 * Get the current application translator.
 *
 * @returns {Translator}
 */
function getTranslator() {
  // we reuse the instance from browser, because it already contains messages loaded from <script>
  return window.Translator/* || BaseTranslator*/
}

/**
 * Exposes standard Translator `trans` function.
 *
 * @param {string} key
 * @param {object} placeholders
 * @param {string} domain
 *
 * @returns {string}
 */
export function trans(key, placeholders = {}, domain = PLATFORM_DOMAIN) {
  return getTranslator().trans(key, placeholders, domain)
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

export function transChoice(key, count, placeholders = {}, domain = PLATFORM_DOMAIN) {
  return getTranslator().transChoice(key, count, placeholders, domain)
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
export function tval(message, placeholders = {}) {
  return trans(message, placeholders, VALIDATION_DOMAIN)
}

// reexport translator object
const Translator = getTranslator()
export {
  DEFAULT_DOMAIN,
  PLATFORM_DOMAIN,
  VALIDATION_DOMAIN,
  Translator
}
