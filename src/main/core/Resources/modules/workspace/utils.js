import isNumber from 'lodash/isNumber'

import {getApps, getActions as getPluginsActions, getDefaultAction as getPluginsDefaultAction} from '#/main/app/plugins'

function getActions(workspaces, workspacesRefresher, path, currentUser, withDefault = false) {
  return getPluginsActions('workspace', workspaces, workspacesRefresher, path, currentUser, withDefault)
}

function getDefaultAction(workspace, workspacesRefresher, path, currentUser = null) {
  return getPluginsDefaultAction('workspace', workspace, workspacesRefresher, path, currentUser)
}

function getRestrictions(workspace, errors, managed, currentUser) {
  const restrictions = getApps('restrictions.workspace')

  return Promise.all(
    // boot restrictions applications
    Object.keys(restrictions).map(action => restrictions[action]())
  ).then((loadedRestrictions) => loadedRestrictions
    .map(restrictionModule => restrictionModule.default(workspace, errors, managed, currentUser))
    .filter(restriction => undefined === restriction.displayed || restriction.displayed)
    .sort((a, b) => {
      if (isNumber(a.order) && !isNumber(b.order)) {
        return -1
      } else if (!isNumber(a.order) && isNumber(b.order)) {
        return 1
      } else if (isNumber(a.order) && isNumber(b.order)) {
        return a.order - b.order
      }

      return 0
    })
  )
}

export {
  getActions,
  getDefaultAction,
  getRestrictions
}
