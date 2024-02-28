import {getActions as getPluginsActions, getDefaultAction as getPluginsDefaultAction} from '#/main/app/plugins'

function getActions(presences, presencesRefresher, path, currentUser, withDefault = false) {
  return getPluginsActions('training_presence', presences, presencesRefresher, path, currentUser, withDefault)
}

function getDefaultAction(presences, presencesRefresher, path, currentUser = null) {
  return getPluginsDefaultAction('training_presence', presences, presencesRefresher, path, currentUser)
}

export {
  getActions,
  getDefaultAction
}
