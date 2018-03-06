import merge from 'lodash/merge'

import {generateUrl} from '#/main/core/api/router'

import {API_REQUEST} from '#/main/core/api/actions'
import {actions as formActions} from '#/main/core/data/form/actions'
import {actions as listActions} from '#/main/core/data/list/actions'


import {Organization as OrganizationTypes} from '#/main/core/administration/user/organization/prop-types'

export const actions = {}

actions.open = (formName, id = null, defaultProps = {}) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_organization_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else {
    defaultProps = merge(defaultProps, OrganizationTypes)
    dispatch(formActions.resetForm(formName, defaultProps, true))
  }
}

actions.addUsers = (id, users) => ({
  [API_REQUEST]: {
    url: generateUrl('apiv2_organization_add_users', {id: id}) +'?'+ users.map(id => 'ids[]='+id).join('&'),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('organizations.list'))
      dispatch(listActions.invalidateData('organizations.current.users'))
    }
  }
})

actions.addManagers = (id, users) => ({
  [API_REQUEST]: {
    url: generateUrl('apiv2_organization_add_managers', {id: id}) +'?'+ users.map(id => 'ids[]='+id).join('&'),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('organizations.list'))
      dispatch(listActions.invalidateData('organizations.current.managers'))
    }
  }
})

actions.addWorkspaces = (id, workspaces) => ({
  [API_REQUEST]: {
    url: generateUrl('apiv2_organization_add_workspaces', {id: id}) +'?'+ workspaces.map(id => 'ids[]='+id).join('&'),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('organizations.list'))
      dispatch(listActions.invalidateData('organizations.current.workspaces'))
    }
  }
})

actions.addGroups = (id, groups) => ({
  [API_REQUEST]: {
    url: generateUrl('apiv2_organization_add_groups', {id: id}) +'?'+ groups.map(id => 'ids[]='+id).join('&'),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('organizations.list'))
      dispatch(listActions.invalidateData('organizations.current.groups'))
    }
  }
})
