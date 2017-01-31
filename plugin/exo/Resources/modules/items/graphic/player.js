import {SHAPE_RECT} from './enums'

export function findArea(pointer, solutions) {
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
