import identity from 'lodash/identity'

import {getApp, getApps} from '#/main/app/plugins'

function getTool(name) {
  return getApp('administration', name)()
}

function getActions(user, desktopRefresher = {}) {
  // adds default refresher actions
  const refresher = Object.assign({
    add: identity,
    update: identity,
    delete: identity
  }, desktopRefresher)

  // get all actions declared for workspace
  const actions = getApps('actions.administration')

  return Promise.all(
    // boot actions applications
    Object.keys(actions).map(action => actions[action]())
  ).then((loadedActions) => loadedActions
    // generate action
    .map(actionModule => actionModule.default(user, refresher))
  )
}

export {
  getTool,
  getActions
}
