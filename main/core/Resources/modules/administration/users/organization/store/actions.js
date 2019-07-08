import merge from 'lodash/merge'
import {makeActionCreator} from '#/main/app/store/actions'
import {url} from '#/main/app/api'

import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors as baseSelectors} from '#/main/core/administration/users/store'
import {Organization as OrganizationTypes} from '#/main/core/user/prop-types'

export const UPDATE_LIMIT = 'UPDATE_LIMIT'

export const actions = {}

actions.updateLimit = makeActionCreator(UPDATE_LIMIT, 'enable')

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
