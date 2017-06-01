const DEFAULT_DOMAIN    = 'message'
const PLATFORM_DOMAIN   = 'platform'
const VALIDATION_DOMAIN = 'validators'

import {execute} from '#/main/core/file-loader'
import {web} from '#/main/core/path'
import {Translator as BaseTranslator} from './translator'

/**
 * Get the current application translator.
 * For now it's the one coming from https://github.com/willdurand/BazingaJsTranslationBundle.
 *
 * @returns {Translator}
 */
export function getTranslator() {
  window.Translator = BaseTranslator

  return BaseTranslator
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
export function trans(key, placeholders = {}, domain = DEFAULT_DOMAIN) {
  if (!isLoaded(key, domain)) {
    execute(web(`js/translations/${domain}/${getLocale()}.js`))
  }

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

export function transChoice(key, count, placeholders = {}, domain = DEFAULT_DOMAIN) {
  if (!isLoaded(key, domain)) {
    execute(web(`js/translations/${domain}/${getLocale()}.js`))
  }

  return getTranslator().transChoice(key, count, placeholders, domain)
}

/**
 * Shortcut to access `platform` messages.
 *
 * @param {string} message
 * @param {object} placeholders
 *
 * @returns {string}
 */
export function t(message, placeholders = {}) {
  return trans(message, placeholders, PLATFORM_DOMAIN)
}

/**
 * Shortcut to access `validators` messages.
 *
 * @param {string} message
 * @param {object} placeholders
 *
 * @returns {string}
 */
export function tval(message, placeholders = {}) {
  return trans(message, placeholders, VALIDATION_DOMAIN)
}

/**
 * Shortcut to access simple translation without placeholders.
 *
 * @param {string} message
 * @param {string} domain
 *
 * @returns {string}
 */
export function tex(message, domain = 'ujm_exo') {
  return trans(message, {}, domain)
}

/**
 * Returns if the translation is loaded for the current locale
 *
 * @returns {boolean}
 */
export function isLoaded(message, domain) {
  return getTranslator().hasMessage(message, domain, getLocale())
}

/**
 * Returns the current locale
 *
 * @returns {string}
 */
export function getLocale() {
  return getTranslator().locale
}

export const ClarolineTranslator = {
  trans: (key, placeholders = {}, domain = 'message') => trans(key, placeholders, domain),
  transChoice: (key, count, placeholders = {}, domain = 'message') => transChoice(key, count, placeholders, domain)
}
