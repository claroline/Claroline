import {createSelector} from 'reselect'

const STORE_NAME = 'trainingEventCurrent'

const store = (state) => state[STORE_NAME] || {}

const event = createSelector(
  [store],
  (store) => store.event
)

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const registrations = createSelector(
  [store],
  (store) => store.registrations
)

export const selectors = {
  STORE_NAME,
  event,
  loaded,
  registrations
}
