import {getActions as getPluginsActions, getDefaultAction as getPluginsDefaultAction} from '#/main/app/plugins'

function getActions(groups, groupsRefresher, path, currentUser, withDefault = false) {
  return getPluginsActions('role', groups, groupsRefresher, path, currentUser, withDefault)
}

function getDefaultAction(groups, groupsRefresher, path, currentUser = null) {
  return getPluginsDefaultAction('role', groups, groupsRefresher, path, currentUser)
}

export {
  getActions,
  getDefaultAction
}
