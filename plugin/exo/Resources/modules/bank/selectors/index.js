import {createSelector} from 'reselect'
import size from 'lodash/size'

const modal = state => state.modal
const filters = state => state.search
const selected = state => state.selected
const currentUser = state => state.currentUser
const countFilters = createSelector(
  [filters],
  (filters) => size(filters)
)

export const select = {
  modal,
  filters,
  selected,
  currentUser,
  countFilters
}
