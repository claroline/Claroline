import {createSelector} from 'reselect'

const STORE_NAME = 'bbbIntegration'

const store = (state) => state[STORE_NAME]

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const servers = createSelector(
  [store],
  (store) => store.servers
)

const allowRecords = createSelector(
  [store],
  (store) => store.allowRecords
)

const maxMeetings = createSelector(
  [store],
  (store) => store.maxMeetings
)

const maxMeetingParticipants = createSelector(
  [store],
  (store) => store.maxMeetingParticipants
)

const maxParticipants = createSelector(
  [store],
  (store) => store.maxParticipants
)

const activeMeetingsCount = createSelector(
  [store],
  (store) => store.activeMeetingsCount
)

const participantsCount = createSelector(
  [store],
  (store) => store.participantsCount
)

export const selectors = {
  STORE_NAME,

  loaded,
  maxMeetings,
  maxMeetingParticipants,
  maxParticipants,
  activeMeetingsCount,
  participantsCount,
  servers,
  allowRecords
}
