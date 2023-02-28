import get from 'lodash/get'
import merge from 'lodash/merge'
import isEmpty from 'lodash/isEmpty'

import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {constants} from '#/main/community/constants'
import {Role as RoleTypes} from '#/main/community/role/prop-types'

import {selectors} from '#/main/community/tools/community/role/store/selectors'

export const ROLE_WORKSPACE_RIGHTS_LOAD = 'ROLE_WORKSPACE_RIGHTS_LOAD'
export const ROLE_DESKTOP_RIGHTS_LOAD = 'ROLE_DESKTOP_RIGHTS_LOAD'
export const ROLE_ADMINISTRATION_RIGHTS_LOAD = 'ROLE_ADMINISTRATION_RIGHTS_LOAD'

export const actions = {}

actions.loadWorkspaceRights = makeActionCreator(ROLE_WORKSPACE_RIGHTS_LOAD, 'rights')
actions.loadDesktopRights = makeActionCreator(ROLE_DESKTOP_RIGHTS_LOAD, 'rights')
actions.loadAdministrationRights = makeActionCreator(ROLE_ADMINISTRATION_RIGHTS_LOAD, 'rights')

actions.new = (defaultProps) => formActions.resetForm(selectors.FORM_NAME, merge({}, RoleTypes.defaultProps, defaultProps), true)

actions.open = (id, contextData = null, reload = false) => (dispatch) => {
  if (!reload) {
    // remove previous role if any to avoid displaying it while loading
    dispatch(formActions.resetForm(selectors.FORM_NAME, {}, false))
  }

  // invalidate embedded lists
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.users'))
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.groups'))

  return dispatch({
    [API_REQUEST]: {
      url: ['apiv2_role_get', {id: id}],
      silent: true,
      success: (response) => {
        dispatch(formActions.resetForm(selectors.FORM_NAME, response, false))

        if (!isEmpty(contextData) || constants.ROLE_WORKSPACE === response.type) {
          dispatch(actions.fetchWorkspaceRights(id, !isEmpty(contextData) ? contextData.id : get(response, 'workspace.id')))
        } else if (constants.ROLE_PLATFORM === response.type) {
          dispatch(actions.fetchDesktopRights(id))
          dispatch(actions.fetchAdministrationRights(id))
        }
      }
    }
  })
}

actions.addUsers = (id, users) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_role_add_users', {id: id}], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: () => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.users'))
    }
  }
})

actions.addGroups = (id, groups) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_role_add_groups', {id: id}], {ids: groups}),
    request: {
      method: 'PATCH'
    },
    success: () => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.groups'))
    }
  }
})

actions.fetchMetrics = (id, year) => ({
  [API_REQUEST]: {
    url: ['apiv2_role_analytics', {id: id, year: year}],
    silent: true
  }
})

actions.fetchWorkspaceRights = (id, contextId = null) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_role_rights_list', {id: id, contextType: 'workspace', contextId: contextId}],
    silent: true,
    success: (response) => dispatch(actions.loadWorkspaceRights(response))
  }
})

actions.fetchDesktopRights = (id) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_role_rights_list', {id: id, contextType: 'desktop'}],
    silent: true,
    success: (response) => dispatch(actions.loadDesktopRights(response))
  }
})

actions.fetchAdministrationRights = (id) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_role_rights_list', {id: id, contextType: 'administration'}],
    silent: true,
    success: (response) => dispatch(actions.loadAdministrationRights(response))
  }
})
