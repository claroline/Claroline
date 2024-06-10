import {route as toolRoute} from '#/main/core/tool/routing'

function route(course, session = null, workspace = null) {
  let coursePath

  if (workspace) {
    coursePath = `/workspace/${workspace.slug}/training_events/about/${course.slug}`
  } else {
    coursePath = `${toolRoute('trainings') }/catalog/${course.slug}`
  }

  if (session) {
    return `${coursePath}/${session.id}`
  }

  return coursePath
}

export {
  route
}
