import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import isEqual from 'lodash/isEqual'

import {makeCancelable} from '#/main/app/api'

import {constants as searchConst} from '#/main/app/content/search/constants'
import {connect} from '#/main/app/content/list/store'
import {DataListProperty as DataListPropertyTypes} from '#/main/app/content/list/prop-types'
import {ListData as ListDataComponent} from '#/main/app/content/list/components/data'

/**
 * Connected DataList.
 *
 * It automatically displays list features registered in the store (@see makeListReducer()).
 * It can also performs API calls to refresh data if configured to.
 */
class AutoloadedListData extends Component {
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
      <ListDataComponent
        {...this.props}
        loading={!this.props.loaded || this.props.invalidated}
      />
    )
  }
}

AutoloadedListData.propTypes = {
  // calculated from redux store
  loaded: T.bool,
  invalidated: T.bool,
  data: T.array.isRequired,
  totalResults: T.number.isRequired,
  filters: T.object,
  sorting: T.object,
  pagination: T.object,
  selection: T.object,
  fetch: T.shape({
    url: T.oneOfType([T.string, T.array]).isRequired,
    autoload: T.bool
  }),
  fetchData: T.func
}

// connect list to redux
const ListData = connect()(AutoloadedListData)

ListData.propTypes = {
  /**
   * The name of the data in the list.
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
   * Provides data delete.
   *
   * @deprecated
   */
  delete: T.shape({
    url: T.oneOfType([T.string, T.array]).isRequired,
    label: T.string,
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

  searchMode: T.string,
  pageSizes: T.arrayOf(T.number),

  // The features to render for the ListData.
  // NB. by default all features are enabled,
  // so use this props to disable the ones you don't want.
  filterable: T.bool,
  sortable  : T.bool,
  selectable: T.bool,
  paginated : T.bool,
  count     : T.bool
}

ListData.defaultProps = {
  searchMode: searchConst.DEFAULT_SEARCH_TYPE,
  filterable: true,
  sortable  : true,
  selectable: true,
  paginated : true,
  count     : true
}

export {
  ListData
}
