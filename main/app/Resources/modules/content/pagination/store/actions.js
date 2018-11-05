import {makeInstanceActionCreator} from '#/main/app/store/actions'

export const PAGINATION_SIZE_UPDATE = 'PAGINATION_SIZE_UPDATE'
export const PAGINATION_PAGE_CHANGE = 'PAGINATION_PAGE_CHANGE'

export const actions = {}

actions.changePage = makeInstanceActionCreator(PAGINATION_PAGE_CHANGE, 'page')
actions.updatePageSize = makeInstanceActionCreator(PAGINATION_SIZE_UPDATE, 'pageSize')
