import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store/actions'

import {selectors} from '#/plugin/analytics/tools/dashboard/progression/store/selectors'

export const USER_PROGRESSION_LOAD = 'USER_PROGRESSION_LOAD'
export const USER_PROGRESSION_RESET = 'USER_PROGRESSION_RESET'
export const LOAD_REQUIREMENTS = 'LOAD_REQUIREMENTS'

export const actions = {}

actions.loadUserProgression = makeActionCreator(USER_PROGRESSION_LOAD, 'workspaceEvaluation', 'resourceEvaluations')
actions.resetUserProgression = makeActionCreator(USER_PROGRESSION_RESET)

actions.fetchUserProgression = (workspaceId, userId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_workspace_get_user_progression', {workspace: workspaceId, user: userId}],
    silent: true,
    before: () => dispatch(actions.resetUserProgression()),
    success: (response) => dispatch(actions.loadUserProgression(response.workspaceEvaluation, response.resourceEvaluations))
  }
})

actions.loadRequirements = makeActionCreator(LOAD_REQUIREMENTS, 'data')

actions.openRequirements = (id) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: ['apiv2_workspace_requirements_fetch', {requirements: id}],
      request: {
        method: 'GET'
      },
      success: (response, dispatch) => dispatch(actions.loadRequirements(response))
    }
  })
}

actions.createRequirementsForRoles = (workspace, roles) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: url(['apiv2_workspace_requirements_create', {workspace: workspace.id, type: 'role'}], {ids: roles.map(r => r.id)}),
      request: {
        method: 'PUT'
      },
      success: (response, dispatch) => dispatch(listActions.invalidateData(selectors.STORE_NAME + '.requirements.roles'))
    }
  })
}

actions.createRequirementsForUsers = (workspace, users) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: url(['apiv2_workspace_requirements_create', {workspace: workspace.id, type: 'user'}], {ids: users.map(u => u.id)}),
      request: {
        method: 'PUT'
      },
      success: (response, dispatch) => dispatch(listActions.invalidateData(selectors.STORE_NAME + '.requirements.users'))
    }
  })
}

actions.addRequirementsResources = (requirements, resources) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: url(['apiv2_workspace_requirements_resources_add', {requirements: requirements.id}], {ids: resources.map(r => r.id)}),
      request: {
        method: 'PUT'
      },
      success: (response, dispatch) => dispatch(actions.loadRequirements(response))
    }
  })
}

actions.removeRequirementsResources = (requirements, resources) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: url(['apiv2_workspace_requirements_resources_remove', {requirements: requirements.id}], {ids: resources.map(r => r.id)}),
      request: {
        method: 'DELETE'
      },
      success: (response, dispatch) => dispatch(actions.loadRequirements(response))
    }
  })
}
