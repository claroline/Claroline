import {API_REQUEST, url} from '#/main/app/api'

import {actions as listActions} from '#/main/app/content/list/store'
import {currentUser} from '#/main/core/user/current'

export const actions = {}

actions.register = (workspaces) => ({
  [API_REQUEST]: {
    url: url(['apiv2_workspace_register', {user: currentUser().id}], {workspaces: workspaces.map(workspace => workspace.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => dispatch(listActions.invalidateData('workspaces'))
  }
})

actions.unregister = (workspaces) => ({
  [API_REQUEST]: {
    url: url(['apiv2_workspace_unregister', {user: currentUser().id}], {workspaces: workspaces.map(workspace => workspace.id)}),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => dispatch(listActions.invalidateData('workspaces'))
  }
})
