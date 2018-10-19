import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'

export const LOAD_MODEL = 'LOAD_MODEL'
export const LOAD_CURRENT = 'LOAD_CURRENT'
export const LOG_REFRESH = 'LOG_REFRESH'

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
    },
    success: (response) => {
      const route = url(['claro_workspace_open', {workspaceId: response.id}])
      window.location.href =  route
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

// logs
actions.refresh = makeActionCreator(LOG_REFRESH, 'content')
actions.reset =  makeActionCreator(LOG_REFRESH)

actions.load = (file) => {
  return {
    [API_REQUEST]: {
      url: ['apiv2_logger_get', {subdir: 'workspace', name: file}],
      success: (response, dispatch) => {
        dispatch(actions.refresh(response))
      }
    }
  }
}