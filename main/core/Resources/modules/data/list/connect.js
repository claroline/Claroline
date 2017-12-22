import {connect} from 'react-redux'
import invariant from 'invariant'

import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {actions as modalActions} from '#/main/core/layout/modal/actions'

import {actions as listActions} from '#/main/core/data/list/actions'
import {select as listSelect} from '#/main/core/data/list/selectors'

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
  const listState = listSelect.list(state, ownProps.name)

  invariant(undefined !== listState, `Try to connect list on undefined store '${ownProps.name}'.`)

  const newProps = {
    loaded: listSelect.loaded(listState),
    invalidated: listSelect.invalidated(listState),
    data: listSelect.data(listState),
    totalResults: listSelect.totalResults(listState),
    open: ownProps.open,
    delete: ownProps.delete
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
 * Injects list actions.
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
      // return the async promise
      return dispatch(listActions.fetchData(ownProps.name, ownProps.fetch.url))
    },
    deleteData(items) {
      // return the async promise
      return dispatch(listActions.deleteData(ownProps.name, ownProps.delete.url, items))
    },
    invalidateData() {
      dispatch(listActions.invalidateData(ownProps.name))
    },

    // filtering
    addFilter(property, value) {
      dispatch(listActions.addFilter(ownProps.name, property, value))
    },
    removeFilter(filter) {
      dispatch(listActions.removeFilter(ownProps.name, filter))
    },

    // sorting
    updateSort(property) {
      dispatch(listActions.updateSort(ownProps.name, property))
    },

    // selection
    toggleSelect(id) {
      dispatch(listActions.toggleSelect(ownProps.name, id))
    },
    toggleSelectAll(items) {
      dispatch(listActions.toggleSelectAll(ownProps.name, items))
    },

    // pagination
    updatePageSize(pageSize) {
      dispatch(listActions.updatePageSize(ownProps.name, pageSize))
    },
    changePage(page) {
      dispatch(listActions.changePage(ownProps.name, page))
    },

    // delete
    deleteItems(items, title, question) {
      dispatch(
        modalActions.showModal(MODAL_DELETE_CONFIRM, {
          title: title,
          question: question,
          handleConfirm: () => {
            if (ownProps.delete.url) {
              dispatch(listActions.deleteData(ownProps.name, ownProps.delete.url, items))
            } else {
              dispatch(listActions.deleteItems(ownProps.name, items))
            }
          }
        })
      )
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
    if (ownProps.fetch) {
      return (...args) => {
        // call original action
        func.apply(null, args)

        // refresh list
        dispatchProps.invalidateData()
      }
    }

    return func
  }

  const props = {
    name:          ownProps.name,
    fetch:         ownProps.fetch,
    definition:    ownProps.definition,
    actions:       ownProps.actions,
    card:          ownProps.card,
    filterColumns: ownProps.filterColumns,
    display:       ownProps.display,
    translations:  ownProps.translations,
    loaded:        stateProps.loaded,
    invalidated:   stateProps.invalidated,
    data:          stateProps.data,
    totalResults:  stateProps.totalResults,

    fetchData: dispatchProps.fetchData
  }

  // open action
  if (stateProps.open) {
    props.primaryAction = stateProps.open
  }

  // delete action
  if (stateProps.delete) {
    props.deleteAction = {
      action: dispatchProps.deleteItems,
      disabled: stateProps.delete.disabled,
      displayed: stateProps.delete.displayed
    }
  }

  // optional list features
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

/**
 * Connects a list component to the store.
 *
 * @returns {function}
 */
function connectList() {
  return (ListComponent) => connect(mapStateToProps, mapDispatchToProps, mergeProps)(ListComponent)
}

export {
  connectList
}
