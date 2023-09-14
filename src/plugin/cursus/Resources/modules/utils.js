import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

function getInfo(course, session, path) {
  if (session && undefined !== get(session, path)) {
    return get(session, path)
  } else if (get(course, path)) {
    return get(course, path)
  }

  return null
}

function isFull(session) {
  if (get(session, 'restrictions.users')) {
    return get(session, 'restrictions.users') <= get(session, 'participants.learners')
  }

  return false
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

function getCourseRegistration(registrations = {}) {
  if (!isEmpty(registrations.pending)) {
    return registrations.pending[0]
  }

  return null
}

function getSessionRegistration(session, registrations = {}) {
  let registration = null

  if (registrations.users) {
    registration = registrations.users.find(registration => session.id === registration.session.id)
  }

  if (!registration && registrations.groups) {
    registration = registrations.groups.find(registration => session.id === registration.session.id)
  }

  return registration
}

function isRegistered(session, registrations= {}) {
  const registration = getSessionRegistration(session, registrations)

  return !isEmpty(registration) && isFullyRegistered(registration)
}

function canSelfRegister(course, session, registrations) {
  return getInfo(course, session, 'registration.selfRegistration')
    && !getInfo(course, session, 'registration.autoRegistration')
    && !isRegistered(session, registrations)
    && (getInfo(course, session, 'registration.pendingRegistrations') || !isFull(session))
}

export {
  getInfo,
  isFull,
  getSessionRegistration,
  getCourseRegistration,
  isFullyRegistered,
  isRegistered,
  canSelfRegister
}
