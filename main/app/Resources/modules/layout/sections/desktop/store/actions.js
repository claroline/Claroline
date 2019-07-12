import {makeActionCreator} from '#/main/app/store/actions'

import {API_REQUEST} from '#/main/app/api'

// actions
export const DESKTOP_LOAD = 'DESKTOP_LOAD'
export const DESKTOP_INVALIDATE = 'DESKTOP_INVALIDATE'
export const DESKTOP_HISTORY_LOAD = 'DESKTOP_HISTORY_LOAD'

// action creators
export const actions = {}

actions.load = makeActionCreator(DESKTOP_LOAD, 'tools', 'userProgression')
actions.loadHistory = makeActionCreator(DESKTOP_HISTORY_LOAD, 'history')
actions.invalidate = makeActionCreator(DESKTOP_INVALIDATE)

/**
 * Fetch the required data to open the current user desktop.
 */
actions.open = () => ({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_desktop_open'],
    success: (response, dispatch) => dispatch(actions.load(response.tools, response.userProgression))
  }
})

actions.getHistory = () => ({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_desktop_history_get'],
    success: (response, dispatch) => dispatch(actions.loadHistory(response))
  }
})