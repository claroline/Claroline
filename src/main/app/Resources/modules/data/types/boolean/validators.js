import {tval} from '#/main/app/intl/translation'

import {parseBool} from '#/main/app/data/types/boolean/utils'

function boolean(value) {
  try {
    parseBool(value)
  } catch (e) {
    return tval('This value should be a valid boolean.')
  }
}

export {
  boolean
}
