/* global document */

import get from 'lodash/get'
import invariant from 'invariant'

/**
 * Exposes the global platform ui configuration.
 *
 * NB. For now it's added in the data set of a DOM tag by Twig.
 */

let config = null

/**
 * Loads configuration object from DOM anchor.
 */
function load() {
  const platformConfig = document.querySelector('#platform-config')

  invariant(platformConfig, 'Can not find platform configuration.')
  invariant(platformConfig.dataset.parameters, 'Can not find platform configuration parameters.')

  config = platformConfig.dataset.parameters
}

/**
 * Gets a platform parameters value.
 *
 * @param {string} [param] - The path of the param to get (if null, the whole config is returned).
 *
 * @return {*} - The param value.
 */
function platformConfig(param) {
  if (!config) {
    load()
  }

  if (!param) {
    return config
  }

  return get(param, config)
}

export {
  platformConfig
}