import {trans} from '#/main/app/intl/translation'
import {getApps} from '#/main/app/plugins'

import {constants} from '#/main/community/constants'

function getActions(users, refresher, path, currentUser, withDefault = false) {
  // get all actions declared for user
  const actions = getApps('actions.user')

  return Promise.all(
    // boot actions applications
    Object.keys(actions).map(action => actions[action]())
  ).then((loadedActions) => loadedActions
    // generate action
    .map(actionModule => actionModule.default(users, refresher, path, currentUser))
    // filter default if needed
    .filter(action => (withDefault || undefined === action.default || !action.default))
  )
}

function displayUsername(user = null) {
  if (user) {
    return user.firstName + ' ' + user.lastName
  }

  return trans('unknown')
}

function getPlatformRoles(roles) {
  return roles.filter(role => constants.ROLE_PLATFORM === role.type).map(role => trans(role.translationKey))
}

export {
  getActions,
  displayUsername,
  getPlatformRoles
}
