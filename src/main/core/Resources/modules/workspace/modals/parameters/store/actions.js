import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'

import {selectors} from '#/main/core/workspace/modals/parameters/store/selectors'

export const actions = {}

actions.get = (workspaceId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_workspace_get', {id: workspaceId}],
    silent: true,
    success: (data) => dispatch(formActions.resetForm(selectors.STORE_NAME, data))
  }
})
