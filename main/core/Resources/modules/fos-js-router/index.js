/* global Routing */

export function generateUrl(route, parameters = {}, absolute = false) {
  return Routing.generate(route, parameters, absolute)
}
