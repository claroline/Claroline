import {makeActionCreator} from './../../utils/actions'

export const SORT_BY_UPDATE = 'SORT_BY_UPDATE'

export const actions = {}

actions.updateSortBy = makeActionCreator(SORT_BY_UPDATE, 'property')
