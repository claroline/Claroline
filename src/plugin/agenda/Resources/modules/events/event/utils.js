import {getActions as getPluginsActions, getDefaultAction as getPluginsDefaultAction} from '#/main/app/plugins'

function getActions(sessions, sessionsRefresher, path, currentUser, withDefault = false) {
  return getPluginsActions('agenda_event', sessions, sessionsRefresher, path, currentUser, withDefault)
}

function getDefaultAction(sessions, sessionsRefresher, path, currentUser = null) {
  return getPluginsDefaultAction('agenda_event', sessions, sessionsRefresher, path, currentUser)
}

export {
  getActions,
  getDefaultAction
}
