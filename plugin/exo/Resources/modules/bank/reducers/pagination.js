import {makeReducer} from '#/main/core/utilities/redux'
import {update} from './../../utils/utils'

import {
  PAGE_NEXT,
  PAGE_PREVIOUS,
  PAGE_CHANGE,
  PAGE_SIZE_UPDATE
} from '#/main/core/layout/pagination/actions'

function nextPage(paginationState) {
  return update(paginationState, {current: {$set: paginationState.current + 1}})
}

function previousPage(paginationState) {
  return update(paginationState, {current: {$set: paginationState.current - 1}})
}

function changePage(paginationState, action = {}) {
  return update(paginationState, {current: {$set: action.page}})
}

function updatePageSize(paginationState, action = {}) {
  // TODO : manage the case when the current page no longer exists
  return update(paginationState, {pageSize: {$set: action.pageSize}})
}

const paginationReducer = makeReducer({
  current: 0,
  pageSize: 10
}, {
  [PAGE_NEXT]: nextPage,
  [PAGE_PREVIOUS]: previousPage,
  [PAGE_CHANGE]: changePage,
  [PAGE_SIZE_UPDATE]: updatePageSize
})

export default paginationReducer
