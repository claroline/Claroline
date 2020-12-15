import identity from 'lodash/identity'

import {getApps} from '#/main/app/plugins'

function getActions(user, desktopRefresher = {}) {
  // adds default refresher actions
  const refresher = Object.assign({
    add: identity,
    update: identity,
    delete: identity
  }, desktopRefresher)

  // get all actions declared for workspace
  const actions = getApps('actions.desktop')

  return Promise.all(
    // boot actions applications
    Object.keys(actions).map(action => actions[action]())
  ).then((loadedActions) => loadedActions
    // generate action
    .map(actionModule => actionModule.default(user, refresher))
  )
}

export {
  getActions
}
