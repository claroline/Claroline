import isArray from 'lodash/isArray'

import {makeReducer} from '#/main/core/utilities/redux'

import {
  SEARCH_CHANGE_FILTERS,
  SEARCH_CLEAR_FILTERS
} from './../actions/search'

function changeFilters(state, action) {
  let newFilters = {}

  for (let filter in action.filters) {
    if (action.filters.hasOwnProperty(filter)) {
      let filterValue = action.filters[filter]
      if (!!filterValue && (!isArray(filterValue) || 0 !== filterValue.length)) {
        newFilters[filter] = action.filters[filter]
      }
    }
  }
  
  return newFilters
}

function clearFilters() {
  return {}
}

const searchReducer = makeReducer({}, {
  [SEARCH_CHANGE_FILTERS]: changeFilters,
  [SEARCH_CLEAR_FILTERS]: clearFilters
})

export default searchReducer
