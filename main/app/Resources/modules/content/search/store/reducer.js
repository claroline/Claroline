import cloneDeep from 'lodash/cloneDeep'

import {makeInstanceReducer} from '#/main/app/store/reducer'

import {
  SEARCH_FILTER_ADD,
  SEARCH_FILTER_REMOVE,
  SEARCH_FILTER_RESET
} from '#/main/app/content/search/store/actions'

const reducer = makeInstanceReducer([], {
  [SEARCH_FILTER_RESET]: (state, action) => action.filters,

  [SEARCH_FILTER_ADD]: (state, action) => {
    const newFilters = cloneDeep(state)

    const existingFilter = newFilters.find(filter => filter.property === action.property)
    if (existingFilter) {
      existingFilter.value = action.value
    } else {
      newFilters.push({
        property: action.property,
        value: action.value,
        locked: action.locked
      })
    }

    return newFilters
  },

  [SEARCH_FILTER_REMOVE]: (state, action) => {
    const newFilters = state.slice(0)
    const pos = state.indexOf(action.filter)
    if (-1 !== pos) {
      newFilters.splice(pos, 1)
    }

    return newFilters
  }
})

export {
  reducer
}
