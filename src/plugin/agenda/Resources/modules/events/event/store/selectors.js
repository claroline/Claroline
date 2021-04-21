import {createSelector} from 'reselect'

const STORE_NAME = 'eventDetails'

const store = (state) => state[STORE_NAME]

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const event = createSelector(
  [store],
  (store) => store.event
)

export const selectors = {
  STORE_NAME,

  event,
  loaded
}
