import {PropTypes as T} from 'prop-types'

import {constants as listConstants} from '#/main/app/content/list/constants'
import {constants as searchConstants} from '#/main/app/content/search/constants'

const ListParameters = {
  propTypes: {
    count: T.bool,
    actions: T.bool,

    // display feature
    display: T.string,
    availableDisplays: T.arrayOf(T.string),

    // sort feature
    sorting: T.string,
    availableSort: T.arrayOf(T.string),

    // filter feature
    searchMode: T.oneOf(
      Object.keys(searchConstants.SEARCH_TYPES)
    ),
    filters: T.arrayOf(T.shape({
      property: T.string.isRequired,
      value: T.any,
      locked: T.bool
    })),
    availableFilters: T.arrayOf(T.string),

    // pagination feature
    paginated: T.bool,
    pageSize: T.number,
    availablePageSizes: T.arrayOf(T.number),

    // table config
    columns: T.arrayOf(T.string),
    availableColumns: T.arrayOf(T.string)

    // grid config (todo)
  },
  defaultProps: {
    // display feature
    display: listConstants.DEFAULT_DISPLAY_MODE,
    availableDisplays: [
      listConstants.DISPLAY_TABLE,
      listConstants.DISPLAY_TABLE_SM,
      listConstants.DISPLAY_TILES,
      listConstants.DISPLAY_TILES_SM,
      listConstants.DISPLAY_LIST_SM,
      listConstants.DISPLAY_LIST
    ],

    // sort feature
    availableSort: [],

    // filter feature
    searchMode: searchConstants.DEFAULT_SEARCH_TYPE,
    availableFilters: [],

    // pagination feature
    paginated: true,
    pageSize: listConstants.DEFAULT_PAGE_SIZE,
    availablePageSizes: listConstants.AVAILABLE_PAGE_SIZES,

    // table config
    availableColumns: []
  }
}

export {
  ListParameters
}
