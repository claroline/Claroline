
function route(course, session = null) {
  const coursePath = `/desktop/trainings/catalog/${course.slug}`

  if (session) {
    return `${coursePath}/${session.id}`
  }

  return coursePath
}

export {
  route
}
