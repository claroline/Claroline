import get from 'lodash/get'

import {param} from '#/main/app/config'
import {registry} from '#/main/app/plugins/registry'

/**
 * Get all the apps declared in plugins.
 *
 * @param {string}  type
 * @param {boolean} onlyEnabled
 */
function getApps(type, onlyEnabled = true) {
  const plugins = registry.all()

  const list = onlyEnabled ? param('plugins') : Object.keys(plugins)

  return list.reduce((acc, current) => Object.assign({}, acc, get(plugins[current], type) || {}), {})
}

/**
 *
 * @param {string} type
 * @param {string} name
 *
 * @return {function} - a function that will import the application
 */
function getApp(type, name) {
  const all = getApps(type, false)

  if (!all[name]) {
    throw new Error(`You have requested a non existent ${type} named ${name}`)
  }

  return all[name]
}

function isAppEnabled(type, name) {
  // check if the app exists
  const all = getApps(type, false)
  if (!all[name]) {
    throw new Error(`You have requested a non existent ${type} named ${name}`)
  }

  // check if the app is enabled
  const enabled = getApps(type)

  return !!enabled[name]
}

export {
  getApps,
  getApp,
  isAppEnabled
}
