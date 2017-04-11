import {makeActionCreator} from '#/main/core/utilities/redux'

export const SORT_BY_UPDATE = 'SORT_BY_UPDATE'

export const actions = {}

actions.updateSortBy = makeActionCreator(SORT_BY_UPDATE, 'property')
