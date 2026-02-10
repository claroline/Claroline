import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

/**
 * Action dispatched when the context is opened, before we fetch the context data.
 * If you need to access to the context data, consider subscribing to CONTEXT_LOAD instead.
 */
export const CONTEXT_OPEN = 'CONTEXT_OPEN'

/**
 * Action dispatched when the context data is loaded in the store
 */
export const CONTEXT_LOAD = 'CONTEXT_LOAD'

/**
 * Action dispatched when the requested context has been fully loaded.
 */
export const CONTEXT_SET_LOADED = 'CONTEXT_SET_LOADED'

export const CONTEXT_SET_ERROR = 'CONTEXT_SET_ERROR'

/**
 * Action dispatched when the user chooses to open/close the context menu.
 */
export const CONTEXT_MENU_TOGGLE_OPEN = 'CONTEXT_MENU_TOGGLE_OPEN'


export const actions = {}

actions.toggleMenuOpen = makeActionCreator(CONTEXT_MENU_TOGGLE_OPEN)

actions.load = makeActionCreator(CONTEXT_LOAD, 'contextData')
actions.setLoaded = makeActionCreator(CONTEXT_SET_LOADED, 'loaded')
actions.setError = makeActionCreator(CONTEXT_SET_ERROR, 'code', 'message', 'additional')
actions.reload = () => actions.setLoaded(false)
actions.open = (contextType, contextId = null) => ({
  type: CONTEXT_OPEN,
  contextType: contextType,
  contextId: contextId
})

actions.fetch = (contextType, contextId = null) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: contextId ?
      ['claro_context_open', {context: contextType, contextId: contextId}] :
      ['claro_context_open', {context: contextType}],
    success: (response) => dispatch(actions.load(response)),
    error: (response, status) => {
      switch (status) {
        case 404:
          dispatch(actions.setError('NOT_FOUND', 'Context not found.', null))
          break
        case 500:
          dispatch(actions.setError('UNKNOWN_ERROR', response.message, response.trace))
          break
        case 401:
        case 403:
          dispatch(actions.load(response)) // the response contains why we can't access the context
          break
      }
    }
  }
})
