import {createSelector} from 'reselect'
import isEmpty from 'lodash/isEmpty'

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
  (store) => {
    if (!isEmpty(store.registration.users)) {
      return store.registration.users[0]
    }

    return store.registration.groups[0]
  }
)

export const selectors = {
  STORE_NAME,
  event,
  loaded,
  registration
}
