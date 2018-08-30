import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {constants as listConstants} from '#/main/app/content/list/constants'

import {WidgetInstance} from '#/main/core/widget/content/prop-types'

const ListWidgetParameters = {
  propTypes: {
    maxResults: T.number,
    count: T.bool,

    // display feature
    display: T.string,
    availableDisplays: T.arrayOf(T.string),

    // sort feature
    sortable: T.bool,
    sorting: T.string,
    availableSort: T.arrayOf(T.string),

    // filter feature
    filterable: T.bool,
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
    columnsFilterable: T.bool,
    columns: T.arrayOf(T.string),
    availableColumns: T.arrayOf(T.string)

    // grid config (todo)
  },
  defaultProps: {
    count: listConstants.DEFAULT_FEATURES.count,

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
    sortable: false,
    availableSort: [],

    // filter feature
    filterable: false,
    availableFilters: [],

    // pagination feature
    paginated: listConstants.DEFAULT_FEATURES.paginated,
    pageSize: listConstants.DEFAULT_PAGE_SIZE,
    availablePageSizes: listConstants.AVAILABLE_PAGE_SIZES,

    // table config
    columnsFilterable: false,
    availableColumns: []
  }
}

const ListWidget = implementPropTypes({}, WidgetInstance, {
  parameters: ListWidgetParameters.propTypes
}, {
  parameters: ListWidgetParameters.defaultProps
})

export {
  ListWidget,
  ListWidgetParameters
}
