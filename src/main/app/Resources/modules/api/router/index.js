/* global Routing */

import invariant from 'invariant'
import isEmpty from 'lodash/isEmpty'
import isString from 'lodash/isString'

/**
 * Generates URL based on symfony exposed route.
 * If a string url is passed argument, it will just return it.
 *
 * @param {string|Array} target
 *
 * @return {string}
 *
 * @internal
 */
function getUrl(target) {
  invariant(target && (isString(target) || Array.isArray(target)), '`target` must be a string or an array')

  if (isString(target)) {
    return target
  }

  return Routing.generate(target[0], target[1] ? target[1] : {}, !!target[2])
}

/**
 * Recursively generates URL query string from object.
 *
 * @param {object} queryParams
 * @param {string} prefix
 *
 * @return {string}
 */
function queryString(queryParams = {}, prefix = null) {
  if (!isEmpty(queryParams)) {
    const params = []

    Object.keys(queryParams).map(p => {
      const paramKey = prefix ? prefix + '[' + p + ']' : p
      const paramValue = queryParams[p]

      let paramString
      if (paramValue && typeof paramValue === 'object') {
        paramString = queryString(paramValue, paramKey)
      } else {
        paramString = encodeURIComponent(paramKey) + '=' + encodeURIComponent(paramValue)
      }

      params.push(paramString)
    })


    if (0 !== params.length) {
      return params.join('&')
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
  const queryStr = queryString(queryParams)

  if (queryStr) {
    return getUrl(target) + '?' + queryStr
  }

  return getUrl(target)
}

export {
  url,
  queryString
}
