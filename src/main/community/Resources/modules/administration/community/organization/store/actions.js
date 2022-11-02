import merge from 'lodash/merge'

import {API_REQUEST, url} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors as baseSelectors} from '#/main/community/administration/community/store'
import {Organization as OrganizationTypes} from '#/main/community/prop-types'

export const actions = {}

actions.open = (formName, id = null, defaultProps = {}) => (dispatch) => {
  // invalidate embedded lists
  dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.organizations.current.groups'))
  dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.organizations.current.users'))
  dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.organizations.current.managers'))
  dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.organizations.current.workspaces'))

  if (id) {
    return dispatch({
      [API_REQUEST]: {
        url: ['apiv2_organization_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  }

  return dispatch(formActions.resetForm(formName, merge(defaultProps, OrganizationTypes.defaultProps), true))
}

actions.addUsers = (id, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_organization_add_users', {id: id}], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.organizations.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.organizations.current.users'))
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
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.organizations.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.organizations.current.managers'))
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
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.organizations.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.organizations.current.workspaces'))
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
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.organizations.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.organizations.current.groups'))
    }
  }
})
