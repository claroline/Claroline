import get from 'lodash/get'
import {createSelector} from 'reselect'

import {selectors as paginationSelectors} from '#/main/app/content/pagination/store/selectors'
import {selectors as searchSelectors} from '#/main/app/content/search/store/selectors'

// retrieves a list instance in the store
const list = (state, listName) => get(state, listName)

// access list data
const loaded       = (listState) => listState.loaded
const invalidated  = (listState) => listState.invalidated
const data         = (listState) => listState.data
const totalResults = (listState) => listState.totalResults
const selected     = (listState) => listState.selected || []
const sortBy       = (listState) => listState.sortBy || {}

const pagination   = (listState) => listState.pagination
const pageSize     = (listState) => paginationSelectors.pageSize(pagination(listState)) // for retro-compatibility
const currentPage  = (listState) => paginationSelectors.currentPage(pagination(listState)) // for retro-compatibility

const filters      = (listState) => searchSelectors.filters(listState.filters) // for retro-compatibility

const sortByQueryString = createSelector(
  [sortBy],
  (sortBy) => {
    if (sortBy.property && 0 !== sortBy.direction) {
      return `sortBy=${-1 === sortBy.direction ? '-':''}${sortBy.property}`
    }

    return ''
  }
)

/**
 * Creates an URL query sting based on the list current config.
 * Format: ?filters[FILTER_NAME]=FILTER_VALUE&sortBy=SORT_NAME&page=0&limit=-1
 *
 * @param {object} listState
 *
 * @returns {string}
 */
function queryString(listState) {
  const queryParams = []

  // add filters
  const currentFilters = searchSelectors.queryString(listState.filters)
  if (0 < currentFilters.length) {
    queryParams.push(currentFilters)
  }

  // add sort by
  const currentSort = sortByQueryString(listState)
  if (0 < currentSort.length) {
    queryParams.push(currentSort)
  }

  // add pagination
  queryParams.push(
    paginationSelectors.queryString(pagination(listState))
  )

  return '?' + queryParams.join('&')
}

const selectedFull = createSelector(
  [data, selected],
  (data, selected) => data.filter(d => selected.indexOf(d.id) > -1)
)

const pages = createSelector(
  [totalResults, pageSize],
  (totalResults, pageSize) => {
    if(-1 === pageSize) {
      return 1
    }

    return Math.ceil(totalResults / pageSize)
  }
)

let selectors, select
selectors = select = {
  list,
  loaded,
  invalidated,
  data,
  totalResults,
  filters,
  sortBy,
  selected,
  selectedFull,
  currentPage,
  pageSize,
  pages,
  sortByQueryString,
  queryString
}

export {
  select, // for retro-compatibility
  selectors
}