import {API_REQUEST, url} from '#/main/app/api'

import {actions as listActions} from '#/main/app/content/list/store'
import {actions as formActions} from '#/main/app/content/form/store'

export const actions = {}

actions.open = (formName, id = null, defaultProps) => {
  if (id) {
    return {
      [API_REQUEST]: {
        url: ['apiv2_group_get', {id}],
        success: (response, dispatch) => dispatch(formActions.resetForm(formName, response, false))
      }
    }
  } else {
    return formActions.resetForm(formName, defaultProps, true)
  }
}

actions.addGroupsToRole = (role, groups)  => ({
  [API_REQUEST]: {
    url: url(['apiv2_role_add_groups', {id: role}], {ids: groups}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('groups.list'))
      dispatch(listActions.invalidateData('groups.current.roles'))
    }
  }
})

actions.unregister = (groups, workspace) => ({
  [API_REQUEST]: {
    url: url(['apiv2_workspace_unregister_groups', {id: workspace.uuid}]) + '?'+ groups.map(group => 'ids[]='+group.id).join('&'),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('groups.list'))
    }
  }
})
