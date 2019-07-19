import {createSelector} from 'reselect'

const STORE_NAME = 'search'

const store = (state) => state[STORE_NAME]

const search = createSelector(
  [store],
  (store) => store.search
)

const fetching = createSelector(
  [store],
  (store) => store.fetching
)

const results = createSelector(
  [store],
  (store) => store.results
)

export const selectors = {
  STORE_NAME,

  search,
  fetching,
  results
}
