import {makeActionCreator} from '#/main/core/utilities/redux'

import {REQUEST_SEND} from './../../api/actions'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {actions as questionActions} from './questions'
import {actions as totalResultsActions} from './total-results'

export const SEARCH_CLEAR_FILTERS  = 'SEARCH_CLEAR_FILTERS'
export const SEARCH_CHANGE_FILTERS = 'SEARCH_CHANGE_FILTERS'

export const actions = {}

actions.fetchQuestions = (filters, pagination = {}, sortBy = {}) => ({
  [REQUEST_SEND]: {
    route: ['question_search'],
    request: {
      method: 'POST',
      body: JSON.stringify({
        filters,
        pagination,
        sortBy
      })
    },
    success: (searchResults, dispatch) => {
      // Update total results
      dispatch(totalResultsActions.changeTotalResults(searchResults.totalResults))
      // Update questions list
      dispatch(questionActions.setQuestions(searchResults.questions))
    }
  }
})

actions.changeFilters = makeActionCreator(SEARCH_CHANGE_FILTERS, 'filters')

actions.search = (filters, pagination = {}, sortBy = {}) => {
  return (dispatch) => {
    // Close search modal
    dispatch(modalActions.fadeModal())

    // Update filters
    dispatch(actions.changeFilters(filters))

    // Fetch new questions list
    return dispatch(actions.fetchQuestions(filters, pagination, sortBy))
  }
}

actions.clearFilters = (pagination = {}, sortBy = {}) => {
  return (dispatch) => {
    // Close search modal
    dispatch(modalActions.fadeModal())

    // Update filters
    dispatch(actions.changeFilters({}))

    // Fetch new questions list
    return dispatch(actions.fetchQuestions({}, pagination, sortBy))
  }
}
