import {createSelector} from 'reselect'

const canEdit = state => state.canEdit === 1
const disableRegistration = state => state.disableRegistration === 1
const sessions = state => state.sessions
const sessionId = state => state.sessionId
const events = state => state.events
const currentEvent = state => state.currentEvent.data
const currentParticipants = state => state.currentEvent.participants
const viewMode = state => state.viewMode
const currentError = state => state.currentError
const eventsUsers = state => state.eventsUsers

const currentSession = createSelector(
  [sessions, sessionId],
  (sessions, sessionId) => sessions.find(s => s.id === sessionId)
)

const sessionEvents = createSelector(
  [events],
  (events) => events.data
)

const sessionEventsTotal = createSelector(
  [events],
  (events) => events.totalResults
)

const eventFormData = state => state.eventForm

export const selectors = {
  canEdit,
  disableRegistration,
  sessionEvents,
  sessionEventsTotal,
  eventFormData,
  currentSession,
  currentEvent,
  currentParticipants,
  viewMode,
  currentError,
  eventsUsers
}