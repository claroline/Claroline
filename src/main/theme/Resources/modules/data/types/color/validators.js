import tinycolor from 'tinycolor2'

import {tval} from '#/main/app/intl/translation'

import {string} from '#/main/app/data/types/validators'

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
