import get from 'lodash/get'
import isEqual from 'lodash/isEqual'

import {makeActionCreator, makeInstanceActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'
import {actions as menuActions} from '#/main/app/layout/menu/store/actions'

import {selectors} from '#/main/core/tool/store/selectors'

// actions
export const TOOL_OPEN              = 'TOOL_OPEN'
export const TOOL_CLOSE             = 'TOOL_CLOSE'
export const TOOL_LOAD              = 'TOOL_LOAD'
export const TOOL_SET_LOADED        = 'TOOL_SET_LOADED'
export const TOOL_SET_ACCESS_DENIED = 'TOOL_SET_ACCESS_DENIED'
export const TOOL_SET_NOT_FOUND     = 'TOOL_SET_NOT_FOUND'
export const TOOL_TOGGLE_FULLSCREEN = 'TOOL_TOGGLE_FULLSCREEN'

// action creators
export const actions = {}

actions.load = makeActionCreator(TOOL_LOAD, 'toolData', 'context')
actions.loadType = makeInstanceActionCreator(TOOL_LOAD, 'toolData', 'context')
actions.setLoaded = makeActionCreator(TOOL_SET_LOADED, 'loaded')
actions.setAccessDenied = makeActionCreator(TOOL_SET_ACCESS_DENIED, 'accessDenied')
actions.setNotFound = makeActionCreator(TOOL_SET_NOT_FOUND, 'notFound')
actions.toggleFullscreen = makeActionCreator(TOOL_TOGGLE_FULLSCREEN)

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
    return dispatch({
      [API_REQUEST]: {
        silent: true,
        url: context.url,
        success: (response, dispatch) => {
          // load tool base data
          dispatch(actions.load(response, context))

          // load tool type data
          dispatch(actions.loadType(toolName, response, context))

          dispatch(actions.setLoaded(true))
          dispatch(menuActions.changeSection('tool'))
        },
        error: (error, status) => {
          switch (status) {
            case 401:
            case 403:
              dispatch(actions.setLoaded(true))
              dispatch(actions.setAccessDenied(true))
              break

            case 404:
              dispatch(actions.setLoaded(true))
              dispatch(actions.setNotFound(true))
              break
          }
        }
      }
    })
  } else {
    dispatch(actions.setLoaded(true))
    dispatch(menuActions.changeSection('tool'))

    return Promise.resolve({})
  }
}

actions.closeTool = (toolName, context) => ({
  [API_REQUEST] : {
    silent: true,
    url: ['apiv2_tool_close', {name: toolName, context: context.type, contextId: get(context, 'data.id', null)}],
    request: {
      method: 'PUT'
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