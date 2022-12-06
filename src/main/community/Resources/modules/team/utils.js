import {getActions as getPluginsActions, getDefaultAction as getPluginsDefaultAction} from '#/main/app/plugins'

function getActions(teams, teamsRefresher, path, currentUser, withDefault = false) {
  return getPluginsActions('team', teams, teamsRefresher, path, currentUser, withDefault)
}

function getDefaultAction(teams, teamsRefresher, path, currentUser = null) {
  return getPluginsDefaultAction('team', teams, teamsRefresher, path, currentUser)
}

export {
  getActions,
  getDefaultAction
}
