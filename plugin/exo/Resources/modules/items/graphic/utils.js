
import {SHAPE_RECT} from '#/plugin/exo/items/graphic/constants'

function findArea(pointer, solutions) {
  return solutions.find(solution => {
    if (solution.area.shape === SHAPE_RECT)  {
      return (
        (pointer.x >= solution.area.coords[0].x) &&
        (pointer.x <= solution.area.coords[1].x) &&
        (pointer.y >= solution.area.coords[0].y) &&
        (pointer.y <= solution.area.coords[1].y)
      )
    }

    // coordinates relative to the circle center
    const x = Math.abs(solution.area.center.x - pointer.x)
    const y = Math.abs(solution.area.center.y - pointer.y)
    const r = solution.area.radius

    // inside the circle if distance to center <= radius
    return x * x + y * y <= r * r
  })
}

function isPointInArea(area, x, y) {
  if (area.shape !== 'circle') {
    return x >= area.coords[0].x &&
      x <= area.coords[1].x &&
      y >= area.coords[0].y &&
      y <= area.coords[1].y
  } else {
    const size = area.radius * 2
    // must be circle
    const r = size / 2

    // coordinates relative to the circle center
    x = Math.abs(area.center.x - x)
    y = Math.abs(area.center.y - y)

    // inside the circle if distance to center <= radius
    return x * x + y * y <= r * r
  }
}

export const utils = {
  findArea,
  isPointInArea
}
