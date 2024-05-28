import isNumber from 'lodash/isNumber'

import {getApps} from '#/main/app/plugins'
import {getActions as getContextActions, getDefaultAction as getContextDefaultAction} from '#/main/app/context/utils'

function getActions(workspaces, workspacesRefresher, path, currentUser, withDefault = false) {
  return getContextActions('workspace', workspaces, workspacesRefresher, path, currentUser, withDefault)
}

function getDefaultAction(workspace, workspacesRefresher, path, currentUser = null) {
  return getContextDefaultAction('workspace', workspace, workspacesRefresher, path, currentUser)
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
