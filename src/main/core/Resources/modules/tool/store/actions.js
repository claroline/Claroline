import get from 'lodash/get'

import {makeActionCreator, makeInstanceActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

// actions
export const TOOL_OPEN       = 'TOOL_OPEN'
export const TOOL_LOAD       = 'TOOL_LOAD'
export const TOOL_SET_LOADED = 'TOOL_SET_LOADED'

// action creators
export const actions = {}

actions.load = makeInstanceActionCreator(TOOL_LOAD, 'toolData', 'context')
actions.setLoaded = makeActionCreator(TOOL_SET_LOADED, 'loaded')
actions.reload = () => actions.setLoaded(false)

/**
 * Fetch a tool.
 *
 * @param {string} toolName
 * @param {string} context
 * @param {string|null} contextId
 */
actions.open = (toolName, context, contextId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['claro_tool_open', {context: context, contextId: contextId, name: toolName}],
    before: () => dispatch({
      type: TOOL_OPEN,
      name: toolName
    }),
    success: (response) => {
      // load tool type data
      dispatch(actions.load(toolName, response, context))

      // mark the tool as loaded
      // it's done through another action (not TOOL_LOAD) to be sure all reducers have been resolved
      // and store is up-to-date
      dispatch(actions.setLoaded(true))
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
