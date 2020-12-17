import {PropTypes as T} from 'prop-types'

import {constants} from '#/main/app/content/pagination/constants'

const Pagination = {
  propTypes: {
    totalResults: T.number.isRequired,
    current: T.number,
    pageSize: T.number,
    availableSizes: T.arrayOf(T.number),
    changePage: T.func.isRequired,
    updatePageSize: T.func.isRequired
  },
  defaultProps: {
    current: 0,
    pageSize: constants.DEFAULT_PAGE_SIZE,
    availableSizes: constants.AVAILABLE_PAGE_SIZES
  }
}

export {
  Pagination
}
