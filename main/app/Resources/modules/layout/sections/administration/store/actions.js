import {makeActionCreator} from '#/main/app/store/actions'

import {API_REQUEST} from '#/main/app/api'

import {actions as menuActions} from '#/main/app/layout/menu/store/actions'
import {actions as toolActions} from '#/main/core/tool/store/actions'
import {constants as toolConst} from '#/main/core/tool/constants'

// actions
export const ADMINISTRATION_LOAD = 'ADMINISTRATION_LOAD'

// action creators
export const actions = {}

actions.load = makeActionCreator(ADMINISTRATION_LOAD, 'tools')

/**
 * Fetch the required data to open administration.
 */
actions.open = () => ({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_admin_open'],
    success: (response, dispatch) => dispatch(actions.load(response.tools))
  }
})

/**
 * Open a tool in the admin.
 *
 * @param {string} toolName
 */
actions.openTool = (toolName) => (dispatch, getState) => {
  const state = getState()

  if (state.tool.loaded) {
    return Promise.resolve(true)
  }

  dispatch(toolActions.open(toolName, {type: toolConst.TOOL_ADMINISTRATION, data: {}}, '/admin'))
  dispatch(menuActions.changeSection('tool'))

  return dispatch({
    [API_REQUEST]: {
      silent: true,
      url: ['claro_admin_open_tool', {toolName: toolName}],
      success: (response, dispatch) => {
        dispatch(toolActions.load(toolName, response))
        dispatch(toolActions.setLoaded())
      }
    }
  })
}
