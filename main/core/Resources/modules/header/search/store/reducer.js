import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {SEARCH_RESULTS_LOAD} from '#/main/core/header/search/store/actions'

export const reducer = combineReducers({
  fetching: makeReducer(false, {
    [SEARCH_RESULTS_LOAD]: () => false
  }),

  results: makeReducer([], {
    [SEARCH_RESULTS_LOAD]: (state, action) => action.results
  })
})
