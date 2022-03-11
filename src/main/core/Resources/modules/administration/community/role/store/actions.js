import {url} from '#/main/app/api'

import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {Role as RoleTypes} from '#/main/core/user/prop-types'

export const actions = {}

actions.open = (formName, id = null) => (dispatch) => {
  // invalidate embedded lists
  dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.roles.current.groups'))
  dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.roles.current.users'))

  if (id) {
    return dispatch({
      [API_REQUEST]: {
        url: ['apiv2_role_get', {id}],
        silent: true,
        before: () => dispatch(formActions.resetForm(formName, null, false)),
        success: (response) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  }

  return dispatch(formActions.resetForm(formName, RoleTypes.defaultProps, true))
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

actions.fetchStatistics = (id, year) => ({
  [API_REQUEST]: {
    url: ['apiv2_role_analytics', {id: id, year: year}]
  }
})
