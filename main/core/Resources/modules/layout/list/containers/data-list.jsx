import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {actions as listActions} from '#/main/core/layout/list/actions'
import {select as listSelect} from '#/main/core/layout/list/selectors'

import {DataAction, DataProperty} from '#/main/core/layout/list/prop-types'
import {DataList as DataListComponent} from '#/main/core/layout/list/components/data-list.jsx'

/**
 * Connected DataList.
 *
 * It automatically displays list features registered in the store (@see makeListReducer()).
 * It can also performs API calls to refresh data if configured to.
 *
 * @param props
 * @constructor
 */
const DataList = props =>
  <DataListComponent {...props} />

DataList.propTypes = {
  /**
   * The name of the data in the list.
   *
   * It should be the key in the store where the list has been mounted
   * (aka where `makeListReducer()` has been called).
   */
  name: T.string.isRequired,

  /**
   * The definition of the list rows data.
   */
  definition: T.arrayOf(
    T.shape(DataProperty.propTypes)
  ).isRequired,

  /**
   * A list of data related actions.
   */
  actions: T.arrayOf(
    T.shape(DataAction.propTypes)
  ),

  /**
   * A function to normalize data for card display.
   * - the data row is passed as argument
   * - the func MUST return an object respecting `DataCard.propTypes`.
   *
   * It's required to enable cards based display modes.
   */
  card: T.func.isRequired,

  /**
   * Enables/Disables the feature to filter the displayed columns.
   */
  filterColumns: T.bool,

  // calculated from redux store
  data: T.array.isRequired,
  totalResults: T.number.isRequired,
  filters: T.object,
  sorting: T.object,
  pagination: T.object,
  selection: T.object
}

/**
 * Gets list data and config from redux store.
 *
 * NB. we will enable list features based on what we find in the store.
 *
 * @param {object} state
 * @param {object} ownProps
 *
 * @returns {object}
 */
function mapStateToProps(state, ownProps) {
  // get the root of the list in the store
  const listState = state[ownProps.name]

  const newProps = {
    data: listSelect.data(listState),
    totalResults: listSelect.totalResults(listState),
    async: listSelect.isAsync(listState),
    queryString: listSelect.queryString(listState)
  }

  // grab data for optional features
  newProps.filterable = listSelect.isFilterable(listState)
  if (newProps.filterable) {
    newProps.filters = listSelect.filters(listState)
  }

  newProps.sortable = listSelect.isSortable(listState)
  if (newProps.sortable) {
    newProps.sortBy = listSelect.sortBy(listState)
  }

  newProps.selectable = listSelect.isSelectable(listState)
  if (newProps.selectable) {
    newProps.selected = listSelect.selected(listState)
  }

  newProps.paginated = listSelect.isPaginated(listState)
  if (newProps.paginated) {
    newProps.pageSize    = listSelect.pageSize(listState)
    newProps.currentPage = listSelect.currentPage(listState)
  }

  return newProps
}

/**
 * Injects store actions based on the list config.
 * NB. we inject all list actions, `mergeProps` will only pick the one for enabled features.
 *
 * @param {function} dispatch
 * @param {object}   ownProps
 *
 * @returns {object}
 */
function mapDispatchToProps(dispatch, ownProps) {
  // we inject all list actions, the `mergeProps` function will filter it
  // based on the enabled features.
  return {
    // async
    fetchData() {
      dispatch(listActions.fetchData(ownProps.name))
    },
    // filtering
    addFilter(property, value) {
      dispatch(listActions.addFilter(property, value))
    },
    removeFilter(filter) {
      dispatch(listActions.removeFilter(filter))
    },
    // sorting
    updateSort(property) {
      dispatch(listActions.updateSort(property))
    },
    // selection
    toggleSelect(id) {
      dispatch(listActions.toggleSelect(id))
    },
    toggleSelectAll(items) {
      dispatch(listActions.toggleSelectAll(items))
    },
    // pagination
    updatePageSize(pageSize) {
      dispatch(listActions.updatePageSize(pageSize))
    },
    changePage(page) {
      dispatch(listActions.changePage(page))
    }
  }
}

/**
 * Generates the final container props based on store available data.
 * For async lists, It also adds async calls to list actions that require data refresh.
 *
 * @param {object} stateProps    - the injected store data
 * @param {object} dispatchProps - the injected store actions
 * @param {object} ownProps      - the props passed to the react components
 *
 * @returns {object} - the final props object that will be passed to DataList container
 */
function mergeProps(stateProps, dispatchProps, ownProps) {
  const asyncDecorator = (func) => {
    if (stateProps.async) {
      return (...args) => {
        // call original action
        func.apply(null, args)

        // refresh list
        dispatchProps.fetchData()
      }
    }

    return func
  }

  const props = {
    name:          ownProps.name,
    definition:    ownProps.definition,
    data:          stateProps.data,
    totalResults:  stateProps.totalResults,
    actions:       ownProps.actions,
    card:          ownProps.card,
    queryString:   stateProps.queryString,
    filterColumns: ownProps.filterColumns
  }

  if (stateProps.filterable) {
    props.filters = {
      current: stateProps.filters,
      addFilter: asyncDecorator(dispatchProps.addFilter),
      removeFilter: asyncDecorator(dispatchProps.removeFilter)
    }
  }

  if (stateProps.sortable) {
    props.sorting = {
      current: stateProps.sortBy,
      updateSort: asyncDecorator(dispatchProps.updateSort)
    }
  }

  if (stateProps.selectable) {
    props.selection = {
      current: stateProps.selected,
      toggle: dispatchProps.toggleSelect,
      toggleAll: dispatchProps.toggleSelectAll
    }
  }

  if (stateProps.paginated) {
    props.pagination = {
      pageSize: stateProps.pageSize,
      current: stateProps.currentPage,
      changePage: asyncDecorator(dispatchProps.changePage),
      updatePageSize: asyncDecorator(dispatchProps.updatePageSize)
    }
  }

  return props
}

// connect list to redux
const DataListContainer = connect(mapStateToProps, mapDispatchToProps, mergeProps)(DataList)

export {
  DataListContainer
}
