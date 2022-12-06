import merge from 'lodash/merge'

import {API_REQUEST, url} from '#/main/app/api'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store'
import {actions as formActions} from '#/main/app/content/form/store'

import {selectors} from '#/main/community/tools/community/group/store/selectors'
import {selectors as baseSelectors} from '#/main/community/tools/community/store/selectors'
import {Group as GroupTypes} from '#/main/community/group/prop-types'

export const actions = {}

actions.new = () => (dispatch, getState) => {
  const defaultOrganization = securitySelectors.mainOrganization(getState())

  return dispatch(formActions.reset(selectors.FORM_NAME, merge({
    organizations: defaultOrganization ? [defaultOrganization] : []
  }, GroupTypes.defaultProps), true))
}

actions.open = (id, reload = false) => (dispatch) => {
  if (!reload) {
    // remove previous group if any to avoid displaying it while loading
    dispatch(formActions.reset(selectors.FORM_NAME, {}, false))
  }

  // invalidate embedded lists
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.users'))
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.organizations'))
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.roles'))

  return dispatch({
    [API_REQUEST]: {
      url: ['apiv2_group_get', {id}],
      silent: true,
      success: (response) => dispatch(formActions.resetForm(selectors.FORM_NAME, response, false))
    }
  })
}

actions.addGroupsToRole = (role, groups) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_role_add_groups', {id: role.id}], {ids: groups.map(group => group.id)}),
    request: {
      method: 'PATCH'
    },
    success: () => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME + '.users.list'))
      dispatch(listActions.invalidateData(selectors.FORM_NAME + '.roles'))
    }
  }
})

actions.unregister = (groups, workspace) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_workspace_unregister_groups', {id: workspace.id}]) + '?'+ groups.map(group => 'ids[]='+group.id).join('&'),
    request: {
      method: 'DELETE'
    },
    success: () => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME + '.users.list'))
    }
  }
})

actions.addUsers = (id, users) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_group_add_users', {id: id}], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: () => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.users'))
    }
  }
})

actions.addRoles = (id, roles) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_group_add_roles', {id: id}], {ids: roles}),
    request: {
      method: 'PATCH'
    },
    success: () => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.roles'))
    }
  }
})

actions.addOrganizations = (id, organizations) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_group_add_organizations', {id: id}], {ids: organizations}),
    request: {
      method: 'PATCH'
    },
    success: () => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.organizations'))
    }
  }
})