import {createSelector} from 'reselect'

const STORE_NAME = 'messagesMenu'

const store = (state) => state[STORE_NAME]

const count = createSelector(
  [store],
  (store) => store.count
)

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const results = createSelector(
  [store],
  (store) => store.results
)

export const selectors = {
  STORE_NAME,

  store,
  count,
  loaded,
  results
}
