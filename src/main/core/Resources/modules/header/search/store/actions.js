import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const SEARCH_RESULTS_LOAD = 'SEARCH_RESULTS_LOAD'
export const SEARCH_SET_FETCHING = 'SEARCH_SET_FETCHING'

export const actions = {}

actions.setFetching = makeActionCreator(SEARCH_SET_FETCHING)
actions.loadResults = makeActionCreator(SEARCH_RESULTS_LOAD, 'results')

actions.search = (currentSearch) => ({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_search', {search: currentSearch}],
    before: (dispatch) => dispatch(actions.setFetching()),
    success: (response, dispatch) => dispatch(actions.loadResults(response))
  }
})
