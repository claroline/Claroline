import {getActions as getPluginsActions, getDefaultAction as getPluginsDefaultAction} from '#/main/app/plugins'

function getActions(users, usersRefresher, path, currentUser, withDefault = false) {
  return getPluginsActions('user', users, usersRefresher, path, currentUser, withDefault)
}

function getDefaultAction(user, usersRefresher, path, currentUser = null) {
  return getPluginsDefaultAction('user', user, usersRefresher, path, currentUser)
}

export {
  getActions,
  getDefaultAction
}
