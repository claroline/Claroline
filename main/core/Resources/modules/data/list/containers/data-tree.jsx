import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {makeCancelable} from '#/main/core/api/utils'

import {connectList} from '#/main/core/data/list/connect'

import {
  DataListAction as DataListActionTypes,
  DataListProperty as DataListPropertyTypes
} from '#/main/core/data/list/prop-types'
import {DataTree as DataTreeComponent} from '#/main/core/data/list/components/data-tree.jsx'

// todo there are big c/c from data-list

/**
 * Connected DataTree.
 *
 * It automatically displays list features registered in the store (@see makeListReducer()).
 * Counter to DataList, DataTree cannot handle the sortable and pagination features.
 *
 * @param props
 * @constructor
 */
class DataTree extends Component {
  constructor(props) {
    super(props)

    if (this.isAutoLoaded()) {
      this.reload()
    }
  }

  componentDidUpdate(prevProps) {
    if (this.isAutoLoaded()) {
      if (this.props.loaded !== prevProps.loaded // data are not loaded
        || this.props.invalidated !== prevProps.invalidated // data have been invalidated
        || (this.props.fetch.autoload !== prevProps.fetch.autoload) // autoload have been enabled
      ) {
        this.reload()
      }
    }
  }

  isAutoLoaded() {
    return this.props.fetch && !!this.props.fetch.autoload
  }

  reload() {
    if (!this.props.loaded || this.props.invalidated) {
      if (this.pending && this.props.invalidated) {
        this.pending.cancel()
      }

      if (!this.pending) {
        this.pending = makeCancelable(
          this.props.fetchData()
        )

        this.pending.promise.then(
          () => this.pending = null,
          () => this.pending = null
        )
      }
    }
  }

  render() {
    return (
      <DataTreeComponent {...this.props} />
    )
  }
}

DataTree.propTypes = {
  /**
   * The name of the data in the tree.
   *
   * It should be the key in the store where the list has been mounted
   * (aka where `makeListReducer()` has been called).
   */
  name: T.string.isRequired,

  /**
   * Open action generator for rows.
   * It gets the current data row as first param.
   *
   * NB. It's called to generate the action (to be able to catch generated URL),
   * so if your open action is a func, generator should return another function,
   * not call it. Example : (row) => myFunc
   */
  open: T.shape({
    action: T.oneOfType([T.func, T.string]),
    disabled: T.func
  }),

  /**
   * Provides asynchronous data load.
   */
  fetch: T.shape({
    url: T.oneOfType([T.string, T.array]).isRequired,
    autoload: T.bool
  }),

  /**
   * Provides data delete.
   */
  delete: T.shape({
    url: T.oneOfType([T.string, T.array]), // if provided, data delete will call server
    disabled: T.func, // receives the list of rows (either the selected ones or the current one)
    displayed: T.func // receives the list of rows (either the selected ones or the current one)
  }),

  /**
   * The definition of the list rows data.
   */
  definition: T.arrayOf(
    T.shape(DataListPropertyTypes.propTypes)
  ).isRequired,

  /**
   * A list of data related actions.
   */
  actions: T.arrayOf(
    T.shape(DataListActionTypes.propTypes)
  ),

  // calculated from redux store
  loaded: T.bool,
  invalidated: T.bool,
  data: T.array.isRequired,
  totalResults: T.number.isRequired,
  filters: T.object,
  selection: T.object,
  fetchData: T.func
}

const DataTreeContainer = connectList()(DataTree)

export {
  DataTreeContainer
}
