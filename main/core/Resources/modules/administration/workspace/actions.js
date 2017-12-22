import {makeActionCreator} from '#/main/core/utilities/redux'
import {generateUrl} from '#/main/core/fos-js-router'

import {actions as listActions} from '#/main/core/data/list/actions'
import {getDataQueryString} from '#/main/core/data/list/utils'

import {API_REQUEST} from '#/main/core/api/actions'

export const WORKSPACE_ADD_MANAGER = 'WORKSPACE_ADD_MANAGER'
export const WORKSPACE_REMOVE_MANAGER = 'WORKSPACE_REMOVE_MANAGER'

export const actions = {}

actions.workspaceAddManager = makeActionCreator(WORKSPACE_ADD_MANAGER, 'workspace', 'user')
actions.workspaceRemoveManager =  makeActionCreator(WORKSPACE_REMOVE_MANAGER, 'workspace', 'user')

actions.copyWorkspaces = (workspaces, isModel = 0) => ({
  [API_REQUEST]: {
    url: generateUrl('api_copy_workspaces', {isModel: isModel}) + getDataQueryString(workspaces),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => dispatch(listActions.fetchData('workspaces'))
  }
})

actions.addManager = (workspace, user) => ({
  [API_REQUEST]: {
    url: ['api_add_user_role', {user: user.id, role: getManagerRole(workspace).id}],
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => dispatch(actions.workspaceAddManager(workspace, user))
  }
})

actions.removeManager = (workspace, user) => ({
  [API_REQUEST]: {
    url: ['api_remove_user_role', {user: user.id, role: getManagerRole(workspace).id}],
    request: {
      method: 'GET'
    },
    success: (data, dispatch) => dispatch(actions.workspaceRemoveManager(workspace, user))
  }
})

const getManagerRole = (workspace) => workspace.roles.find(role => role.name.includes('ROLE_WS_MANAGER'))
