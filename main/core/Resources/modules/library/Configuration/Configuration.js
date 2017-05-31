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

    for (let bundle in this.config) {
      if (this.config.hasOwnProperty(bundle)) {
        actions = union(actions, get(this.config[bundle], 'actions', []).filter(el => {
          return el.type === 'administration_users'
        }))
      }
    }

    return actions
  }

  getWorkspacesAdministrationActions() {
    let actions = []

    for (let bundle in this.config) {
      if (this.config.hasOwnProperty(bundle)) {
        actions = union(actions, get(this.config[bundle], 'actions', []).filter(el => {
          return el.type === 'administration_workspaces'
        }))
      }
    }

    return actions
  }
}

// default actions. Maybe do something cleaner later

function getDefaultConfig(config) {
  for (let bundle in config) {
    if (config.hasOwnProperty(bundle)) {
      setDefaultBundle(config[bundle])
    }
  }

  return config
}

function setDefaultBundle(bundle) {
  if (bundle.actions) {
    bundle.actions.forEach((action, key) => {
      bundle.actions[key] = defaults(action, {
        url: '#',
        icon: 'fa fa-fw fa-cog',
        options: {}
      })
    })
  }
}

export default new Configuration(config)
