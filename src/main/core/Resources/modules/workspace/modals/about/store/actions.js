import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const LOAD_WORKSPACE_ABOUT = 'LOAD_WORKSPACE_ABOUT'

export const actions = {}

actions.load = makeActionCreator(LOAD_WORKSPACE_ABOUT, 'version', 'changelogs')

actions.get = (workspaceId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_workspace_get', {id: workspaceId}],
    silent: true,
    success: (data) => dispatch(actions.load(data))
  }
})
