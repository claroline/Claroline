import {createSelector} from 'reselect'
import isEmpty from 'lodash/isEmpty'

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

const empty = createSelector(
  [results],
  (results) => {
    if (isEmpty(results)) {
      return true
    }

    return -1 === Object.keys(results)
      .findIndex(resultType => !isEmpty(results[resultType]))
  }
)

export const selectors = {
  STORE_NAME,

  search,
  fetching,
  results,
  empty
}
