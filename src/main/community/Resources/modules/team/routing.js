import {route as toolRoute} from '#/main/core/tool/routing'

function route(team, basePath = null) {
  if (basePath) {
    return basePath + '/teams/' + team.id
  }

  return toolRoute('community') + '/teams/' + team.id
}

function teamRoute(team) {
  return '/desktop/workspaces/open/' + team.workspace.slug + '/community' + '/teams/' + team.id
}

export {
  route,
  teamRoute
}
