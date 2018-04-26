import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import isEqual from 'lodash/isEqual'

import {makeCancelable} from '#/main/core/api/utils'

import {connectList} from '#/main/core/data/list/connect'
import {DataListProperty as DataListPropertyTypes} from '#/main/core/data/list/prop-types'
import {DataTree as DataTreeComponent} from '#/main/core/data/list/components/data-tree'

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
      // list is configured to auto fetch data
      if (!isEqual(this.props.invalidated, prevProps.invalidated)
        || !isEqual(this.props.fetch, prevProps.fetch)) {
        // we force reload if the target url has changed
        this.reload(!isEqual(this.props.fetch.url, prevProps.fetch.url))
      }
    }
  }

  isAutoLoaded() {
    return this.props.fetch && !!this.props.fetch.autoload
  }

  reload(force = false) {
    if (force || !this.props.loaded || this.props.invalidated) {
      if (this.pending && (force || this.props.invalidated)) {
        this.pending.cancel()
      }

      if (!this.pending) {
        this.pending = makeCancelable(
          this.props.fetchData(this.props.fetch.url)
        )

        this.pending.promise.then(
          () => this.pending = null,
          () => this.pending = null
        )
      }
    }
  }

  render() {
    if (this.isAutoLoaded()) {
      this.reload()
    }

    return (
      <DataTreeComponent
        {...this.props}
      />
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
   * Provides asynchronous data load.
   */
  fetch: T.shape({
    url: T.oneOfType([T.string, T.array]).isRequired,
    autoload: T.bool
  }),

  /**
   * The definition of the list rows data.
   */
  definition: T.arrayOf(
    T.shape(DataListPropertyTypes.propTypes)
  ).isRequired,

  /**
   * Open action generator for rows.
   * It gets the current data row as first param.
   *
   * NB. It's called to generate the action (to be able to catch generated URL),
   * so if your open action is a func, generator should return another function,
   * not call it. Example : (row) => myFunc
   */
  primaryAction: T.func,

  /**
   * A list of data related actions.
   */
  actions: T.func,

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
