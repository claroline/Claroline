import {createSelector} from 'reselect'

const getTotalResults = (state) => state.totalResults
const getPagination   = (state) => state.pagination

const getPageSize = createSelector(
  [getPagination],
  (pagination) => pagination.pageSize
)

const countPages = createSelector(
  [getTotalResults, getPageSize],
  (totalResults, pageSize) => {
    if (-1 === pageSize) {
      return 1
    }

    const rest = totalResults % pageSize
    const nbPages = (totalResults - rest) / pageSize

    return nbPages + (rest > 0 ? 1 : 0)
  }
)

export const select = {
  getTotalResults,
  getPagination,
  getPageSize,
  countPages
}
