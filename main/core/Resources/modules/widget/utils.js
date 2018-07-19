import get from 'lodash/get'
import isObject from 'lodash/isObject'

import {asset} from '#/main/core/scaffolding/asset'

function computeStyles(widget) {
  const styles = {}
  const display = get(widget, 'display') || {}

  if (display.color) {
    styles.color = display.color
  }

  switch (display.backgroundType) {
    case 'none':
      styles.background = 'none'
      break
    case 'color':
      styles.background = display.background
      break
    case 'image':
      if (isObject(display.background)){
        styles.background = `url(${asset(display.background.url)}) center center no-repeat`
      } else {
        styles.background = `url(${asset(display.background)}) center center no-repeat`
      }
      break
  }

  return styles
}

export {
  computeStyles
}
