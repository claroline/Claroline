
function route(basePath, course, session = null) {
  const coursePath = `${basePath}/catalog/${course.slug}`

  if (session) {
    return `${coursePath}/${session.id}`
  }

  return coursePath
}

export {
  route
}
