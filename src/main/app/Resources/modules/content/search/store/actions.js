import invariant from 'invariant'

import {makeInstanceAction, makeInstanceActionCreator} from '#/main/app/store/actions'

export const SEARCH_FILTER_ADD    = 'SEARCH_FILTER_ADD'
export const SEARCH_FILTER_REMOVE = 'SEARCH_FILTER_REMOVE'
export const SEARCH_FILTER_RESET  = 'SEARCH_FILTER_RESET'

export const actions = {}

/**
 * Add a filter to the search.
 *
 * @param {array}   searchName - the name of the search to which we need to attach the filters
 * @param {string}  property
 * @param {*}       value
 * @param {boolean} locked
 */
actions.addFilter = (searchName, property, value, locked = false) => {
  invariant(property, 'property is required.')
  invariant(value !== undefined && value !== null, 'value is required.')

  return ({
    type: makeInstanceAction(SEARCH_FILTER_ADD, searchName),
    property,
    value,
    locked
  })
}

/**
 * Remove a filter from the search.
 *
 * @param {array} searchName - the name of the search to which we need to attach the filters
 */
actions.removeFilter = makeInstanceActionCreator(SEARCH_FILTER_REMOVE, 'filter')

/**
 * Reset all search filters at once
 *
 * @param {array} searchName - the name of the search to which we need to attach the filters
 * @param {array} filters    - the list of filters to set
 */
actions.resetFilters = makeInstanceActionCreator(SEARCH_FILTER_RESET, 'filters')
