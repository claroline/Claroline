import {makeActionCreator} from '#/main/core/utilities/redux'
import {generateUrl} from '#/main/core/fos-js-router'

import {actions as listActions} from '#/main/core/layout/list/actions'
import {getDataQueryString} from '#/main/core/layout/list/utils'

import {REQUEST_SEND} from '#/main/core/api/actions'

export const WORKSPACE_ADD_MANAGER = 'WORKSPACE_ADD_MANAGER'
export const WORKSPACE_REMOVE_MANAGER = 'WORKSPACE_REMOVE_MANAGER'

export const actions = {}

actions.workspaceAddManager = makeActionCreator(WORKSPACE_ADD_MANAGER, 'workspace', 'user')
actions.workspaceRemoveManager =  makeActionCreator(WORKSPACE_REMOVE_MANAGER, 'workspace', 'user')

actions.removeWorkspaces = (workspaces) => ({
  [REQUEST_SEND]: {
    url: generateUrl('api_delete_workspaces') + getDataQueryString(workspaces),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      //do something better
      dispatch(listActions.changePage(0))
      dispatch(listActions.fetchData('workspaces'))
    }
  }
})

actions.copyWorkspaces = (workspaces, isModel = 0) => ({
  [REQUEST_SEND]: {
    url: generateUrl('api_copy_workspaces', {isModel: isModel}) + getDataQueryString(workspaces),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => dispatch(listActions.fetchData('workspaces'))
  }
})

actions.addManager = (workspace, user) => ({
  [REQUEST_SEND]: {
    url: generateUrl('api_add_user_role', {user: user.id, role: getManagerRole(workspace).id}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => dispatch(actions.workspaceAddManager(workspace, user))
  }
})

actions.removeManager = (workspace, user) => ({
  [REQUEST_SEND]: {
    url: generateUrl('api_remove_user_role', {user: user.id, role: getManagerRole(workspace).id}),
    request: {
      method: 'GET'
    },
    success: (data, dispatch) => dispatch(actions.workspaceRemoveManager(workspace, user))
  }
})

const getManagerRole = (workspace) => workspace.roles.find(role => role.name.includes('ROLE_WS_MANAGER'))
