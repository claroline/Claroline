import {getActions as getPluginsActions, getDefaultAction as getPluginsDefaultAction} from '#/main/app/plugins'

function getActions(workspaces, workspacesRefresher, path, currentUser, withDefault = false) {
  return getPluginsActions('workspace', workspaces, workspacesRefresher, path, currentUser, withDefault)
}

function getDefaultAction(workspace, workspacesRefresher, path, currentUser = null) {
  return getPluginsDefaultAction('workspace', workspace, workspacesRefresher, path, currentUser)
}

export {
  getActions,
  getDefaultAction
}
