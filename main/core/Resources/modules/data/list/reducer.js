import cloneDeep from 'lodash/cloneDeep'
import merge from 'lodash/merge'
import difference from 'lodash/difference'

import {makeInstanceReducer, reduceReducers, combineReducers} from '#/main/core/scaffolding/reducer'

import {constants} from '#/main/core/data/list/constants'
import {
  LIST_FILTER_ADD,
  LIST_FILTER_REMOVE,
  LIST_SORT_UPDATE,
  LIST_RESET_SELECT,
  LIST_TOGGLE_SELECT,
  LIST_TOGGLE_SELECT_ALL,
  LIST_DATA_INVALIDATE,
  LIST_DATA_LOAD,
  LIST_DATA_DELETE,
  LIST_PAGE_CHANGE,
  LIST_PAGE_SIZE_UPDATE
} from '#/main/core/data/list/actions'

const defaultState = {
  loaded: false,
  invalidated: false,
  data: [],
  totalResults: 0,
  filters: [],
  sortBy: {
    property: null,
    direction: 0
  },
  selected: [],
  page: 0,
  pageSize: constants.DEFAULT_PAGE_SIZE
}

const invalidatedReducer = makeInstanceReducer(defaultState.invalidated, {
  [LIST_DATA_INVALIDATE]: () => true,
  [LIST_DATA_LOAD]: () => false
})

const loadedReducer = makeInstanceReducer(defaultState.invalidated, {
  [LIST_DATA_LOAD]: () => true
})

/**
 * Reduces list data items.
 */
const dataReducer = makeInstanceReducer(defaultState.data, {
  [LIST_DATA_LOAD]: (state, action) => action.data,
  [LIST_DATA_DELETE]: (state, action) => {
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
const totalResultsReducer = makeInstanceReducer(defaultState.totalResults, {
  [LIST_DATA_LOAD]: (state, action) => action.total,
  [LIST_DATA_DELETE]: (state, action) => state - action.items.length
})

/**
 * Reduces list filters.
 */
const filtersReducer = makeInstanceReducer(defaultState.filters, {
  [LIST_FILTER_ADD]: (state, action) => {
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

  [LIST_FILTER_REMOVE]: (state, action) => {
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
const sortByReducer = makeInstanceReducer(defaultState.sortBy, {
  [LIST_SORT_UPDATE]: (state, action) => {
    let direction = 1
    if (state && state.property === action.property) {
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
const selectedReducer = makeInstanceReducer(defaultState.selected, {
  [LIST_RESET_SELECT]: () => [],

  [LIST_TOGGLE_SELECT]: (state, action) => {
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

  [LIST_DATA_DELETE]: (state, action) => {
    const items = cloneDeep(state)

    action.items.forEach(toRemove => {
      const itemIndex = items.findIndex(item => item.id === toRemove.id)
      items.splice(itemIndex, 1)
    })

    return items
  },

  [LIST_TOGGLE_SELECT_ALL]: (state, action) => {
    return 0 < state.length ? [] : [].concat(state, action.rows.map(row => row.id))
  }
})

/**
 * Reduces list current page.
 */
const pageReducer = makeInstanceReducer(defaultState.page, {
  /**
   * Changes the current page.
   *
   * @param {Object} state
   * @param {Object} action
   *
   * @returns {Object}
   */
  [LIST_PAGE_CHANGE]: (state, action) => action.page,

  /**
   * Resets current page on page size changes.
   *
   * @todo find a better way to handle this
   *
   * @returns {Object}
   */
  [LIST_PAGE_SIZE_UPDATE]: () => 0,

  /**
   * Resets current page on filter add.
   *
   * @todo find a better way to handle this
   *
   * @returns {Object}
   */
  [LIST_FILTER_ADD]: () => 0
})

/**
 * Reduces list page size.
 */
const pageSizeReducer = makeInstanceReducer(defaultState.pageSize, {
  /**
   * Changes the page size.
   *
   * @param {Object} state
   * @param {Object} action
   *
   * @returns {Object}
   */
  [LIST_PAGE_SIZE_UPDATE]: (state, action) => action.pageSize
})

const baseReducer = {
  loaded: loadedReducer,
  invalidated: invalidatedReducer,
  data: dataReducer,
  totalResults: totalResultsReducer,
  filters: filtersReducer,
  sortBy: sortByReducer,
  selected: selectedReducer,
  page: pageReducer,
  pageSize: pageSizeReducer
}

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
 *      data: handlers
 *   }
 *
 * @param {string} listName      - the name of the list.
 * @param {object} initialState  - the initial state of the list instance (useful to add default filters in autoloading lists).
 * @param {object} customReducer - an object containing custom reducer.
 * @param {object} options       - an options object to disable/enable list features (default: DEFAULT_FEATURES).
 *
 * @returns {function}
 */
function makeListReducer(listName, initialState = {}, customReducer = {}, options = {}) {
  const reducer = {}

  const listState = merge({}, defaultState, initialState)
  const listOptions = merge({}, constants.DEFAULT_FEATURES, options)

  // adds base list reducers
  reducer.loaded = baseReducer.loaded(listName, listState.loaded)

  reducer.invalidated = customReducer.invalidated ?
    reduceReducers(baseReducer.invalidated(listName, listState.invalidated), customReducer.invalidated) : baseReducer.invalidated(listName, listState.invalidated)

  reducer.data = customReducer.data ?
    reduceReducers(baseReducer.data(listName, listState.data), customReducer.data) : baseReducer.data(listName, listState.data)

  reducer.totalResults = customReducer.totalResults ?
    reduceReducers(baseReducer.totalResults(listName, listState.totalResults), customReducer.totalResults) : baseReducer.totalResults(listName, listState.totalResults)

  // adds reducers for optional features when enabled
  if (listOptions.filterable) {
    reducer.filters = baseReducer.filters(listName, listState.filters)
  }

  if (listOptions.sortable) {
    reducer.sortBy = baseReducer.sortBy(listName, listState.sortBy)
  }

  if (listOptions.selectable) {
    reducer.selected = baseReducer.selected(listName, listState.selected)
  }

  if (listOptions.paginated) {
    reducer.page = baseReducer.page(listName, listState.page)
    reducer.pageSize = baseReducer.pageSize(listName, listState.pageSize)
  }

  // get custom keys
  const rest = difference(Object.keys(customReducer), Object.keys(baseReducer))
  rest.map(reducerName =>
    reducer[reducerName] = customReducer[reducerName]
  )

  return combineReducers(reducer)
}

export {
  makeListReducer
}
