/* global document */

import get from 'lodash/get'
import invariant from 'invariant'

/**
 * Exposes the global platform parameters.
 *
 * NB. For now it's added in the data set of a DOM tag by Twig.
 */

let parameters = null

/**
 * Loads configuration object from DOM anchor.
 */
function load() {
  const platformConfig = document.querySelector('#platform-config')

  invariant(platformConfig, 'Can not find platform configuration.')
  invariant(platformConfig.dataset.parameters, 'Can not find platform configuration parameters.')

  parameters = JSON.parse(platformConfig.dataset.parameters) || {}
}

/**
 * Gets a platform parameters value.
 *
 * @param {string} [parameterName] - The path of the param to get (if null, the whole config is returned).
 *
 * @return {*} - The param value.
 */
function param(parameterName) {
  if (!parameters) {
    load()
  }

  if (!parameterName) {
    return parameters
  }

  return get(parameters, parameterName)
}

export {
  param
}
