import {getActions as getPluginsActions, getDefaultAction as getPluginsDefaultAction} from '#/main/app/plugins'

function getActions(courses, coursesRefresher, path, currentUser, withDefault = false) {
  return getPluginsActions('course', courses, coursesRefresher, path, currentUser, withDefault)
}

function getDefaultAction(courses, coursesRefresher, path, currentUser = null) {
  return getPluginsDefaultAction('course', courses, coursesRefresher, path, currentUser)
}

export {
  getActions,
  getDefaultAction
}
