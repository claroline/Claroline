import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const WORKSPACE_CREATION_LOG = 'WORKSPACE_CREATION_LOG'

export const actions = {}

actions.loadCreationLogs =  makeActionCreator(WORKSPACE_CREATION_LOG)

actions.fetchCreationLogs = (file) => ({
  [API_REQUEST]: {
    url: ['apiv2_logger_get', {subdir: 'workspace', name: file}],
    success: (response, dispatch) => {
      dispatch(actions.loadCreationLogs(response))
    }
  }
})
