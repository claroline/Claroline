import merge from 'lodash/merge'

import {API_REQUEST, url} from '#/main/app/api'
import {selectors as securitySelectors} from '#/main/app/security/store/selectors'
import {actions as listActions} from '#/main/app/content/list/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {User as UserTypes} from '#/main/community/user/prop-types'
import {selectors} from '#/main/community/tools/community/user/store/selectors'

export const actions = {}

actions.new = (defaultProps) => (dispatch, getState) => {
  const defaultOrganization = securitySelectors.mainOrganization(getState())

  return dispatch(formActions.resetForm(selectors.FORM_NAME, merge({
    mainOrganization: defaultOrganization,
    organizations: defaultOrganization ? [defaultOrganization] : []
  }, UserTypes.defaultProps, defaultProps), true))
}

actions.open = (username, reload = false) => (dispatch, getState) => {
  if (!reload) {
    const currentUser = formSelectors.data(formSelectors.form(getState(), selectors.FORM_NAME))
    if (currentUser && username === currentUser.username) {
      // no need to reload the displayed user
      return
    }

    // remove previous user if any to avoid displaying it while loading
    dispatch(formActions.resetForm(selectors.FORM_NAME, {}, false))
  }

  // invalidate embedded lists
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.groups'))
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.organizations'))
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.roles'))

  return dispatch({
    [API_REQUEST]: {
      url: ['apiv2_user_get', {field: 'username', id: username}],
      silent: true,
      success: (response, dispatch) => dispatch(formActions.resetForm(selectors.FORM_NAME, response, false))
    }
  })
}

actions.addUsersToRole = (role, users)  => ({
  [API_REQUEST]: {
    url: url(['apiv2_role_add_users', {id: role.id}], {ids: users.map(user => user.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME + '.roles'))
    }
  }
})

actions.unregister = (users, workspace) => ({
  [API_REQUEST]: {
    url: url(['apiv2_workspace_unregister_users', {id: workspace.id}]) + '?'+ users.map(user => 'ids[]='+user.id).join('&'),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(listActions.deleteItems(selectors.LIST_NAME, users))
    }
  }
})

actions.addGroups = (id, groups) => ({
  [API_REQUEST]: {
    url: url(['apiv2_user_add_groups', {id: id}], {ids: groups}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.groups'))
    }
  }
})

actions.addRoles = (id, roles) => ({
  [API_REQUEST]: {
    url: url(['apiv2_user_add_roles', {id: id}], {ids: roles}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.roles'))
    }
  }
})

actions.addOrganizations = (id, organizations) => ({
  [API_REQUEST]: {
    url: url(['apiv2_user_add_organizations', {id: id}], {ids: organizations}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.organizations'))
    }
  }
})
