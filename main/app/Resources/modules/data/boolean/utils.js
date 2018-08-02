import isBoolean from 'lodash/isBoolean'

import {t} from '#/main/core/translation'

/**
 * Parses a boolean display string into a real Boolean.
 *
 * It matches :
 *  - real boolean (true/false)
 *  - int boolean (0/1)
 *  - english for yes/no, true/false
 *  - current locale yes/no, true/false
 *
 * @param {boolean|string} value
 * @param {boolean}        silent - if true, does not throw on parse error.
 *
 * @returns {boolean}
 */
function parseBool(value, silent = false) {
  if (isBoolean(value)) {
    return value
  } else if (typeof value === 'string') {
    switch (value.toLowerCase().trim()) {
      case '1':
      case 'true':
      case t('true').toLowerCase().trim():
      case 'yes':
      case t('yes').toLowerCase().trim():
        return true
      case '0':
      case 'false':
      case t('false').toLowerCase().trim():
      case 'no':
      case t('no').toLowerCase().trim():
        return false
    }
  }

  if (!silent) {
    throw new Error('Invalid boolean value.')
  }

  return false
}

/**
 * Translates a boolean value.
 *
 * @param value
 *
 * @returns {string}
 */
function translateBool(value) {
  return value ? t('yes') : t('no')
}

export {
  parseBool,
  translateBool
}
