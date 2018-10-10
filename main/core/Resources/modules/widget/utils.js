import get from 'lodash/get'

import {asset} from '#/main/core/scaffolding/asset'

function computeStyles(widget) {
  const styles = {}
  const display = get(widget, 'display') || {}

  if (display.borderColor) {
    styles.borderColor = display.borderColor
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
function computeTitleStyles(widget) {
  const styles = {}
  const display = get(widget, 'display') || {}

  if (display.borderColor) {
    styles.background = display.borderColor
  }

  if (display.color) {
    styles.color = display.color
  }

  return styles
}

export {
  computeStyles,
  computeTitleStyles
}
