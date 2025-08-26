import {route as workspaceRoute} from '#/main/core/workspace/routing'

function route(sequence, step = null) {
  const sequencePath = `${workspaceRoute(sequence.workspace, 'progression')}/sequences/${sequence.id}`

  if (step) {
    return `${sequencePath}/play/${step.id}`
  }

  return sequencePath
}

export {
  route
}
