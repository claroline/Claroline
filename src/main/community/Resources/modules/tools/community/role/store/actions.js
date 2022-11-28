import merge from 'lodash/merge'

import {API_REQUEST, url} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {Role as RoleTypes} from '#/main/community/role/prop-types'

import {selectors} from '#/main/community/tools/community/role/store/selectors'

export const actions = {}

actions.new = (defaultProps) => formActions.resetForm(selectors.FORM_NAME, merge({}, RoleTypes.defaultProps, defaultProps), true)

actions.open = (id, contextData = null, reload = false) => (dispatch) => {
  if (!reload) {
    // remove previous group if any to avoid displaying it while loading
    dispatch(formActions.resetForm(selectors.FORM_NAME, {}, false))
  }

  // invalidate embedded lists
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.users'))
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.groups'))

  return dispatch({
    [API_REQUEST]: {
      url: ['apiv2_role_get', {id: id, options: contextData ? ['serialize_role_tools_rights', `workspace_id_${contextData.id}`] : []}],
      success: (response) => dispatch(formActions.resetForm(selectors.FORM_NAME, response, false))
    }
  })
}

actions.addUsers = (id, users) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_role_add_users', {id: id}], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: () => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.users'))
    }
  }
})

actions.addGroups = (id, groups) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_role_add_groups', {id: id}], {ids: groups}),
    request: {
      method: 'PATCH'
    },
    success: () => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.groups'))
    }
  }
})

actions.fetchMetrics = (id, year) => ({
  [API_REQUEST]: {
    url: ['apiv2_role_analytics', {id: id, year: year}]
  }
})
