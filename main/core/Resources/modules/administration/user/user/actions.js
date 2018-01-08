import {generateUrl} from '#/main/core/api/router'

import {API_REQUEST} from '#/main/core/api/actions'
import {actions as listActions} from '#/main/core/data/list/actions'
import {actions as formActions} from '#/main/core/data/form/actions'

import {User as UserTypes} from '#/main/core/administration/user/user/prop-types'

export const actions = {}

actions.open = (formName, id = null) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_user_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, UserTypes.defaultProps, true))
  }
}

actions.addGroups = (id, groups) => ({
  [API_REQUEST]: {
    url: generateUrl('apiv2_user_add_groups', {id: id}) +'?'+ groups.map(id => 'ids[]='+id).join('&'),
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
    url: generateUrl('apiv2_user_add_roles', {id: id}) +'?'+ roles.map(id => 'ids[]='+id).join('&'),
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
    url: generateUrl('apiv2_user_add_organizations', {id: id}) +'?'+ organizations.map(id => 'ids[]='+id).join('&'),
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
    url: ['apiv2_user_update', {uuid: user.id}],
    request: {
      body: JSON.stringify({isEnabled: true, uuid: user.id}),
      method: 'PUT'
    },
    success: (data, dispatch) => dispatch(listActions.fetchData('users'))
  }
})

actions.disable = (user) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_update', {uuid: user.id}],
    request: {
      method: 'PUT',
      body: JSON.stringify({isEnabled: false, uuid: user.id})
    },
    success: (data, dispatch) => dispatch(listActions.fetchData('users'))
  }
})

actions.createWorkspace = (user) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_pws_create', {uuid: user.id}],
    request: { method: 'POST'},
    success: (data, dispatch) => dispatch(listActions.fetchData('users'))
  }
})

actions.deleteWorkspace = (user) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_pws_delete', {uuid: user.id}],
    request: {method: 'DELETE'},
    success: (data, dispatch) => dispatch(listActions.fetchData('users'))
  }
})
