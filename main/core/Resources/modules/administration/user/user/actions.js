import {url} from '#/main/core/api/router'

import {API_REQUEST} from '#/main/core/api/actions'
import {actions as listActions} from '#/main/core/data/list/actions'
import {actions as formActions} from '#/main/core/data/form/actions'

import {User as UserTypes} from '#/main/core/administration/user/user/prop-types'

export const actions = {}

actions.open = (formName, id = null) => {
  if (id) {
    return {
      [API_REQUEST]: {
        url: ['apiv2_user_get', {id}],
        success: (response, dispatch) => dispatch(formActions.resetForm(formName, response, false))
      }
    }
  } else {
    return formActions.resetForm(formName, UserTypes.defaultProps, true)
  }
}

actions.addGroups = (id, groups) => ({
  [API_REQUEST]: {
    url: url(['apiv2_user_add_groups', {id: id}], {ids: groups}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('users.list'))
      dispatch(listActions.invalidateData('users.current.groups'))
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
      dispatch(listActions.invalidateData('users.list'))
      dispatch(listActions.invalidateData('users.current.roles'))
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
      dispatch(listActions.invalidateData('users.list'))
      dispatch(listActions.invalidateData('users.current.organizations'))
    }
  }
})

actions.enable = (user) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_update', {id: user.id}],
    request: {
      body: JSON.stringify(Object.assign({}, user, {restrictions: {disabled:false}})),
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('users.list'))
    }
  }
})

actions.disable = (user) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_update', {id: user.id}],
    request: {
      method: 'PUT',
      body: JSON.stringify(Object.assign({}, user, {restrictions: {disabled:true}}))
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('users.list'))
    }
  }
})

actions.createWorkspace = (user) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_pws_create', {id: user.id}],
    request: { method: 'POST'},
    success: (data, dispatch) => dispatch(listActions.invalidateData('users.list'))
  }
})

actions.deleteWorkspace = (user) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_pws_delete', {id: user.id}],
    request: {method: 'DELETE'},
    success: (data, dispatch) => dispatch(listActions.invalidateData('users.list'))
  }
})
