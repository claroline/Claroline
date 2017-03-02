import {makeActionCreator} from './../../utils/actions'

export const TOTAL_RESULTS_CHANGE = 'TOTAL_RESULTS_CHANGE'

export const actions = {}

actions.changeTotalResults = makeActionCreator(TOTAL_RESULTS_CHANGE, 'totalResults')
