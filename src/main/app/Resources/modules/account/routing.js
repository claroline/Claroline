
function route(sectionName) {
  if (sectionName) {
    return `/account/${sectionName}`
  }

  return '/account'
}

export {
  route
}
