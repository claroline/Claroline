import get from 'lodash/get'

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
      styles.background = `url(${display.background})`
      break
  }

  return styles
}

export {
  computeStyles
}
