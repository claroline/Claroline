import {createSelector} from 'reselect'

// check enabled list features
const isAsync      = (listState) => typeof listState.fetchUrl !== 'undefined'
const isFilterable = (listState) => typeof listState.filters !== 'undefined'
const isSortable   = (listState) => typeof listState.sortBy !== 'undefined'
const isSelectable = (listState) => typeof listState.selected !== 'undefined'
const isPaginated  = (listState) => typeof listState.page !== 'undefined' && listState.pageSize !== 'undefined'

// access list data
const fetchUrl     = (listState) => listState.fetchUrl
const data         = (listState) => listState.data
const totalResults = (listState) => listState.totalResults
const filters      = (listState) => listState.filters || []
const sortBy       = (listState) => listState.sortBy || {}
const selected     = (listState) => listState.selected || []
const pageSize     = (listState) => listState.pageSize || -1
const currentPage  = (listState) => listState.page || 0

// ATTENTION : we assume all data object have an unique `id` prop
// maybe one day we will decorate data on load with ids if missing...
const selectedRows = createSelector(
  [data, selected],
  (data, selected) => selected.map(id => data.find(row => id === row.id))
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
  const currentFilters = filters(listState)
  if (0 < currentFilters.length) {
    currentFilters.map(filter => {
      let value = filter.value.constructor.name === 'Moment' ? filter.value.unix(): filter.value
      queryParams.push(`filters[${filter.property}]=${encodeURIComponent(value)}`)
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

export const select = {
  isAsync,
  isFilterable,
  isSortable,
  isSelectable,
  isPaginated,
  fetchUrl,
  data,
  totalResults,
  filters,
  sortBy,
  selected,
  currentPage,
  pageSize,
  selectedRows,
  queryString
}
