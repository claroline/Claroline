import React from 'react'
import {PropTypes as T} from 'prop-types'

import {DataListPagination as DataListPaginationTypes} from '#/main/app/content/list/prop-types'

import {ListCount} from '#/main/app/content/list/components/count'
import {Pagination} from '#/main/app/content/pagination/components/pagination'

const ListFooter = props =>
  <div className="list-footer">
    {props.count &&
      <ListCount totalResults={props.totalResults} />
    }

    {props.pagination &&
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
