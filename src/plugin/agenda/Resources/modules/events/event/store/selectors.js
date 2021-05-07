import {createSelector} from 'reselect'

const STORE_NAME = 'eventDetails'
const LIST_NAME = STORE_NAME + '.participants'

const store = (state) => state[STORE_NAME] || {}

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
  LIST_NAME,

  event,
  loaded
}
