import {createSelector} from 'reselect'

const getProperty = (state) => state.property // FIX ME
const getSortBy = (state) => state.sortBy

export const getPropertySortDirection =  createSelector(
  [getProperty, getSortBy],
  (property, sortBy) => {
    return property === sortBy.property ? sortBy.direction : 0
  }
)