import get from 'lodash/get'
import {createSelector} from 'reselect'

// retrieves a list instance in the store
const list = (state, listName) => get(state, listName)

// access list data
const loaded       = (listState) => listState.loaded
const invalidated  = (listState) => listState.invalidated
const data         = (listState) => listState.data
const totalResults = (listState) => listState.totalResults
const filters      = (listState) => listState.filters || []
const sortBy       = (listState) => listState.sortBy || {}
const selected     = (listState) => listState.selected || []
const pageSize     = (listState) => listState.pageSize || -1
const currentPage  = (listState) => listState.page || 0

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
  const currentFilters = filters(listState)
  if (0 < currentFilters.length) {
    currentFilters.map(filter => {
      queryParams.push(`filters[${filter.property}]=${encodeURIComponent(filter.value)}`)
    }).join('&')
  }

  // add sort by
  const currentSort = sortBy(listState)
  if (currentSort.property && 0 !== currentSort.direction) {
    queryParams.push(`sortBy=${-1 === currentSort.direction ? '-':''}${currentSort.property}`)
  }

  // add pagination
  const page = currentPage(listState)
  const limit = pageSize(listState)
  queryParams.push(`page=${page}&limit=${limit}`)

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

export const select = {
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
  queryString
}
