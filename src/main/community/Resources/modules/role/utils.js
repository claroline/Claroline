import {getActions as getPluginsActions, getDefaultAction as getPluginsDefaultAction} from '#/main/app/plugins'

function getActions(roles, rolesRefresher, path, currentUser, withDefault = false) {
  return getPluginsActions('role', roles, rolesRefresher, path, currentUser, withDefault)
}

function getDefaultAction(roles, rolesRefresher, path, currentUser = null) {
  return getPluginsDefaultAction('role', roles, rolesRefresher, path, currentUser)
}

export {
  getActions,
  getDefaultAction
}
