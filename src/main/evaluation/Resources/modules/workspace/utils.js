import {getActions as getPluginsActions, getDefaultAction as getPluginsDefaultAction} from '#/main/app/plugins'

function getActions(users, usersRefresher, path, currentUser, withDefault = false) {
  return getPluginsActions('workspace_evaluation', users, usersRefresher, path, currentUser, withDefault)
}

function getDefaultAction(user, usersRefresher, path, currentUser = null) {
  return getPluginsDefaultAction('workspace_evaluation', user, usersRefresher, path, currentUser)
}

export {
  getActions,
  getDefaultAction
}
