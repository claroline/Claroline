import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store/actions'

import {selectors} from '#/main/evaluation/tools/evaluation/store/selectors'

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

actions.addRequiredResources = (workspaceId, resources) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_workspace_required_resource_add', {workspace: workspaceId}], {ids: resources.map(r => r.id)}),
    request: {
      method: 'PATCH'
    },
    success: () => dispatch(listActions.invalidateData(selectors.STORE_NAME+'.requiredResources'))
  }
})
