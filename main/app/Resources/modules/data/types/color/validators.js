import tinycolor from 'tinycolor2'

import {tval} from '#/main/app/intl/translation'

import {string} from '#/main/core/validation'

function color(value) {
  if (string(value)) {
    return string(value)
  }

  const colorObj = tinycolor(value)
  if (!colorObj.isValid()) {
    return tval('This value should be a valid color.')
  }
}

export {
  color
}
