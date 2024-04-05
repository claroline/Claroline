import get from 'lodash/get'
import merge from 'lodash/merge'

import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'
import {actions as securityActions} from '#/main/app/security/store/actions'

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

/**
 * Action dispatched when the requested context can not be found.
 */
export const CONTEXT_NOT_FOUND = 'CONTEXT_NOT_FOUND'

/**
 * Action dispatched when a context manager dismiss all the access restrictions to open the context.
 */
export const CONTEXT_RESTRICTIONS_DISMISS = 'CONTEXT_RESTRICTIONS_DISMISS'

export const CONTEXT_MENU_OPEN = 'MENU_OPEN'
export const CONTEXT_MENU_CLOSE = 'MENU_CLOSE'
export const CONTEXT_MENU_TOGGLE = 'MENU_TOGGLE'

export const actions = {}

actions.load = makeActionCreator(CONTEXT_LOAD, 'contextData')
actions.setLoaded = makeActionCreator(CONTEXT_SET_LOADED, 'loaded')
actions.setNotFound = makeActionCreator(CONTEXT_NOT_FOUND)
actions.dismissRestrictions = makeActionCreator(CONTEXT_RESTRICTIONS_DISMISS)

actions.open = (contextType, contextId = null) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: contextId ?
      ['claro_context_open', {context: contextType, contextId: contextId}] :
      ['claro_context_open', {context: contextType}],
    before: () => dispatch({
      type: CONTEXT_OPEN,
      contextType: contextType,
      contextId: contextId
    }),
    success: (response) => dispatch(actions.load(response)),
    error: (response, status) => {
      switch (status) {
        case 404:
          dispatch(actions.setNotFound())
          break
        case 401:
        case 403:
          dispatch(actions.load(response)) // the response contains why we can't access the context
          break
      }
    }
  }
})

actions.openMenu = makeActionCreator(CONTEXT_MENU_OPEN)
actions.closeMenu = makeActionCreator(CONTEXT_MENU_CLOSE)
actions.toggleMenu = makeActionCreator(CONTEXT_MENU_TOGGLE)

actions.changeStatus = (currentUser, status) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_user_change_status', {status: status}],
    success: (response) => dispatch(securityActions.updateUser(merge({}, currentUser, {status: response}))),
    request: {method: 'PUT'}
  }
})
