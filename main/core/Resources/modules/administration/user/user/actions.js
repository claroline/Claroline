import {url} from '#/main/app/api/router'

import {API_REQUEST} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store'
import {actions as formActions} from '#/main/app/content/form/store'

import {User as UserTypes} from '#/main/core/user/prop-types'

export const USER_COMPARE = 'USER_COMPARE'

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

actions.compareOpen = (data) => ({
  type: USER_COMPARE,
  data: data
})

actions.compare = (ids) => {
  const queryParams = []

  ids.map((id, index) => {
    queryParams.push(`filters[id][${index}]=${id}`)
  })

  return {
    [API_REQUEST]: {
      url: url(['apiv2_user_list']) + '?' + queryParams.join('&'),
      success: (response, dispatch) => dispatch(actions.compareOpen(response.data))
    }
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

actions.enable = (users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_users_enable'], {ids: users.map(u => u.id)}),
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('users.list'))
    }
  }
})

actions.disable = (users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_users_disable'], {ids: users.map(u => u.id)}),
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('users.list'))
    }
  }
})

actions.createWorkspace = (users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_users_pws_create'], {ids: users.map(u => u.id)}),
    request: {
      method: 'POST'
    },
    success: (data, dispatch) => dispatch(listActions.invalidateData('users.list'))
  }
})

actions.deleteWorkspace = (users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_users_pws_delete'], {ids: users.map(u => u.id)}),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => dispatch(listActions.invalidateData('users.list'))
  }
})

actions.merge = (id1, id2, navigate) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_merge', {keep: id1, remove: id2}],
    request: {method: 'PUT'},
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('users.list'))
      dispatch(listActions.resetSelect('users.list'))
      navigate('/users')
    }
  }
})
