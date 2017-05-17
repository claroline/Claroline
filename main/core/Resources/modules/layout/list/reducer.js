import cloneDeep from 'lodash/cloneDeep'

import {makeReducer, combineReducers} from '#/main/core/utilities/redux'

import {
  LIST_FILTER_ADD,
  LIST_FILTER_REMOVE,
  LIST_SORT_UPDATE,
  LIST_RESET_SELECT,
  LIST_TOGGLE_SELECT,
  LIST_TOGGLE_SELECT_ALL
} from '#/main/core/layout/list/actions'

function addFilter(state, action = {}) {
  const newFilters = cloneDeep(state)

  const existingFilter = newFilters.find(filter => filter.property === action.property)
  if (existingFilter) {
    existingFilter.value = action.value
  } else {
    newFilters.push({
      property: action.property,
      value: action.value
    })
  }

  return newFilters
}

function removeFilter(state, action = {}) {
  const newFilters = state.slice(0)
  const pos = state.indexOf(action.filter)
  if (-1 !== pos) {
    newFilters.splice(pos, 1)
  }

  return newFilters
}

function updateSort(state, action = {}) {
  let direction = 1
  if (state.property === action.property) {
    if (1 === state.direction) {
      direction = -1
    } else if (-1 === state.direction) {
      direction = 0
    }
    else {
      direction = 1
    }
  }

  return {
    property: action.property,
    direction: direction
  }
}

function resetSelect() {
  return []
}

function toggleSelectAll(state, action = {}) {
  return 0 < state.length ? [] : action.items
}

function toggleSelect(state, action = {}) {
  const selected = state.slice(0)

  const itemPos = state.indexOf(action.id)
  if (-1 === itemPos) {
    // Item not selected
    selected.push(action.id)
  } else {
    // Item selected
    selected.splice(itemPos, 1)
  }

  return selected
}

const filterReducer = makeReducer([], {
  [LIST_FILTER_ADD]: addFilter,
  [LIST_FILTER_REMOVE]: removeFilter
})

const sortReducer = makeReducer({
  property: null,
  direction: 0
}, {
  [LIST_SORT_UPDATE]: updateSort
})

const selectReducer = makeReducer([], {
  [LIST_RESET_SELECT]: resetSelect,
  [LIST_TOGGLE_SELECT]: toggleSelect,
  [LIST_TOGGLE_SELECT_ALL]: toggleSelectAll
})

const makeListReducer = (filterable = true, sortable = true, selectable = true) => {
  const reducer = {}

  if (filterable) {
    reducer.filters = filterReducer
  }

  if (sortable) {
    reducer.sortBy = sortReducer
  }

  if (selectable) {
    reducer.selected = selectReducer
  }

  return combineReducers(reducer)
}

export {
  makeListReducer
}
