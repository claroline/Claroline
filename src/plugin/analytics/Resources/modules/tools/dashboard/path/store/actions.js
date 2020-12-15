import {makeActionCreator} from '#/main/app/store/actions'

import {API_REQUEST} from '#/main/app/api'

const PATHS_DATA_LOAD = 'PATHS_DATA_LOAD'

const actions = {}

actions.loadPathsData = makeActionCreator(PATHS_DATA_LOAD, 'tracking')

actions.fetchPathsData = (workspaceId) => ({
  [API_REQUEST]: {
    url: ['claroline_paths_trackings_fetch', {workspace: workspaceId}],
    request: {
      method: 'GET'
    },
    success: (data, dispatch) => {
      dispatch(actions.loadPathsData(data))
    }
  }
})

export {
  actions,
  PATHS_DATA_LOAD
}