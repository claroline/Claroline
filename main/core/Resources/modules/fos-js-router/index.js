/* global Routing */

import invariant from 'invariant'
import isString from 'lodash/isString'

/**
 * Generates URL based on symfony exposed route.
 *
 * @param {string}  route      - the name of the route
 * @param {object}  parameters - the route parameters map
 * @param {boolean} absolute   - Do we want the absolute URL ?
 *
 * @return {string}
 */
function generateUrl(route, parameters = {}, absolute = false) {
  return Routing.generate(route, parameters, absolute)
}

function getUrl(target) {
  invariant(target && (isString(target) || Array.isArray(target)), '`target` should be a string or an array')

  if (isString(target)) {
    return target
  }

  return generateUrl(target[0], target[1] ? target[1] : {}, !!target[2])
}

export {
  generateUrl,
  getUrl
}
