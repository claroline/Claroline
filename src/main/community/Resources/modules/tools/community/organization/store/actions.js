import merge from 'lodash/merge'

import {API_REQUEST, url} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {Organization as OrganizationTypes} from '#/main/community/organization/prop-types'
import {selectors} from '#/main/community/tools/community/organization/store/selectors'

export const actions = {}

actions.new = (defaultProps) => formActions.resetForm(selectors.FORM_NAME, merge({}, OrganizationTypes.defaultProps, defaultProps), true)

actions.open = (id, reload = false) => (dispatch) => {
  if (!reload) {
    // remove previous group if any to avoid displaying it while loading
    dispatch(formActions.resetForm(selectors.FORM_NAME, {}, false))
  }

  // invalidate embedded lists
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.users'))
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.groups'))
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.managers'))
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.workspaces'))

  return dispatch({
    [API_REQUEST]: {
      url: ['apiv2_organization_get', {id: id}],
      success: (response) => dispatch(formActions.resetForm(selectors.FORM_NAME, response, false))
    }
  })
}

actions.addUsers = (id, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_organization_add_users', {id: id}], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.users'))
    }
  }
})

actions.addManagers = (id, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_organization_add_managers', {id: id}], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.managers'))
    }
  }
})

actions.addWorkspaces = (id, workspaces) => ({
  [API_REQUEST]: {
    url: url(['apiv2_organization_add_workspaces', {id: id}], {ids: workspaces}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.workspaces'))
    }
  }
})

actions.addGroups = (id, groups) => ({
  [API_REQUEST]: {
    url: url(['apiv2_organization_add_groups', {id: id}], {ids: groups}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.groups'))
    }
  }
})
