import {API_REQUEST} from '#/main/app/api'

export const actions = {}

actions.fetchEvaluation = (workspaceId, userId) => ({
  [API_REQUEST] : {
    silent: true,
    url: ['apiv2_workspace_evaluation_get', {workspace: workspaceId, user: userId}]
  }
})
