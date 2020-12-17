import {createSelector} from 'reselect'

const STORE_NAME = 'tasks_scheduling'

const store = (state) => state[STORE_NAME]

const isCronConfigured = createSelector(
  [store],
  (store) => store.isCronConfigured
)

export const selectors = {
  STORE_NAME,
  store,
  isCronConfigured
}
