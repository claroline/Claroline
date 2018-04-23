import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {makeCancelable} from '#/main/core/api/utils'

import {connectList} from '#/main/core/data/list/connect'

import {DataListProperty as DataListPropertyTypes} from '#/main/core/data/list/prop-types'
import {DataList as DataListComponent} from '#/main/core/data/list/components/data-list'

/**
 * Connected DataList.
 *
 * It automatically displays list features registered in the store (@see makeListReducer()).
 * It can also performs API calls to refresh data if configured to.
 */
class DataList extends Component {
  constructor(props) {
    super(props)

    if (this.isAutoLoaded()) {
      this.reload()
    }
  }

  componentDidUpdate(prevProps) {
    if (this.isAutoLoaded()) {
      // list is configured to auto fetch data
      if (this.props.loaded !== prevProps.loaded // data are not loaded
        || this.props.invalidated !== prevProps.invalidated // data have been invalidated
        || (this.props.fetch.autoload !== prevProps.fetch.autoload) // autoload has been enabled
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
      <DataListComponent
        {...this.props}
      />
    )
  }
}

DataList.propTypes = {
  /**
   * The name of the data in the list.
   *
   * It should be the key in the store where the list has been mounted
   * (aka where `makeListReducer()` has been called).
   */
  name: T.string.isRequired,

  /**
   * Provides asynchronous data load.
   *
   * @todo : maybe also allow a CallbackAction
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
   * Provides data delete.
   */
  delete: T.shape({
    url: T.oneOfType([T.string, T.array]).isRequired,
    disabled: T.func, // receives the list of rows to delete
    displayed: T.func // receives the list of rows to delete
  }),

  /**
   * A list of data related actions.
   */
  actions: T.func,

  /**
   * The card component to render in grid modes.
   */
  card: T.func,

  /**
   * Enables/Disables the feature to filter the displayed columns.
   */
  filterColumns: T.bool,

  // calculated from redux store
  loaded: T.bool,
  invalidated: T.bool,
  data: T.array.isRequired,
  totalResults: T.number.isRequired,
  filters: T.object,
  sorting: T.object,
  pagination: T.object,
  selection: T.object,
  fetchData: T.func
}

// connect list to redux
const DataListContainer = connectList()(DataList)

export {
  DataListContainer
}
