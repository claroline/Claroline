import {tval} from '#/main/app/intl/translation'

import {string} from '#/main/core/validation'
import {IPv4} from '#/main/app/data/types/ip/utils'

/**
 * Validates an IP string.
 *   - it MUST contains 4 groups separated by ".".
 *   - each group MUST be a number between 0 and 255 or "*".
 *
 * @param {string} value
 *
 * @return {boolean}
 */
function ip(value) {
  if (string(value)) {
    return string(value)
  }

  if (!IPv4.isValid(value)) {
    return tval('This value should be a valid IPv4.')
  }
}

export {
  ip
}
