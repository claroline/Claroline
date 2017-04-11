import React, {PropTypes as T} from 'react'

import {ListHeader} from '#/main/core/layout/list/components/header.jsx'
import {Pagination} from '#/main/core/layout/list/components/pagination.jsx'

const EmptyList = props =>
  <div className="empty-list">
    {props.hasFilters ?
      'No results found. Try to change your filters' :
      'No results found.'
    }
  </div>

EmptyList.propTypes = {
  hasFilters: T.bool
}

EmptyList.defaultProps = {
  hasFilters: false
}

/**
 * Full data list with configured components (eg. search, pagination).
 *
 * @param props
 * @constructor
 */
const DataList = props =>
  <div className="data-list">
    <ListHeader
      format={props.format}
      columns={props.columns}
      filters={props.filters}
    />

    {0 === props.totalResults &&
      <EmptyList hasFilters={props.filters && 0 < props.filters.active.length} />
    }

    {0 < props.totalResults &&
      props.children
    }

    {(0 < props.totalResults && props.pagination) &&
      <Pagination {...props.pagination} />
    }
  </div>

DataList.propTypes = {
  /**
   * Display format of the list.
   */
  format: T.oneOf(['list', 'tiles-sm', 'tiles-lg']),

  /**
   * Total results available in the list.
   */
  totalResults: T.number.isRequired,

  /**
   * Data columns configuration.
   */
  columns: T.shape({
    /**
     * Available columns to display for data.
     */
    available: T.arrayOf(T.string).isRequired,
    /**
     * Current displayed columns.
     */
    active: T.arrayOf(T.string).isRequired
  }),

  /**
   * Search filters configuration.
   * Providing this object automatically display the search box component.
   */
  filters: T.shape({
    available: T.arrayOf(T.string).isRequired,
    active: T.arrayOf(T.string).isRequired,
    onChange: T.func.isRequired
  }),

  /**
   * Pagination configuration.
   * Providing this object automatically display pagination and results per page components.
   */
  pagination: T.shape({
    current: T.number.isRequired,
    pageSize: T.number.isRequired,
    pages: T.number.isRequired,
    handlePagePrevious: T.func.isRequired,
    handlePageNext: T.func.isRequired,
    handlePageChange: T.func.isRequired,
    handlePageSizeUpdate: T.func.isRequired
  }),

  children: T.node.isRequired
}

DataList.defaultProps = {
  format: 'list'
}

export {
  DataList
}
