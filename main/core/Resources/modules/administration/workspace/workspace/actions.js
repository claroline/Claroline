import {generateUrl} from '#/main/core/api/router'

import {actions as listActions} from '#/main/core/data/list/actions'
import {getDataQueryString} from '#/main/core/data/list/utils'
import {actions as formActions} from '#/main/core/data/form/actions'
import {actions as alertActions} from '#/main/core/layout/alert/actions'
import {constants as alertConstants} from '#/main/core/layout/alert/constants'
import {constants as actionConstants} from '#/main/core/layout/action/constants'

import {Workspace as WorkspaceTypes} from '#/main/core/administration/workspace/workspace/prop-types'

import {API_REQUEST} from '#/main/core/api/actions'

export const WORKSPACE_ADD_MANAGER = 'WORKSPACE_ADD_MANAGER'
export const WORKSPACE_REMOVE_MANAGER = 'WORKSPACE_REMOVE_MANAGER'

export const actions = {}

actions.open = (formName, id = null) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_workspace_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, WorkspaceTypes.defaultProps, true))
  }
}

actions.copyWorkspaces = (workspaces, isModel = 0) => ({
  [API_REQUEST]: {
    url: generateUrl('apiv2_workspace_copy_bulk') + getDataQueryString(workspaces) + '&model=' + isModel,
    request: {
      method: 'GET'
    },
    success: (data, dispatch) => dispatch(listActions.invalidateData('workspaces.list'))
  }
})

actions.addOrganizations = (id, organizations) => ({
  [API_REQUEST]: {
    url: generateUrl('apiv2_workspace_add_organizations', {id: id}) +'?'+ organizations.map(id => 'ids[]='+id).join('&'),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('workspaces.list'))
      dispatch(listActions.invalidateData('workspaces.current.organizations'))
    }
  }
})

actions.addManagers = (id, users, roleId) => ({
  [API_REQUEST]: {
    url: generateUrl('apiv2_role_add_users', {id: roleId}) + '?' +  users.map(user => 'ids[]=' + user).join('&'),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('workspaces.list'))
      dispatch(listActions.invalidateData('workspaces.current.managers'))
    }
  }
})

actions.deleteWorkspaces = (workspaces) => ({
  [API_REQUEST]: {
    url: generateUrl('apiv2_workspace_delete_bulk_override') + '?' +  workspaces.map(w => 'ids[]=' + w.uuid).join('&'),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('workspaces.list'))
    },
    error: (data, dispatch) => {
      if (data['errors']) {
        Object.values(data['errors']).forEach(message => dispatch(alertActions.addAlert(
          'workspace-deletion',
          alertConstants.ALERT_STATUS_WARNING,
          actionConstants.ACTION_DELETE,
          null,
          message
        )))

      }
      dispatch(listActions.invalidateData('workspaces.list'))
    }
  }
})
