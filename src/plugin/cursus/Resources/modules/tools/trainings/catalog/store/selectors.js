import {createSelector} from 'reselect'
import isEmpty from 'lodash/isEmpty'

const STORE_NAME = 'trainingCatalog'
const LIST_NAME = STORE_NAME + '.courses'
const FORM_NAME = STORE_NAME + '.courseForm'

const catalog = (state) => state[STORE_NAME]

const course = createSelector(
  [catalog],
  (catalog) => catalog.course
)

const sessionRegistrations = createSelector(
  [catalog],
  (catalog) => catalog.courseRegistrations
)

const availableSessions = createSelector(
  [catalog],
  (catalog) => catalog.courseAvailableSessions
)

const defaultSession = createSelector(
  [catalog],
  (catalog) => catalog.courseDefaultSession
)

const activeSession = createSelector(
  [catalog],
  (catalog) => catalog.courseActiveSession
)

const activeSessionRegistration = createSelector(
  [activeSession, sessionRegistrations],
  (activeSession, sessionRegistrations) => {
    let activeRegistration = null
    if (activeSession) {
      if (sessionRegistrations.users) {
        activeRegistration = sessionRegistrations.users.find(registration => activeSession.id === registration.session.id)
      }

      if (!activeRegistration && sessionRegistrations.groups) {
        activeRegistration = sessionRegistrations.groups.find(registration => activeSession.id === registration.session.id)
      }
    }

    return activeRegistration
  }
)

const courseRegistration = createSelector(
  [sessionRegistrations],
  (sessionRegistrations) => {
    if (!isEmpty(sessionRegistrations.pending)) {
      return sessionRegistrations.pending[0]
    }

    return null
  }
)

export const selectors = {
  STORE_NAME,
  LIST_NAME,
  FORM_NAME,

  course,
  activeSession,
  defaultSession,
  availableSessions,
  sessionRegistrations,
  activeSessionRegistration,
  courseRegistration
}