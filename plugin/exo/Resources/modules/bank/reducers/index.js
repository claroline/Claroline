import { combineReducers } from 'redux'

import {reducers as apiReducers} from './../../api/reducers'
import {reduceModal}       from './../../modal/reducer'
import questionsReducer    from './questions'
import selectReducer       from './select'
import sortByReducer       from './sort-by'
import paginationReducer   from './pagination'
import searchReducer       from './search'
import totalResultsReducer from './total-results'

export const bankApp = combineReducers({
  modal: reduceModal,
  currentRequests: apiReducers.currentRequests,
  questions: questionsReducer,
  selected: selectReducer,
  sortBy: sortByReducer,
  pagination: paginationReducer,
  search: searchReducer,
  totalResults: totalResultsReducer,
  currentUser: (state = null) => state
})
