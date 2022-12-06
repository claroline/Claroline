import {connect as baseConnect} from 'react-redux'
import invariant from 'invariant'
import isEqual from 'lodash/isEqual'

import {toKey} from '#/main/core/scaffolding/text'
import {trans, transChoice} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {actions as listActions} from '#/main/app/content/list/store/actions'
import {select as listSelect} from '#/main/app/content/list/store/selectors'

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
    totalResults: listSelect.totalResults(listState)
  }

  // grab data for optional features
  if (ownProps.filterable) {
    newProps.filters = listSelect.filters(listState)
  }

  if (ownProps.sortable) {
    newProps.sortBy = listSelect.sortBy(listState)
  }

  if (ownProps.selectable) {
    newProps.selected = listSelect.selected(listState)
  }

  if (ownProps.paginated) {
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
    fetchData(url, invalidate = false) {
      // return the async promise
      return dispatch(listActions.fetchData(ownProps.name, url, invalidate))
    },
    invalidateData() {
      dispatch(listActions.invalidateData(ownProps.name))
    },

    // filtering
    addFilter(property, value, locked) {
      dispatch(listActions.addFilter(ownProps.name, property, value, locked))
    },
    removeFilter(filter) {
      dispatch(listActions.removeFilter(ownProps.name, filter))
    },
    resetFilters(filters = []) {
      dispatch(listActions.resetFilters(ownProps.name, filters))
    },

    // sorting
    updateSort(property, direction) {
      dispatch(listActions.updateSort(ownProps.name, property, direction))
    },

    // selection
    toggleSelect(id, action = 'select') {
      dispatch(listActions.toggleSelect(ownProps.name, id, action))
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
    deleteItems(items) {
      if (ownProps.delete.url) {
        dispatch(listActions.deleteData(ownProps.name, ownProps.delete.url, items))
      } else {
        dispatch(listActions.deleteItems(ownProps.name, items))
      }
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

        // refresh list (I'm not sure I should do that)
        if (ownProps.fetch.autoload) {
          dispatchProps.invalidateData()
        } else {
          dispatchProps.fetchData(ownProps.fetch.url, true)
        }
      }
    }

    return func
  }

  const props = {
    fetchData: dispatchProps.fetchData,

    level:         ownProps.level,
    displayLevel:  ownProps.displayLevel,
    className:     ownProps.className,
    title:         ownProps.title,
    id:            toKey(ownProps.name),
    name:          ownProps.name,
    fetch:         ownProps.fetch,
    definition:    ownProps.definition,
    card:          ownProps.card,
    display:       ownProps.display,
    translations:  ownProps.translations,
    readOnly:      stateProps.readOnly,
    loaded:        stateProps.loaded,
    invalidated:   stateProps.invalidated,
    data:          stateProps.data,
    totalResults:  stateProps.totalResults
  }

  props.customActions = ownProps.customActions
  // Data actions
  props.primaryAction = ownProps.primaryAction

  // create the final list of actions
  // merge standard actions with the delete one
  // todo find a better way to handle difference between promised actions and standard ones
  props.actions = (rows) => {
    // generates defined list actions
    let actions
    if (ownProps.actions) {
      actions = ownProps.actions(rows)
    }

    if (ownProps.delete) {
      const deleteAction = {
        name: 'delete',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-trash',
        label: ownProps.delete.label || trans('delete', {}, 'actions'),
        dangerous: true,
        confirm: {
          title: trans('objects_delete_title'),
          message: transChoice('objects_delete_question', rows.length, {count: rows.length}),
          button: trans('delete', {}, 'actions')
        },
        disabled: undefined !== ownProps.delete.disabled && ownProps.delete.disabled(rows),
        displayed: undefined === ownProps.delete.displayed || ownProps.delete.displayed(rows),
        callback: () => dispatchProps.deleteItems(rows)
      }

      if (actions instanceof Promise) {
        if (actions) {
          actions = actions.then((actions) => [deleteAction].concat(actions))
          //actions = actions.then((actions) => actions.concat([deleteAction]))
        } else {
          actions = Promise.resolve([deleteAction])
        }
      } else {
        if (actions) {
          actions = [deleteAction].concat(actions)
          // actions = actions.concat([deleteAction])
        } else {
          actions = [deleteAction]
        }
      }
    }

    return actions
  }

  // optional list features
  props.count = ownProps.count

  if (ownProps.filterable) {
    props.filters = {
      mode: ownProps.searchMode,
      current: stateProps.filters,
      addFilter: asyncDecorator(dispatchProps.addFilter),
      removeFilter: asyncDecorator(dispatchProps.removeFilter),
      resetFilters: asyncDecorator(dispatchProps.resetFilters)
    }
  }

  if (ownProps.sortable) {
    props.sorting = {
      current: stateProps.sortBy,
      updateSort: asyncDecorator(dispatchProps.updateSort)
    }
  }

  if (ownProps.selectable) {
    props.selection = {
      current: stateProps.selected,
      toggle: dispatchProps.toggleSelect,
      toggleAll: dispatchProps.toggleSelectAll
    }
  }

  if (ownProps.paginated) {
    props.pagination = {
      pageSize: stateProps.pageSize,
      current: stateProps.currentPage,
      availableSizes: ownProps.pageSizes,
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
function connect() {
  return (ListComponent) => baseConnect(mapStateToProps, mapDispatchToProps, mergeProps, {
    // the default behavior is to use shallow comparison
    // but as I create new objects in `mergeProps`, the comparison always returns false
    // and cause recomputing
    areMergedPropsEqual: (next, prev) => isEqual(next, prev)
  })(ListComponent)
}

export {
  connect
}
