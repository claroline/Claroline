import {trans} from '#/main/app/intl/translation'
import {getApps} from '#/main/app/plugins'

function getActions(users, refresher, withDefault = false) {
  // get all actions declared for user
  const actions = getApps('actions.user')

  return Promise.all(
    // boot actions applications
    Object.keys(actions).map(action => actions[action]())
  ).then((loadedActions) => loadedActions
    // generate action
    .map(actionModule => actionModule.default(users, refresher))
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

export {
  getActions,
  displayUsername
}
