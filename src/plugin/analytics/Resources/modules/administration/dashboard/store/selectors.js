import {createSelector} from 'reselect'

const STORE_NAME = 'dashboard'

const store = (state) => state[STORE_NAME]

const count = createSelector(
  [store],
  (store) => store.count
)

export const selectors = {
  STORE_NAME,

  store,
  count
}
