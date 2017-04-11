import { combineReducers } from 'redux'

import {reducer as modalReducer} from '#/main/core/layout/modal/reducer'
import {reducers as apiReducers} from './../../api/reducers'
import questionsReducer    from './questions'
import selectReducer       from './select'
import sortByReducer       from './sort-by'
import paginationReducer   from './pagination'
import searchReducer       from './search'
import totalResultsReducer from './total-results'

export const bankApp = combineReducers({
  modal: modalReducer,
  currentRequests: apiReducers.currentRequests,
  questions: questionsReducer,
  selected: selectReducer,
  sortBy: sortByReducer,
  pagination: paginationReducer,
  search: searchReducer,
  totalResults: totalResultsReducer,
  currentUser: (state = null) => state
})
