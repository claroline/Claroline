import {createSelector} from 'reselect'

const STORE_NAME = 'course'
const FORM_NAME = STORE_NAME + '.courseForm'

const store = (state) => state[STORE_NAME] || {}

const course = createSelector(
  [store],
  (store) => store.course
)

const sessionRegistrations = createSelector(
  [store],
  (store) => store.courseRegistrations
)

const availableSessions = createSelector(
  [store],
  (store) => store.courseAvailableSessions
)

const defaultSession = createSelector(
  [store],
  (store) => store.courseDefaultSession
)

const activeSession = createSelector(
  [store],
  (store) => store.courseActiveSession
)

const courseStats = createSelector(
  [store],
  (store) => store.courseStats
)

const participantsView = createSelector(
  [store],
  (store) => store.participantsView
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,

  course,
  activeSession,
  defaultSession,
  availableSessions,
  sessionRegistrations,
  courseStats,
  participantsView
}
