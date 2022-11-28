import {getActions as getPluginsActions, getDefaultAction as getPluginsDefaultAction} from '#/main/app/plugins'

function getActions(workspaces, workspacesRefresher, path, currentUser, withDefault = false) {
  return getPluginsActions('user', workspaces, workspacesRefresher, path, currentUser, withDefault)
}

function getDefaultAction(workspace, workspacesRefresher, path, currentUser = null) {
  return getPluginsDefaultAction('user', workspace, workspacesRefresher, path, currentUser)
}

export {
  getActions,
  getDefaultAction
}
