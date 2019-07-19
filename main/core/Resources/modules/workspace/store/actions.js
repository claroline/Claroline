import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

import {selectors} from '#/main/core/workspace/store/selectors'

// actions
export const WORKSPACE_LOAD                 = 'WORKSPACE_LOAD'
export const WORKSPACE_SET_LOADED           = 'WORKSPACE_SET_LOADED'
export const WORKSPACE_SERVER_ERRORS        = 'WORKSPACE_SERVER_ERRORS'
export const WORKSPACE_RESTRICTIONS_ERROR   = 'WORKSPACE_RESTRICTIONS_ERROR'
export const WORKSPACE_RESTRICTIONS_DISMISS = 'WORKSPACE_RESTRICTIONS_DISMISS'

// action creators
export const actions = {}

actions.load = makeActionCreator(WORKSPACE_LOAD, 'workspaceData')
actions.setLoaded = makeActionCreator(WORKSPACE_SET_LOADED, 'loaded')
actions.setRestrictionsError = makeActionCreator(WORKSPACE_RESTRICTIONS_ERROR, 'errors')
actions.setServerErrors = makeActionCreator(WORKSPACE_SERVER_ERRORS, 'errors')
actions.dismissRestrictions = makeActionCreator(WORKSPACE_RESTRICTIONS_DISMISS)

/**
 * Fetch the required data to open the current Workspace.
 *
 * @param {number} workspaceId
 *
 * @TODO : manage workspaces which change the current ui locale
 */
actions.open = (workspaceId) => (dispatch, getState) => {
  const workspace = selectors.workspace(getState())
  const loaded = selectors.loaded(getState())
  if (!loaded || !workspace || workspace.id !== workspaceId) {
    dispatch({
      [API_REQUEST]: {
        silent: true,
        url: ['claro_workspace_open', {workspaceId: workspaceId}],
        before: (dispatch) => dispatch(actions.setLoaded(false)),
        success: (response, dispatch) => {
          dispatch(actions.load(response))

          // mark the workspace as loaded
          // it's done through another action (not WORKSPACE_LOAD) to be sure all reducers have been resolved
          // and store is up-to-date
          dispatch(actions.setLoaded(true))
        },
        error: (response, status, dispatch) => {
          switch (status) {
            case 403: dispatch(actions.setRestrictionsError(response)); break
            case 401: dispatch(actions.setRestrictionsError(response)); break
            default: dispatch(actions.setServerErrors(response))
          }

          dispatch(actions.setLoaded(true))
        }
      }
    })
  }
}
