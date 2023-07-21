import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  SEARCH_RESULTS_LOAD,
  SEARCH_SET_FETCHING
} from '#/main/core/header/search/modals/search/store/actions'

export const reducer = combineReducers({
  fetching: makeReducer(false, {
    [SEARCH_SET_FETCHING]: () => true,
    [SEARCH_RESULTS_LOAD]: () => false
  }),

  results: makeReducer({}, {
    [SEARCH_RESULTS_LOAD]: (state, action) => action.results
  })
})
