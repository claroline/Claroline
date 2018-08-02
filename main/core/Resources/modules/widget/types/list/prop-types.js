import {PropTypes as T} from 'prop-types'

import {constants as listConstants} from '#/main/app/content/list/constants'

const ListWidgetParameters = {
  propTypes: {
    filterable: T.bool,
    sortable: T.bool,
    paginated: T.bool,
    pageSize: T.number,
    defaultFilters: T.array,
    availableColumns: T.array
  },
  defaultProps: {
    filterable: listConstants.DEFAULT_FEATURES.filterable,
    sortable: listConstants.DEFAULT_FEATURES.sortable,
    paginated: listConstants.DEFAULT_FEATURES.paginated,
    pageSize: listConstants.DEFAULT_PAGE_SIZE
  }
}

export {
  ListWidgetParameters
}
