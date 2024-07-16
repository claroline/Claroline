import {route as toolRoute} from '#/main/core/tool/routing'

function route(course, session = null, basePath = null) {
  let coursePath

  if (basePath) {
    coursePath = basePath
  } else {
    coursePath = toolRoute('trainings')
  }

  coursePath = `${coursePath}/course/${course.slug}`

  if (session) {
    return `${coursePath}/${session.id}`
  }

  return coursePath
}

export {
  route
}
