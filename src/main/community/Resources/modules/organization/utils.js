import {getActions as getPluginsActions, getDefaultAction as getPluginsDefaultAction} from '#/main/app/plugins'

function getActions(organizations, organizationsRefresher, path, currentUser, withDefault = false) {
  return getPluginsActions('organization', organizations, organizationsRefresher, path, currentUser, withDefault)
}

function getDefaultAction(organizations, organizationsRefresher, path, currentUser = null) {
  return getPluginsDefaultAction('organization', organizations, organizationsRefresher, path, currentUser)
}

export {
  getActions,
  getDefaultAction
}
