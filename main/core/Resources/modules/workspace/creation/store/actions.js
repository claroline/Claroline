import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const LOAD_MODEL = 'LOAD_MODEL'
export const LOAD_CURRENT = 'LOAD_CURRENT'

export const actions = {}

actions.loadModel = makeActionCreator(LOAD_MODEL, 'data')
actions.loadCurrent = makeActionCreator(LOAD_CURRENT, 'data')

actions.fetchModel = (model) => ({
  [API_REQUEST]: {
    url: ['apiv2_workspace_get', {id: model}],
    request: {
      method: 'GET'
    },
    success: (response, dispatch) => {
      dispatch(actions.loadModel(response))
    }
  }
})

//récupérer l'action save du formulaire à la place
actions.save = (workspace) => ({
  [API_REQUEST]: {
    url: ['apiv2_workspace_create'],
    request: {
      body: JSON.stringify(workspace),
      method: 'POST'
    }
  }
})

actions.copyBase = (modelId, data) => ({
  [API_REQUEST]: {
    url: ['apiv2_workspace_copy_base', {workspace: modelId}],
    request: {
      body: JSON.stringify(data),
      method: 'POST'
    },
    success: (response, dispatch) => {
      dispatch(actions.loadCurrent(response))
    }
  }
})
