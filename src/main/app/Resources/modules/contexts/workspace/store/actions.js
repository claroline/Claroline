import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'
import {selectors as securitySelectors} from '#/main/app/security/store/selectors'

import {actions as contextActions, selectors as contextSelectors} from '#/main/app/context/store'

// actions
export const WORKSPACE_EVALUATION_UPDATE    = 'WORKSPACE_EVALUATION_UPDATE'

// action creators
export const actions = {}

actions.updateEvaluation = makeActionCreator(WORKSPACE_EVALUATION_UPDATE, 'userEvaluation')

actions.reload = () => contextActions.setLoaded(false)

actions.fetchCurrentEvaluation = () => (dispatch, getState) => {
  const currentUser = securitySelectors.currentUser(getState())
  if (!currentUser) {
    // we don't have evaluation for anonymous users
    return
  }

  const workspace = contextSelectors.data(getState())
  if (!workspace) {
    // this method is used when a resource evaluation is updated
    // there may be no workspace if the resource is played on the desktop
    return
  }

  return dispatch({
    [API_REQUEST] : {
      silent: true,
      url: ['apiv2_workspace_evaluation_get', {workspace: workspace.id, user: currentUser.id}],
      success: (data) => dispatch(actions.updateEvaluation(data))
    }
  })
}

actions.checkAccessCode = (workspace, code) => (dispatch) => dispatch({
  [API_REQUEST] : {
    url: ['claro_workspace_unlock', {id: workspace.id}],
    request: {
      method: 'POST',
      body: JSON.stringify({code: code})
    },
    success: () => dispatch(actions.reload(workspace))
  }
})

actions.selfRegister = (workspace) => (dispatch) => dispatch({
  [API_REQUEST] : {
    url: ['apiv2_workspace_self_register', {workspace: workspace.id}],
    request: {
      method: 'PUT'
    },
    success: () => dispatch(actions.reload(workspace))
  }
})
