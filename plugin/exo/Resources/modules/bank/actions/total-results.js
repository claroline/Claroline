import {makeActionCreator} from '#/main/core/utilities/redux'

export const TOTAL_RESULTS_CHANGE = 'TOTAL_RESULTS_CHANGE'

export const actions = {}

actions.changeTotalResults = makeActionCreator(TOTAL_RESULTS_CHANGE, 'totalResults')
