import {createSelector} from 'reselect'

const list = (state) => state.list

const filters = createSelector(
  [list],
  (list) => list.filters
)

const sortBy = createSelector(
  [list],
  (list) => list.sortBy
)

const selected = createSelector(
  [list],
  (list) => list.selected
)

export const select = {
  list,
  filters,
  sortBy,
  selected
}
