import identity from 'lodash/identity'

import {getApps} from '#/main/app/plugins'

function getActions(workspaces, workspacesRefresher, path, currentUser, withDefault = false) {
  // adds default refresher actions
  const refresher = Object.assign({
    add: identity,
    update: identity,
    delete: identity
  }, workspacesRefresher)

  // get all actions declared for workspace
  const actions = getApps('actions.workspace')

  return Promise.all(
    // boot actions applications
    Object.keys(actions).map(action => actions[action]())
  ).then((loadedActions) => loadedActions
    // generate action
    .map(actionModule => actionModule.default(workspaces, refresher, path, currentUser))
    // filter default if needed
    .filter(action => (withDefault || undefined === action.default || !action.default))
  )
}

/**
 * Gets the default action of a workspace.
 *
 * @param {object} workspace
 * @param {object} workspacesRefresher
 * @param {string} path                - the UI path where the workspace is opened
 * @param {object} currentUser         - the authenticated user
 *
 * @return {Promise.<Array>}
 */
function getDefaultAction(workspace, workspacesRefresher, path, currentUser = null) {
  // load all available actions
  return getActions([workspace], workspacesRefresher, path, currentUser, true)
    // only get the default one
    .then(actions => actions.find(action => action.default))
}

export {
  getActions,
  getDefaultAction
}
