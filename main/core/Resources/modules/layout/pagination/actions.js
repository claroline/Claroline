import {makeActionCreator} from '#/main/core/utilities/redux'

export const PAGE_SIZE_UPDATE = 'PAGE_SIZE_UPDATE'
export const PAGE_CHANGE      = 'PAGE_CHANGE'

export const actions = {}

actions.changePage = makeActionCreator(PAGE_CHANGE, 'page')
actions.updatePageSize = makeActionCreator(PAGE_SIZE_UPDATE, 'pageSize')
