import {connect as baseConnect} from 'react-redux'

import {actions, selectors} from '#/main/app/content/search/store'

/**
 * Connects a search component to the store.
 *
 * @returns {function}
 */
function connect() {
  return (SearchComponent) => baseConnect(
    (state, ownProps) => ({
      current: selectors.filters(selectors.search(state, ownProps.name))
    }),
    (dispatch, ownProps) => ({
      addFilter(property, value, locked = false) {
        dispatch(actions.addFilter(ownProps.name, property, value, locked))
      },

      removeFilter(filter) {
        dispatch(actions.removeFilter(ownProps.name, filter))
      },

      resetFilters(filters = []) {
        dispatch(actions.resetFilters(ownProps.name, filters))
      }
    })
  )(SearchComponent)
}

export {
  connect
}