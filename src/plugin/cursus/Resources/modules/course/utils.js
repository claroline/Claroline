import identity from 'lodash/identity'

import {getApps} from '#/main/app/plugins'

function getActions(courses, context, courseRefresher, path, currentUser) {
  // adds default refresher actions
  const refresher = Object.assign({
    add: identity,
    update: identity,
    delete: identity
  }, courseRefresher)

  // get all actions declared for workspace
  const actions = getApps('actions.course')

  return Promise.all(
    // boot actions applications
    Object.keys(actions).map(action => actions[action]())
  ).then((loadedActions) => loadedActions
    // generate action
    .map(actionModule => actionModule.default(courses, context, refresher, path, currentUser))
  )
}

export {
  getActions
}
