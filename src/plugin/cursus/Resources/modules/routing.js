import {route as toolRoute} from '#/main/core/tool/routing'

function route(course, session = null, basePath = null) {
  let coursePath
  if (basePath) {
    coursePath = `${basePath}/catalog/${course.slug}`
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
