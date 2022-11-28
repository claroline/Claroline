import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import isEqual from 'lodash/isEqual'

import {makeCancelable} from '#/main/app/api'

import {connect} from '#/main/app/content/list/store'
import {DataListProperty as DataListPropertyTypes} from '#/main/app/content/list/prop-types'
import {TreeData as TreeDataComponent} from '#/main/app/content/tree/components/data'

// todo there are big c/c from data-list

/**
 * Connected TreeData.
 *
 * It automatically displays list features registered in the store (@see makeListReducer()).
 * Counter to ListData, TreeData cannot handle the search, sortable and pagination features.
 *
 * @param props
 * @constructor
 */
class AutoloadedTreeData extends Component {
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
    return (
      <TreeDataComponent
        {...this.props}
        loading={!this.props.loaded || this.props.invalidated}
      />
    )
  }
}

AutoloadedTreeData.propTypes = {
  // calculated from redux store
  loaded: T.bool,
  invalidated: T.bool,
  data: T.array.isRequired,
  totalResults: T.number.isRequired,
  filters: T.object,
  selection: T.object,
  fetch: T.shape({
    url: T.oneOfType([T.string, T.array]).isRequired,
    autoload: T.bool
  }),
  fetchData: T.func
}

const TreeData = connect()(AutoloadedTreeData)

TreeData.propTypes = {
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
   * Provides data delete.
   */
  delete: T.shape({
    url: T.oneOfType([T.string, T.array]).isRequired,
    disabled: T.func, // receives the list of rows to delete
    displayed: T.func // receives the list of rows to delete
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

  // The features to render for the TreeData.
  // NB. by default all features are enabled,
  // so use this props to disable the ones you don't want.
  filterable: T.bool,
  sortable  : T.bool,
  selectable: T.bool,
  paginated : T.bool,
  count     : T.bool
}

TreeData.defaultProps = {
  selectable: true,
  sortable: false, // not implemented
  paginated: false // not implemented
}

export {
  TreeData
}
