import {createSelector} from 'reselect'

const STORE_NAME = 'notifications'

const store = (state) => state[STORE_NAME]

const count = createSelector(
  [store],
  (store) => store.count
)

const notifications = createSelector(
  [store],
  (store) => store.notifications
)

export const selectors = {
  STORE_NAME,
  count,
  notifications
}
