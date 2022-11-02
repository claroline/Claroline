import {url} from '#/main/app/api/router'

import {API_REQUEST} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store/actions'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors as baseSelectors} from '#/main/community/administration/community/store/selectors'
import {User as UserTypes} from '#/main/community/prop-types'

export const USER_COMPARE = 'USER_COMPARE'

export const actions = {}

actions.open = (formName, id = null) => (dispatch) => {
  // invalidate embedded lists
  dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.users.current.groups'))
  dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.users.current.organizations'))
  dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.users.current.roles'))

  if (id) {
    return dispatch({
      [API_REQUEST]: {
        url: ['apiv2_user_get', {id}],
        success: (response) => dispatch(formActions.resetForm(formName, response, false))
      }
    })
  }

  return dispatch(formActions.resetForm(formName, UserTypes.defaultProps, true))
}

actions.close = (formName) => formActions.resetForm(formName)

actions.addGroups = (id, groups) => ({
  [API_REQUEST]: {
    url: url(['apiv2_user_add_groups', {id: id}], {ids: groups}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.users.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.users.current.groups'))
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
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.users.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.users.current.roles'))
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
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.users.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.users.current.organizations'))
    }
  }
})
