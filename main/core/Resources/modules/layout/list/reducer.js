import cloneDeep from 'lodash/cloneDeep'
import merge from 'lodash/merge'

import {makeReducer, reduceReducers, combineReducers} from '#/main/core/utilities/redux'

import {constants} from '#/main/core/layout/list/constants'
import {
  LIST_FILTER_ADD,
  LIST_FILTER_REMOVE,
  LIST_SORT_UPDATE,
  LIST_RESET_SELECT,
  LIST_TOGGLE_SELECT,
  LIST_TOGGLE_SELECT_ALL,
  LIST_DATA_LOAD,
  LIST_DATA_DELETE,
  LIST_PAGE_CHANGE,
  LIST_PAGE_SIZE_UPDATE
} from '#/main/core/layout/list/actions'

/**
 * Reduces the API url from where the data come from.
 * It's used to refresh async data lists.
 *
 * This is not supposed to change at runtime. We store it in redux for the sake of simplicity.
 */
const fetchUrlReducer = (state = null) => state

/**
 * Reduces the API url from where the data come from.
 * It's used to delete elements from data lists.
 *
 * This is not supposed to change at runtime. We store it in redux for the sake of simplicity.
 */
const deleteReducer = (state = null) => state

/**
 * Reduces list data items.
 */
const dataReducer = makeReducer([], {
  [LIST_DATA_LOAD]: (state, action = {}) => action.data,
  [LIST_DATA_DELETE]: (state, action = {}) => {
    const items = cloneDeep(state)

    action.items.forEach(toRemove => {
      const itemIndex = items.findIndex(item => item.id === toRemove.id)
      items.splice(itemIndex, 1)
    })

    return items
  }
})

/**
 * Reduces list total results.
 */
const totalResultsReducer = makeReducer(0, {
  [LIST_DATA_LOAD]: (state, action = {}) => action.total,
  [LIST_DATA_DELETE]: (state, action = {}) => state - action.items.length
})

/**
 * Reduces list filters.
 */
const filterReducer = makeReducer([], {
  [LIST_FILTER_ADD]: (state, action = {}) => {
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
  },

  [LIST_FILTER_REMOVE]: (state, action = {}) => {
    const newFilters = state.slice(0)
    const pos = state.indexOf(action.filter)
    if (-1 !== pos) {
      newFilters.splice(pos, 1)
    }

    return newFilters
  }
})

/**
 * Reduces list sort.
 */
const sortReducer = makeReducer({property: null, direction: 0}, {
  [LIST_SORT_UPDATE]: (state, action = {}) => {
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
})

/**
 * Reduces data selection.
 *
 * ATTENTION: we assume all data rows have an unique prop `id`.
 */
const selectReducer = makeReducer([], {
  [LIST_RESET_SELECT]: () => {
    return []
  },

  [LIST_TOGGLE_SELECT]: (state, action = {}) => {
    const selected = state.slice(0)

    const itemPos = state.indexOf(action.row.id)
    if (-1 === itemPos) {
      // Item not selected
      selected.push(action.row.id)
    } else {
      // Item selected
      selected.splice(itemPos, 1)
    }

    return selected
  },

  [LIST_DATA_DELETE]: (state, action = {}) => {
    const items = cloneDeep(state)

    action.items.forEach(toRemove => {
      const itemIndex = items.findIndex(item => item.id === toRemove.id)
      items.splice(itemIndex, 1)
    })

    return items
  },

  [LIST_TOGGLE_SELECT_ALL]: (state, action = {}) => {
    return 0 < state.length ? [] : action.rows.map(row => row.id)
  }
})

/**
 * Reduces list current page.
 */
const pageReducer = makeReducer(0, {
  /**
   * Changes the current page.
   *
   * @param {Object} state
   * @param {Object} action
   *
   * @returns {Object}
   */
  [LIST_PAGE_CHANGE]: (state, action = {}) => action.page,

  /**
   * Resets current page on page size changes.
   *
   * @todo find a better way to handle this
   *
   * @returns {Object}
   */
  [LIST_PAGE_SIZE_UPDATE]: () => 0
})

/**
 * Reduces list page size.
 */
const pageSizeReducer = makeReducer(constants.DEFAULT_PAGE_SIZE, {
  /**
   * Changes the page size.
   *
   * @param {Object} state
   * @param {Object} action
   *
   * @returns {Object}
   */
  [LIST_PAGE_SIZE_UPDATE]: (state, action = {}) => action.pageSize
})

/**
 * Creates reducers for lists.
 * It will register reducers for enabled features (eg. filtering, pagination)
 *
 * The `customReducers` param permits to pass reducers for specific list actions.
 * For now, `customReducers` can only have access to the `data` and `totalResults` stores.
 * `customReducers` are applied after the list ones.
 *
 * Example to add a custom reducer to `data`:
 *   customReducers = {
 *      data: myReducerFunc()
 *   }
 *
 * @param {object} customReducers - an object containing custom reducers.
 * @param {object} options        - an options object to disable/enable list features (default: DEFAULT_FEATURES).
 *
 * @returns {function}
 */
function makeListReducer(customReducers = {}, options = {}) {
  const reducer = {}
  const listOptions = merge({}, constants.DEFAULT_FEATURES, options)

  // adds base list reducers
  reducer.data = customReducers.data ?
    reduceReducers(dataReducer, customReducers.data) : dataReducer

  reducer.totalResults = customReducers.totalResults ?
    reduceReducers(totalResultsReducer, customReducers.totalResults) : totalResultsReducer

  // adds reducers for optional features when enabled
  if (listOptions.async) {
    reducer.fetchUrl = fetchUrlReducer
  }

  if (listOptions.deletable) {
    reducer.delete = deleteReducer
  }

  if (listOptions.filterable) {
    reducer.filters = filterReducer
  }

  if (listOptions.sortable) {
    reducer.sortBy = sortReducer
  }

  if (listOptions.selectable) {
    reducer.selected = selectReducer
  }

  if (listOptions.paginated) {
    reducer.page = pageReducer
    reducer.pageSize = pageSizeReducer
  }

  return combineReducers(reducer)
}

export {
  makeListReducer
}
