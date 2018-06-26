import {url} from '#/main/app/api'

import {API_REQUEST} from '#/main/app/api'
import {actions as listActions} from '#/main/core/data/list/actions'

export const actions = {}

actions.register = (users, workspace) => ({
  [API_REQUEST]: {
    url: url(['apiv2_workspace_registration_validate', {id: workspace.uuid}]) + '?'+ users.map(user => 'ids[]='+user.id).join('&'),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('users.list'))
      dispatch(listActions.invalidateData('pending.list'))
    }
  }
})

actions.remove = (users, workspace) => ({
  [API_REQUEST]: {
    url: url(['apiv2_workspace_registration_remove', {id: workspace.uuid}]) + '?'+ users.map(user => 'ids[]='+user.id).join('&'),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('users.list'))
      dispatch(listActions.invalidateData('pending.list'))
    }
  }
})
