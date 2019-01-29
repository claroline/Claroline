import {tval} from '#/main/app/intl/translation'

import {string} from '#/main/core/validation'
import {IPv4} from '#/main/app/data/types/ip/utils'

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
