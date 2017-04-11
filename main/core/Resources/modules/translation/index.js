/**
 * Get the current application translator.
 * For now it's the one coming from https://github.com/willdurand/BazingaJsTranslationBundle.
 *
 * @returns {Translator}
 */
export function getTranslator() {
  return window.Translator
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
export function trans(key, placeholders = {}, domain = 'message') {
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
export function transChoice(key, count, placeholders = {}, domain = 'message') {
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
  return trans(message, placeholders, 'platform')
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
