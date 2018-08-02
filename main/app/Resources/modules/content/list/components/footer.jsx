import React from 'react'
import {PropTypes as T} from 'prop-types'

import {transChoice} from '#/main/core/translation'

import {constants} from '#/main/app/content/list/constants'
import {DataListPagination as DataListPaginationTypes} from '#/main/app/content/list/prop-types'

import {Pagination} from '#/main/app/content/list/components/pagination.jsx'

const ListFooter = props =>
  <div className="list-footer">
    <div className="count">
      {transChoice('list_results_count', props.totalResults, {count: props.totalResults}, 'platform')}
    </div>

    {(props.pagination && constants.AVAILABLE_PAGE_SIZES[0] < props.totalResults) &&
      <Pagination
        {...props.pagination}
        totalResults={props.totalResults}
      />
    }
  </div>

ListFooter.propTypes = {
  totalResults: T.number.isRequired,
  pagination: T.shape(
    DataListPaginationTypes.propTypes
  )
}

export {
  ListFooter
}
