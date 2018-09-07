import React from 'react'
import {PropTypes as T} from 'prop-types'

import {constants} from '#/main/app/content/list/constants'
import {DataListPagination as DataListPaginationTypes} from '#/main/app/content/list/prop-types'

import {ListCount} from '#/main/app/content/list/components/count'
import {Pagination} from '#/main/app/content/pagination/components/pagination'

const ListFooter = props =>
  <div className="list-footer">
    {props.count &&
      <ListCount totalResults={props.totalResults} />
    }

    {(props.pagination && constants.AVAILABLE_PAGE_SIZES[0] < props.totalResults) &&
      <Pagination
        {...props.pagination}
        totalResults={props.totalResults}
      />
    }
  </div>

ListFooter.propTypes = {
  count: T.bool.isRequired,
  totalResults: T.number.isRequired,
  pagination: T.shape(
    DataListPaginationTypes.propTypes
  )
}

export {
  ListFooter
}
