import get from 'lodash/get'

import {asset} from '#/main/app/config/asset'

function computeStyles(widget) {
  const styles = {}
  const display = get(widget, 'display') || {}

  if (display.minHeight) {
    styles.minHeight = display.minHeight
  }

  if (display.borderColor) {
    styles.border = '1px sold '+display.borderColor
  }

  if (display.backgroundColor) {
    styles.backgroundColor = display.backgroundColor
  }

  if (display.backgroundUrl) {
    styles.backgroundImage = `url(${asset(display.backgroundUrl)})`
    styles.backgroundRepeat = 'no-repeat'
    styles.backgroundPosition = 'center center'
  }

  if (display.boxShadow) {
    styles.boxShadow = display.boxShadow
  }

  if (display.borderRadius) {
    styles.borderRadius = display.borderRadius
  }

  if (display.textColor) {
    styles.color = display.textColor
  }

  return styles
}
function computeTitleStyles(widget) {
  const styles = {}
  const display = get(widget, 'display') || {}

  if (display.borderColor) {
    styles.background = display.borderColor
  }

  if (display.titleColor) {
    styles.color = display.titleColor
  }

  if (display.maxContentWidth) {
    styles.maxWidth = display.maxContentWidth
  }

  return styles
}

function computeBodyStyles(widget) {
  const styles = {}
  const display = get(widget, 'display') || {}

  if (display.maxContentWidth) {
    styles.maxWidth = display.maxContentWidth
  }

  return styles
}

export {
  computeStyles,
  computeTitleStyles,
  computeBodyStyles
}
