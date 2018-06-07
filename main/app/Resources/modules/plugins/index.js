import {registry} from '#/main/app/plugins/registry'

/**
 *
 * @param {string} type
 */
function getApps(type) {
  const plugins = registry.all()

  return Object.keys(plugins).reduce((acc, current) => Object.assign({}, acc, plugins[current][type] || {}), {})
}

/**
 *
 * @param {string} type
 * @param {string} name
 *
 * @return {function} - a function that will import the application
 */
function getApp(type, name) {
  const all = getApps(type)

  if (!all[name]) {
    throw new Error(`You have requested a non existent ${type} named ${name}`)
  }

  return all[name]
}

export {
  getApps,
  getApp
}
