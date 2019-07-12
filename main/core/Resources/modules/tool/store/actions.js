import {makeActionCreator, makeInstanceActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

import {actions as menuActions} from '#/main/app/layout/menu/store/actions'

// actions
export const TOOL_OPEN        = 'TOOL_OPEN'
export const TOOL_CLOSE       = 'TOOL_CLOSE'
export const TOOL_LOAD        = 'TOOL_LOAD'
export const TOOL_SET_LOADED  = 'TOOL_SET_LOADED'

// action creators
export const actions = {}

actions.load = makeInstanceActionCreator(TOOL_LOAD, 'toolData')
actions.setLoaded = makeActionCreator(TOOL_SET_LOADED)

actions.open = makeActionCreator(TOOL_OPEN, 'name', 'context', 'basePath')
actions.close = makeActionCreator(TOOL_CLOSE)

/**
 * Fetch a tool.
 *
 * @param {string} toolName
 * @param {object} context
 * @param {string} basePath
 */
actions.fetch = (toolName, context, basePath = '') => (dispatch, getState) => {
  const state = getState()

  if (state.tool.loaded) {
    return Promise.resolve(true)
  }

  dispatch(actions.open(toolName, context, basePath))
  dispatch(menuActions.changeSection('tool'))

  if (context.url) {
    return dispatch({
      [API_REQUEST]: {
        silent: true,
        url: context.url,
        success: (response, dispatch) => {
          dispatch(actions.load(toolName, response))
          dispatch(actions.setLoaded())
        }
      }
    })
  } else {
    dispatch(actions.setLoaded())
  }
}