import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const SEARCH_RESULTS_LOAD = 'SEARCH_RESULTS_LOAD'
//export const SEARCH_RESULTS_LOAD = 'SEARCH_RESULTS_LOAD'

export const actions = {}

actions.setFetching = makeActionCreator(SEARCH_RESULTS_LOAD, 'fetching')
actions.loadResults = makeActionCreator(SEARCH_RESULTS_LOAD, 'results')

actions.search = (/*currentSearch*/) => ({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_search'],
    success: (response, dispatch) => dispatch(actions.loadResults(response))
  }
})
