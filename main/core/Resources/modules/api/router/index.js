/* global Routing */

import invariant from 'invariant'
import isEmpty from 'lodash/isEmpty'
import isString from 'lodash/isString'

/**
 * Generates URL based on symfony exposed route.
 *
 * @param {string}  route      - the name of the route
 * @param {object}  parameters - the route parameters map
 * @param {boolean} absolute   - Do we want the absolute URL ?
 *
 * @return {string}
 *
 * @deprecated use `url()` instead
 */
function generateUrl(route, parameters = {}, absolute = false) {
  return Routing.generate(route, parameters, absolute)
}

/**
 *
 * @param target
 *
 * @return {string}
 *
 * @internal
 * @deprecated use `url()` instead
 */
function getUrl(target) {
  invariant(target && (isString(target) || Array.isArray(target)), '`target` should be a string or an array')

  if (isString(target)) {
    return target
  }

  return generateUrl(target[0], target[1] ? target[1] : {}, !!target[2])
}

/**
 * Recursively generates URL query string from object.
 *
 * @param {object} queryParams
 * @param {string} prefix
 *
 * @return {string}
 */
function getQueryString(queryParams = {}, prefix = null) {
  if (!isEmpty(queryParams)) {
    const params = []

    Object.keys(queryParams).map(p => {
      const paramKey = prefix ? prefix + '[' + p + ']' : p
      const paramValue = queryParams[p]

      let paramString
      if (paramValue && typeof paramValue === 'object') {
        paramString = getQueryString(paramValue, paramKey)
      } else {
        paramString = encodeURIComponent(paramKey) + '=' + encodeURIComponent(paramValue)
      }

      params.push(paramString)
    })


    if (0 !== params.length) {
      return '?' + params.join('&')
    }
  }

  return ''
}

/**
 * Generates URL based on symfony exposed route.
 *
 * @param {string|array} target      - the api target (either a URL string or a symfony route array)
 * @param {object}       queryParams - the list of params to append to query string.
 *
 * @return {string}
 */
function url(target, queryParams = {}) {
  return getUrl(target) + getQueryString(queryParams)
}

export {
  url,
  generateUrl,
  getUrl
}
