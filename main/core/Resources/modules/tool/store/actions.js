import isEqual from 'lodash/isEqual'

import {makeActionCreator, makeInstanceActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'
import {actions as menuActions} from '#/main/app/layout/menu/store/actions'

import {selectors} from '#/main/core/tool/store/selectors'

// actions
export const TOOL_OPEN        = 'TOOL_OPEN'
export const TOOL_CLOSE       = 'TOOL_CLOSE'
export const TOOL_LOAD        = 'TOOL_LOAD'
export const TOOL_SET_LOADED  = 'TOOL_SET_LOADED'

// action creators
export const actions = {}

actions.load = makeInstanceActionCreator(TOOL_LOAD, 'toolData', 'context')
actions.setLoaded = makeActionCreator(TOOL_SET_LOADED, 'loaded')

actions.open = (name, context, basePath) => (dispatch, getState) => {
  const prevName = selectors.name(getState())
  const prevContext = selectors.context(getState())

  if (name !== prevName || !isEqual(prevContext, context)) {
    dispatch({
      type: TOOL_OPEN,
      name: name,
      context: context,
      basePath: basePath
    })

    dispatch(actions.setLoaded(false))
  }
}

actions.close = makeActionCreator(TOOL_CLOSE)

/**
 * Fetch a tool.
 *
 * @param {string} toolName
 * @param {object} context
 */
actions.fetch = (toolName, context) => (dispatch) => {
  if (context.url) {
    dispatch({
      [API_REQUEST]: {
        silent: true,
        url: context.url,
        success: (response, dispatch) => {
          dispatch(actions.load(toolName, response, context))
          dispatch(actions.setLoaded(true))
          dispatch(menuActions.changeSection('tool'))
        }
      }
    })
  } else {
    dispatch(actions.setLoaded(true))
    dispatch(menuActions.changeSection('tool'))
  }
}

actions.closeTool = (toolName, context) => ({
  [API_REQUEST] : {
    url: ['apiv2_tool_close'],
    request: {
      method: 'PUT',
      body: JSON.stringify({toolName: toolName, context: context})
    }
  }
})
