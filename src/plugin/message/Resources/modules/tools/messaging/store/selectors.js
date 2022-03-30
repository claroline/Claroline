import {createSelector} from 'reselect'

const STORE_NAME = 'messaging'

const store = (state) => state[STORE_NAME]

const message = createSelector(
  [store],
  (store) => store.currentMessage
)

const mailNotified = createSelector(
  [store],
  (store) => store.mailNotified
)

export const selectors = {
  STORE_NAME,
  message,
  mailNotified
}
