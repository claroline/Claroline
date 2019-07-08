import {makeActionCreator} from '#/main/app/store/actions'

import {API_REQUEST} from '#/main/app/api'

import {actions as menuActions} from '#/main/app/layout/menu/store/actions'
import {actions as toolActions} from '#/main/core/tool/store/actions'
import {constants as toolConst} from '#/main/core/tool/constants'

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

/**
 * Open a tool in the desktop.
 *
 * @param {string} toolName
 */
actions.openTool = (toolName) => (dispatch, getState) => {
  const state = getState()

  if (state.tool.loaded) {
    return Promise.resolve(true)
  }

  dispatch(toolActions.open(toolName, {type: toolConst.TOOL_DESKTOP, data: {}}, '/desktop'))
  dispatch(menuActions.changeSection('tool'))

  return dispatch({
    [API_REQUEST]: {
      silent: true,
      url: ['claro_desktop_open_tool', {toolName: toolName}],
      success: (response, dispatch) => {
        dispatch(toolActions.load(toolName, response))
        dispatch(toolActions.setLoaded())
      }
    }
  })
}

actions.getHistory = () => ({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_desktop_history_get'],
    success: (response, dispatch) => dispatch(actions.loadHistory(response))
  }
})