import {createSelector} from 'reselect'

const pagination = (state) => state.pagination

const pageSize = createSelector(
  [pagination],
  (pagination) => pagination.pageSize
)

const current = createSelector(
  [pagination],
  (pagination) => pagination.current
)

export const select = {
  pagination,
  pageSize,
  current
}
