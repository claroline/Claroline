
function route(basePath, course, session = null) {
  const coursePath = `${basePath}/${course.slug}`

  if (session) {
    return `${coursePath}/${session.id}`
  }

  return coursePath
}

export {
  route
}
