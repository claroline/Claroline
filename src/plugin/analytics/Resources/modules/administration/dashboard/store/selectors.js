import {createSelector} from 'reselect'

const STORE_NAME = 'dashboard'
const LIST_NAME = STORE_NAME + '.securityLogs'
const MESSAGE_NAME = STORE_NAME + '.messageLogs'

const store = (state) => state[STORE_NAME]

const count = createSelector(
  [store],
  (store) => store.count
)

export const selectors = {
  STORE_NAME,
  LIST_NAME,
  MESSAGE_NAME,

  store,
  count
}
