import {
  SHAPE_RECT,
  DIR_N,
  DIR_NE,
  DIR_E,
  DIR_SE,
  DIR_S,
  DIR_SW,
  DIR_W,
  DIR_NW
} from './enums'

export function resizeArea(area, resizerPosition, offsetX, offsetY) {
  if (area.shape === SHAPE_RECT) {
    return resizeRect(area, resizerPosition, offsetX, offsetY)
  }

  return resizeCircle(area, resizerPosition, offsetX, offsetY)
}

function resizeRect(area, resizerPosition, offsetX, offsetY) {
  const width = area.coords[1].x - area.coords[0].x
  const height = area.coords[1].y - area.coords[0].y

  switch (resizerPosition) {
    case DIR_N: {
      const y = height - offsetY < 1 ?
        area.coords[1].y - 1 :
        area.coords[0].y + offsetY
      return overrideRect(area, {y}, {})
    }
    case DIR_S: {
      const y = height + offsetY < 1 ?
        area.coords[0].y + 1 :
        area.coords[1].y + offsetY
      return overrideRect(area, {}, {y})
    }
    case DIR_W: {
      const x = width - offsetX < 1 ?
        area.coords[1].x - 1 :
        area.coords[0].x + offsetX
      return overrideRect(area, {x}, {})
    }
    case DIR_E: {
      const x = width + offsetX < 1 ?
        area.coords[0].x + 1 :
        area.coords[1].x + offsetX
      return overrideRect(area, {}, {x})
    }
    case DIR_NW: {
      const y = height - offsetY < 1 ?
        area.coords[1].y - 1 :
        area.coords[0].y + offsetY
      const x = width - offsetX < 1 ?
        area.coords[1].x - 1 :
        area.coords[0].x + offsetX
      return overrideRect(area, {x, y}, {})
    }
    case DIR_NE: {
      const y = height - offsetY < 1 ?
        area.coords[1].y - 1 :
        area.coords[0].y + offsetY
      const x = width + offsetX < 1 ?
        area.coords[0].x + 1 :
        area.coords[1].x + offsetX
      return overrideRect(area, {y}, {x})
    }
    case DIR_SE: {
      const y = height + offsetY < 1 ?
        area.coords[0].y + 1 :
        area.coords[1].y + offsetY
      const x = width + offsetX < 1 ?
        area.coords[0].x + 1 :
        area.coords[1].x + offsetX
      return overrideRect(area, {}, {x, y})
    }
    case DIR_SW: {
      const y = height + offsetY < 1 ?
        area.coords[0].y + 1 :
        area.coords[1].y + offsetY
      const x = width - offsetX < 1 ?
        area.coords[1].x - 1 :
        area.coords[0].x + offsetX
      return overrideRect(area, {x}, {y})
    }
    default:
      throw new Error(`Unknown position ${resizerPosition}`)
  }
}

function resizeCircle(area, resizerPosition, offsetX, offsetY) {
  const shrinkX1 = offsetX < 0 && [DIR_NE, DIR_E, DIR_SE].indexOf(resizerPosition) > -1
  const shrinkX2 = offsetX > 0 && [DIR_NW, DIR_W, DIR_SW].indexOf(resizerPosition) > -1
  const shrinkY1 = offsetY < 0 && [DIR_SW, DIR_S, DIR_SE].indexOf(resizerPosition) > -1
  const shrinkY2 = offsetY > 0 && [DIR_NW, DIR_N, DIR_NE].indexOf(resizerPosition) > -1
  const max = Math.abs(Math.abs(offsetX) > Math.abs(offsetY) ? offsetX : offsetY)
  const radius = (shrinkX1 || shrinkX2 || shrinkY1 || shrinkY2) ?
    area.radius - max :
    area.radius + max

  return Object.assign({}, area, {radius: radius > 0 ? radius : 1})
}

function overrideRect(area, leftTop = {}, rightBottom = {}) {
  return Object.assign({}, area, {
    coords: [
      Object.assign({}, area.coords[0], leftTop),
      Object.assign({}, area.coords[1], rightBottom)
    ]
  })
}
