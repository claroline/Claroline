import {url} from '#/main/app/api'

import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors as baseSelectors} from '#/main/community/administration/community/store'
import {Group as GroupTypes} from '#/main/community/prop-types'

export const actions = {}

actions.open = (formName, id = null) => (dispatch) => {
  // invalidate embedded lists
  dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.groups.current.users'))
  dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.groups.current.organizations'))
  dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.groups.current.roles'))

  if (id) {
    return dispatch({
      [API_REQUEST]: {
        url: ['apiv2_group_get', {id}],
        success: (response, dispatch) => dispatch(formActions.resetForm(formName, response, false))
      }
    })
  }

  return dispatch(formActions.resetForm(formName, GroupTypes.defaultProps, true))
}

actions.addUsers = (id, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_group_add_users', {id: id}], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.groups.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.groups.current.users'))
    }
  }
})

actions.addRoles = (id, roles) => ({
  [API_REQUEST]: {
    url: url(['apiv2_group_add_roles', {id: id}], {ids: roles}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.groups.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.groups.current.roles'))
    }
  }
})

actions.addOrganizations = (id, organizations) => ({
  [API_REQUEST]: {
    url: url(['apiv2_group_add_organizations', {id: id}], {ids: organizations}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.groups.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.groups.current.organizations'))
    }
  }
})

actions.updatePassword = (groups) => ({
  [API_REQUEST]: {
    url: url(['apiv2_group_initialize_password'], {ids: groups}),
    request: {
      method: 'POST'
    }
  }
})
