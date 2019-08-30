import {url} from '#/main/app/api'

import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {Role as RoleTypes} from '#/main/core/user/prop-types'

export const actions = {}

actions.open = (formName, id = null) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_role_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, RoleTypes.defaultProps, true))
  }
}

actions.addUsers = (id, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_role_add_users', {id: id}], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.roles.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.roles.current.users'))
    }
  }
})

actions.addGroups = (id, groups) => ({
  [API_REQUEST]: {
    url: url(['apiv2_role_add_groups', {id: id}], {ids: groups}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.roles.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.roles.current.groups'))
    }
  }
})
