import {createSelector} from 'reselect'

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

const courseStats = createSelector(
  [catalog],
  (catalog) => catalog.courseStats
)

const participantsView = createSelector(
  [catalog],
  (catalog) => catalog.participantsView
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
  courseStats,
  participantsView
}
