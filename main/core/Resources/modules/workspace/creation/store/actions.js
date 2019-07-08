import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const LOG_REFRESH = 'LOG_REFRESH'

export const actions = {}

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

actions.load = (file) => ({
  [API_REQUEST]: {
    url: ['apiv2_logger_get', {subdir: 'workspace', name: file}],
    success: (response, dispatch) => {
      dispatch(actions.refresh(response))
    }
  }
})
