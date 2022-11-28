import {API_REQUEST, url} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors} from '#/main/community/tools/community/store/selectors'
import merge from 'lodash/merge'
import {Role as RoleTypes} from '#/main/community/role/prop-types'

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

actions.open = (formName, username = null, defaultProps) => (dispatch, getState) => {
  const current = formSelectors.data(formSelectors.form(getState(), formName))

  if (current.username !== username) {
    if (username) {
      return dispatch({
        [API_REQUEST]: {
          url: url(['apiv2_user_find'], {filters: {username: username}}),
          success: (response, dispatch) => dispatch(formActions.resetForm(formName, response, false))
        }
      })
    }

    dispatch(formActions.resetForm(formName, defaultProps, true))
  }
}

actions.addUsersToRole = (role, users)  => ({
  [API_REQUEST]: {
    url: url(['apiv2_role_add_users', {id: role.id}], {ids: users.map(user => user.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.users.list'))
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.users.current.roles'))
    }
  }
})

actions.unregister = (users, workspace) => ({
  [API_REQUEST]: {
    url: url(['apiv2_workspace_unregister_users', {id: workspace.id}]) + '?'+ users.map(user => 'ids[]='+user.id).join('&'),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(listActions.deleteItems(selectors.STORE_NAME + '.users.list', users))
    }
  }
})
