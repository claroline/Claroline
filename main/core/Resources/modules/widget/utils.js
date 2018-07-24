import get from 'lodash/get'

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
      styles.background = `url(${asset(display.background.url)}) center center no-repeat`
      break
  }

  return styles
}

export {
  computeStyles
}
