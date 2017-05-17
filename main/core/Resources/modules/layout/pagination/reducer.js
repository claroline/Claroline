import {makeReducer} from '#/main/core/utilities/redux'

import {DEFAULT_PAGE_SIZE} from '#/main/core/layout/pagination/default'
import {
  PAGE_CHANGE,
  PAGE_SIZE_UPDATE
} from '#/main/core/layout/pagination/actions'

function changePage(paginationState, action = {}) {
  return Object.assign({}, paginationState, {
    current: action.page
  })
}

function updatePageSize(paginationState, action = {}) {
  return {
    current: 0, // todo : find a better way to handle this
    pageSize: action.pageSize
  }
}

const reducer = makeReducer({
  current: 0,
  pageSize: DEFAULT_PAGE_SIZE
}, {
  [PAGE_CHANGE]: changePage,
  [PAGE_SIZE_UPDATE]: updatePageSize
})

export {reducer}
