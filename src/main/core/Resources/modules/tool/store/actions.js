import get from 'lodash/get'

import {makeActionCreator, makeInstanceActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

// actions
export const TOOL_OPEN              = 'TOOL_OPEN'
export const TOOL_LOAD              = 'TOOL_LOAD'
export const TOOL_SET_LOADED        = 'TOOL_SET_LOADED'
export const TOOL_SET_ACCESS_DENIED = 'TOOL_SET_ACCESS_DENIED'
export const TOOL_SET_NOT_FOUND     = 'TOOL_SET_NOT_FOUND'

// action creators
export const actions = {}

actions.load = makeActionCreator(TOOL_LOAD, 'toolData', 'context')
actions.loadType = makeInstanceActionCreator(TOOL_LOAD, 'toolData', 'context')
actions.setLoaded = makeActionCreator(TOOL_SET_LOADED, 'loaded')
actions.setAccessDenied = makeActionCreator(TOOL_SET_ACCESS_DENIED, 'accessDenied')
actions.setNotFound = makeActionCreator(TOOL_SET_NOT_FOUND, 'notFound')

/**
 * Fetch a tool.
 *
 * @param {string} toolName
 * @param {string} context
 * @param {string|null} contextId
 */
actions.open = (toolName, context, contextId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_tool_open', {context: context, contextId: contextId, name: toolName}],
    before: () => dispatch({
      type: TOOL_OPEN,
      name: toolName
    }),
    success: (response, dispatch) => {
      // load tool base data
      dispatch(actions.load(response, context))

      // load tool type data
      dispatch(actions.loadType(toolName, response, context))

      // mark the tool as loaded
      // it's done through another action (not TOOL_LOAD) to be sure all reducers have been resolved
      // and store is up-to-date
      dispatch(actions.setLoaded(true))
    },
    error: (error, status) => {
      switch (status) {
        case 401:
        case 403:
          dispatch(actions.setAccessDenied(true))
          dispatch(actions.setLoaded(true))
          break

        case 404:
          dispatch(actions.setNotFound(true))
          dispatch(actions.setLoaded(true))
          break
      }
    }
  }
})

actions.configure = (toolName, context, parameters) => ({
  [API_REQUEST] : {
    silent: true,
    url: ['apiv2_tool_configure', {name: toolName, context: context.type, contextId: get(context, 'data.id', null)}],
    request: {
      method: 'PUT',
      body: JSON.stringify(parameters)
    }
  }
})
