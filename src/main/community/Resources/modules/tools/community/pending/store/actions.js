import {API_REQUEST, url} from '#/main/app/api'

import {actions as listActions} from '#/main/app/content/list/store'

import {selectors} from '#/main/community/tools/community/store/selectors'

export const actions = {}

actions.register = (users, workspace) => ({
  [API_REQUEST]: {
    url: url(['apiv2_workspace_registration_validate', {id: workspace.id}]) + '?'+ users.map(user => 'ids[]='+user.id).join('&'),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.users.list'))
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.pending'))
    }
  }
})

actions.remove = (users, workspace) => ({
  [API_REQUEST]: {
    url: url(['apiv2_workspace_registration_remove', {id: workspace.id}]) + '?'+ users.map(user => 'ids[]='+user.id).join('&'),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.users.list'))
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.pending'))
    }
  }
})
