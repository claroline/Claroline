import {makeActionCreator} from './../../utils/actions'

export const PAGE_SIZE_UPDATE = 'PAGE_SIZE_UPDATE'
export const PAGE_CHANGE      = 'PAGE_CHANGE'
export const PAGE_NEXT        = 'PAGE_NEXT'
export const PAGE_PREVIOUS    = 'PAGE_PREVIOUS'

export const actions = {}

actions.changePage = makeActionCreator(PAGE_CHANGE, 'page')
actions.nextPage = makeActionCreator(PAGE_NEXT)
actions.previousPage = makeActionCreator(PAGE_PREVIOUS)
actions.updatePageSize = makeActionCreator(PAGE_SIZE_UPDATE, 'pageSize')
