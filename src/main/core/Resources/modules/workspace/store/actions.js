import get from 'lodash/get'

import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'
import {actions as menuActions} from '#/main/app/layout/menu/store/actions'

import {selectors} from '#/main/core/workspace/store/selectors'

// actions
export const WORKSPACE_OPEN = 'WORKSPACE_OPEN'
export const WORKSPACE_LOAD = 'WORKSPACE_LOAD'
export const WORKSPACE_SET_LOADED = 'WORKSPACE_SET_LOADED'
export const WORKSPACE_SERVER_ERRORS = 'WORKSPACE_SERVER_ERRORS'
export const WORKSPACE_RESTRICTIONS_ERROR = 'WORKSPACE_RESTRICTIONS_ERROR'
export const WORKSPACE_RESTRICTIONS_DISMISS = 'WORKSPACE_RESTRICTIONS_DISMISS'
export const WORKSPACE_RESTRICTIONS_UNLOCKED = 'WORKSPACE_RESTRICTIONS_UNLOCKED'
export const WORKSPACE_NOT_FOUND = 'WORKSPACE_NOT_FOUND'
export const SHORTCUTS_LOAD = 'SHORTCUTS_LOAD'

// action creators
export const actions = {}

actions.open = makeActionCreator(WORKSPACE_OPEN, 'slug')
actions.load = makeActionCreator(WORKSPACE_LOAD, 'workspaceData')
actions.setLoaded = makeActionCreator(WORKSPACE_SET_LOADED, 'loaded')
actions.setRestrictionsError = makeActionCreator(WORKSPACE_RESTRICTIONS_ERROR, 'errors')
actions.setServerErrors = makeActionCreator(WORKSPACE_SERVER_ERRORS, 'errors')
actions.setNotFound = makeActionCreator(WORKSPACE_NOT_FOUND)
actions.dismissRestrictions = makeActionCreator(WORKSPACE_RESTRICTIONS_DISMISS)
actions.unlockWorkspace = makeActionCreator(WORKSPACE_RESTRICTIONS_UNLOCKED)
actions.loadShortcuts = makeActionCreator(SHORTCUTS_LOAD, 'shortcuts')

/**
 * Fetch the required data to open the current Workspace.
 *
 * @param {number} slug
 *
 * @TODO : manage workspaces which change the current ui locale
 */
actions.fetch = (slug) => (dispatch, getState) => {
  const workspace = selectors.workspace(getState())
  const loaded = selectors.loaded(getState())
  if (!loaded || !workspace || workspace.slug !== slug) {
    return dispatch({
      [API_REQUEST]: {
        silent: true,
        url: ['claro_workspace_open', {slug: slug}],
        before: () => dispatch(actions.open(slug)),
        success: (response) => {
          dispatch(actions.load(response))

          // mark the workspace as loaded
          // it's done through another action (not WORKSPACE_LOAD) to be sure all reducers have been resolved
          // and store is up-to-date
          dispatch(actions.setLoaded(true))

          // set menu state based on ws configuration
          dispatch(menuActions.setState(get(response, 'workspace.opening.menu')))
        },
        error: (response, status) => {
          switch (status) {
            case 404: dispatch(actions.setNotFound()); break
            case 403: dispatch(actions.setRestrictionsError(response)); break
            case 401: dispatch(actions.setRestrictionsError(response)); break
            default: dispatch(actions.setServerErrors(response))
          }

          dispatch(actions.setLoaded(true))
        }
      }
    })
  }
}

actions.fetchModel = (id) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_workspace_get', {id: id}]
  }
})

actions.reload = (workspace) => (dispatch) => {
  dispatch(actions.setLoaded(false))
  dispatch(actions.fetch(workspace.slug))
}

actions.closeWorkspace = (slug) => ({
  [API_REQUEST] : {
    silent: true,
    url: ['apiv2_workspace_close', {slug: slug}],
    request: {
      method: 'PUT'
    }
  }
})

actions.checkAccessCode = (workspace, code) => ({
  [API_REQUEST] : {
    url: ['claro_workspace_unlock', {id: workspace.id}],
    request: {
      method: 'POST',
      body: JSON.stringify({code: code})
    },
    success: (response, dispatch) => {
      dispatch(actions.unlockWorkspace())
    }
  }
})

actions.selfRegister = (workspace) => ({
  [API_REQUEST] : {
    url: ['apiv2_workspace_self_register', {workspace: workspace.id}],
    request: {
      method: 'PUT'
    },
    success: (response, dispatch) => dispatch(actions.reload(workspace))
  }
})

actions.addShortcuts = (workspaceId, roleId, shortcuts) => ({
  [API_REQUEST] : {
    url: ['apiv2_workspace_shortcuts_add', {workspace: workspaceId, role: roleId}],
    request: {
      method: 'PUT',
      body: JSON.stringify({shortcuts: shortcuts})
    },
    success: (response, dispatch) => {
      dispatch(actions.loadShortcuts(response))
    }
  }
})

actions.removeShortcut = (workspaceId, roleId, type, name) => ({
  [API_REQUEST] : {
    url: ['apiv2_workspace_shortcut_remove', {workspace: workspaceId, role: roleId}],
    request: {
      method: 'PUT',
      body: JSON.stringify({type: type, name: name})
    },
    success: (response, dispatch) => {
      dispatch(actions.loadShortcuts(response))
    }
  }
})
