import {createSelector} from 'reselect'

const STORE_NAME = 'messaging'

const store = (state) => state[STORE_NAME]

const message = createSelector(
  [store],
  (store) => store.currentMessage
)

export const selectors = {
  STORE_NAME,
  message
}
