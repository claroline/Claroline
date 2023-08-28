import {route as workspaceRoute} from '#/main/core/workspace/routing'

function route(team, basePath = null) {
  if (basePath) {
    return basePath + '/teams/' + team.id
  }

  return workspaceRoute(team.workspace, 'community')+`/teams/${team.id}`
}

export {
  route
}
