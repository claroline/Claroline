import {API_REQUEST} from '#/main/app/api'

import {actions as contextActions} from '#/main/app/context/store'

export const actions = {}

actions.checkAccessCode = (workspace, code) => (dispatch) => dispatch({
  [API_REQUEST] : {
    url: ['apiv2_workspace_unlock', {id: workspace.id}],
    request: {
      method: 'POST',
      body: JSON.stringify({code: code})
    },
    success: () => dispatch(contextActions.reload())
  }
})

actions.selfRegister = (workspace) => (dispatch) => dispatch({
  [API_REQUEST] : {
    url: ['apiv2_workspace_self_register', {workspace: workspace.id}],
    request: {
      method: 'PUT'
    },
    success: () => dispatch(contextActions.reload())
  }
})
