import get from 'lodash/get'

function getInfo(course, session, path) {
  if (session && undefined !== get(session, path)) {
    return get(session, path)
  } else if (get(course, path)) {
    return get(course, path)
  }

  return null
}

function isFullyRegistered(registration) {
  if (registration) {
    if (registration.user) {
      return registration.confirmed && registration.validated
    }

    return true
  }

  return false
}

function isFull(session) {
  if (get(session, 'restrictions.users')) {
    return get(session, 'restrictions.users') <= get(session, 'participants.learners')
  }

  return false
}

export {
  getInfo,
  isFull,
  isFullyRegistered
}
