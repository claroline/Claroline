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

const registration = createSelector(
  [store],
  (store) => store.registration
)

export const selectors = {
  STORE_NAME,
  event,
  loaded,
  registration
}
