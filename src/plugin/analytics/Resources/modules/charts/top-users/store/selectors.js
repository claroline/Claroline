import {createSelector} from 'reselect'

const STORE_NAME = 'topUsersChart'

const store = (state) => state[STORE_NAME]

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const data = createSelector(
  [store],
  (store) => store.data
)

export const selectors = {
  STORE_NAME,

  loaded,
  data
}
