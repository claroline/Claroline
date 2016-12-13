import config from 'bundle-configs'
import union from 'lodash/union'
import get from 'lodash/get'
import defaults from 'lodash/defaults'

class Configuration {
  constructor(config) {
    // set the default actions here
    this.config = getDefaultConfig(config)
  }

  getConfig() {
    return this.config
  }

  setConfig(config) {
    this.config = getDefaultConfig(config)
  }

  getUsersAdministrationActions() {
    let actions = []

    for (var bundle in this.config) {
      actions = union(actions, get(this.config[bundle], 'actions', []).filter(el => {
        return el.type === 'administration_users'
      }))
    }

    return actions
  }
}

// default actions. Maybe do something cleaner later

function getDefaultConfig(config) {
  for (var bundle in config) {
    setDefaultBundle(config[bundle])
  }

  return config
}

function setDefaultBundle(bundle) {
  if (bundle.actions) {
    bundle.actions.forEach((action, key) => {
      bundle.actions[key] = defaults(action, {href: '#', class: 'fa fa-cog'})
    })
  }
}

export default new Configuration(config)
