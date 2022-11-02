import {API_REQUEST, url} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors} from '#/main/community/tools/community/store'

export const actions = {}

actions.open = (formName, id = null, defaultValue) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_role_get', {id, options: ['serialize_role_tools_rights', `workspace_id_${defaultValue.workspace.id}`]}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, defaultValue, true))
  }
}

actions.addUsers = (id, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_role_add_users', {id: id}], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.roles.list'))
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.roles.current.users'))
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
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.roles.list'))
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.roles.current.groups'))
    }
  }
})

