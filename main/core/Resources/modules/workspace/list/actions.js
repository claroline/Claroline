import {url} from '#/main/app/api'
import {API_REQUEST} from '#/main/app/api'

import {actions as listActions} from '#/main/core/data/list/actions'
import {currentUser} from '#/main/core/user/current'
export const actions = {}

actions.register = (workspace) => ({
  [API_REQUEST]: {
    url: url(['apiv2_workspace_register', {workspace: workspace.uuid, user: currentUser().id}]),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => dispatch(listActions.invalidateData('workspaces'))
  }
})

actions.unregister = (workspace) => ({
  [API_REQUEST]: {
    url: url(['apiv2_workspace_unregister', {workspace: workspace.uuid, user: currentUser().id}]),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => dispatch(listActions.invalidateData('workspaces'))
  }
})
