import {createSelector} from 'reselect'

const STORE_NAME = 'messagesModal'

const store = (state) => state[STORE_NAME]

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
  loaded,
  results
}
