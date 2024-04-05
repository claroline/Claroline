import identity from 'lodash/identity'

import {getApp, getApps} from '#/main/app/plugins'

import {constants} from '#/main/core/tool/constants'

function getTools(contextType) {
  if (constants.TOOL_ADMINISTRATION === contextType) {
    return getApps('administration')
  } else if (constants.TOOL_ACCOUNT === contextType) {
    return getApps('account')
  }

  return getApps('tools')
}

async function getTool(name, contextType) {
  if (constants.TOOL_ADMINISTRATION === contextType) {
    return getApp('administration', name)()
  } else if (constants.TOOL_ACCOUNT === contextType) {
    return getApp('account', name)()
  }

  return getApp('tools', name)()
}

function getActions(tool, context, toolRefresher, path, currentUser) {
  // adds default refresher actions
  const refresher = Object.assign({
    add: identity,
    update: identity,
    delete: identity
  }, toolRefresher)

  // get all actions declared for the tool
  const actions = getApps('actions.tool')

  return Promise.all(
    // boot actions applications
    Object.keys(actions).map(action => actions[action]())
  ).then((loadedActions) => loadedActions
    // generate action
    .map(actionModule => actionModule.default(tool, context, refresher, path, currentUser))
  )
}

export {
  getTools,
  getTool,
  getActions
}
