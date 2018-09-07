import React from 'react'
import {PropTypes as T} from 'prop-types'

import {constants} from '#/main/app/content/pagination/constants'
import {countPages} from '#/main/app/content/pagination/utils'

import {PaginationPages} from '#/main/app/content/pagination/components/pages'
import {PaginationSize} from '#/main/app/content/pagination/components/size'

const Pagination = props =>
  <nav className="pagination-container">
    <PaginationPages
      current={props.current}
      pages={countPages(props.totalResults, props.pageSize)}
      changePage={props.changePage}
    />

    <PaginationSize
      pageSize={props.pageSize}
      availableSizes={props.availableSizes}
      updatePageSize={props.updatePageSize}
    />
  </nav>

Pagination.propTypes = {
  totalResults: T.number.isRequired,
  current: T.number,
  pageSize: T.number,
  availableSizes: T.arrayOf(T.number),
  changePage: T.func.isRequired,
  updatePageSize: T.func.isRequired
}

Pagination.defaultProps = {
  current: 0,
  pageSize: constants.DEFAULT_PAGE_SIZE,
  availableSizes: constants.AVAILABLE_PAGE_SIZES
}

export {
  Pagination
}
