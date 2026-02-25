import cloneDeep from 'lodash/cloneDeep'
import difference from 'lodash/difference'
import intersection from 'lodash/intersection'
import merge from 'lodash/merge'

import {makeInstanceReducer, reduceReducers, combineReducers} from '#/main/app/store/reducer'

import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'
import {reducer as searchReducer} from '#/main/app/content/search/store/reducer'
import {
  LIST_SORT_UPDATE,
  LIST_RESET_SELECT,
  LIST_TOGGLE_SELECT,
  LIST_TOGGLE_SELECT_ALL,
  LIST_DATA_INVALIDATE,
  LIST_DATA_LOAD,
  LIST_DATA_DELETE,
  LIST_SET_ERROR
} from '#/main/app/content/list/store/actions'
import {constants} from '#/main/app/content/list/constants'
import {PAGINATION_PAGE_CHANGE, PAGINATION_SIZE_UPDATE} from '#/main/app/content/pagination/store/actions'
import {
  SEARCH_FILTER_ADD,
  SEARCH_FILTER_REMOVE,
  SEARCH_FILTER_RESET,
  SEARCH_TEXT_UPDATE
} from '#/main/app/content/search/store/actions'

const defaultState = {
  loaded: false,
  invalidated: false,
  error: null,
  data: [],
  totalResults: 0,
  sortBy: {
    property: null,
    direction: 0
  },
  selected: [],
  pagination: {
    page: 0,
    pageSize: constants.DEFAULT_PAGE_SIZE
  }
}

/**
 * Reducers list data invalidated state.
 * A list is invalidated when its data needs to be refreshed.
 */
const invalidatedReducer = makeInstanceReducer(defaultState.invalidated, {
  [SECURITY_USER_CHANGE]: () => true,
  [LIST_DATA_INVALIDATE]: () => true,
  [LIST_DATA_LOAD]: () => false,
  [LIST_SET_ERROR]: () => false
})

const loadedReducer = makeInstanceReducer(defaultState.loaded, {
  [LIST_DATA_LOAD]: () => true,
  [LIST_SET_ERROR]: () => true
})

const errorReducer = makeInstanceReducer(defaultState.error, {
  [LIST_DATA_LOAD]: () => null,
  [LIST_SET_ERROR]: (state, action) => ({
    code: action.code.toUpperCase(),
    message: action.message,
    additional: action.additional
  })
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
 * Reduces list sort.
 */
const sortByReducer = makeInstanceReducer(defaultState.sortBy, {
  [LIST_SORT_UPDATE]: (state, action) => ({
    property: action.property,
    direction: action.direction
  })
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
      // item is not selected
      selected.push(action.row.id)
    } else {
      // Item selected
      selected.splice(itemPos, 1)
    }

    return selected
  },

  [LIST_DATA_LOAD]: (state, action) => intersection(state, action.data.map(item => item.id)),

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

const paginationReducer = makeInstanceReducer(defaultState.pagination, {
  /**
   * Changes the current page.
   *
   * @param {Object} state
   * @param {Object} action
   *
   * @returns {Object}
   */
  [PAGINATION_PAGE_CHANGE]: (state, action) => ({
    page: action.page,
    pageSize: state.pageSize
  }),

  /**
   * Changes the page size.
   *
   * @param {Object} state
   * @param {Object} action
   *
   * @returns {Object}
   */
  [PAGINATION_SIZE_UPDATE]: (state, action) => ({
    page: state.page,
    pageSize: action.pageSize
  }),

  // reset page on search update
  [SEARCH_FILTER_ADD]: (state) => ({page: 0, pageSize: state.pageSize}),
  [SEARCH_FILTER_REMOVE]: (state) => ({page: 0, pageSize: state.pageSize}),
  [SEARCH_FILTER_RESET]: (state) => ({page: 0, pageSize: state.pageSize}),
  [SEARCH_TEXT_UPDATE]: (state) => ({page: 0, pageSize: state.pageSize})
})

const baseReducer = {
  loaded: loadedReducer,
  invalidated: invalidatedReducer,
  error: errorReducer,
  data: dataReducer,
  totalResults: totalResultsReducer,
  sortBy: sortByReducer,
  selected: selectedReducer,

  filters: searchReducer,
  pagination: paginationReducer
}

/**
 * Creates reducers for lists.
 *
 * The `customReducers` param permits to pass reducers for specific list actions.
 * `customReducers` are applied after the list ones.
 *
 * Example to add a custom reducer to `data`:
 *   customReducers = {
 *      data: makeReducer(initialState, handlers)
 *   }
 *
 * @param {string} listName      - the name of the list.
 * @param {object} initialState  - the initial state of the list instance (useful to add default filters in autoloading lists).
 * @param {object} customReducer - an object containing custom reducer.
 *
 * @returns {function}
 */
function makeListReducer(listName, initialState = {}, customReducer = {}) {
  const listState = merge({}, defaultState, initialState)

  // generates the list store by merging base reducers and app ones
  const reducer = Object
    .keys(baseReducer)
    .reduce((finalReducer, current) => {
      if (customReducer[current]) {
        // apply base and custom reducer to the store key
        finalReducer[current] = reduceReducers(baseReducer[current](listName, listState[current]), customReducer[current])
      } else {
        // we just need to add the standard reducer
        finalReducer[current] = baseReducer[current](listName, listState[current])
      }

      return finalReducer
    }, {})

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
