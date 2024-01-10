import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {DataProperty} from '#/main/app/data/types/prop-types'

import {constants as searchConst} from '#/main/app/content/search/constants'
import {constants as listConst} from '#/main/app/content/list/constants'

/**
 * Definition of a data object property.
 *
 * @type {object}
 */
const DataListProperty = {
  propTypes: merge({}, DataProperty.propTypes, {
    /**
     * An alias for the property.
     * If defined, filters and sortBy will use it to retrieve the property.
     *
     * This permits to simplify queryStrings and server communication when prop
     * is in a sub-object (e.g. `meta.published` can be referenced using `published`).
     *
     * @type {string}
     */
    alias: T.string,

    /**
     * Defines if the property is displayed by default in `table` like representations.
     *
     * @type {bool}
     */
    displayed: T.bool, // only used in tables representation

    /**
     * Defines if the property is the primary data column (will hold primaryAction if displayed, will also take more space).
     */
    primary: T.bool, // only used in tables representation

    /**
     * Defines if the property can be displayed in `table` like representations.
     *
     * @type {bool}
     */
    displayable: T.bool, // only used in tables representation

    /**
     * Defines if the property can be used to filter the data list.
     *
     * @type {bool}
     */
    filterable: T.bool,

    /**
     * Defines if the property can be used to sort the data list.
     *
     * @type {bool}
     */
    sortable: T.bool,

    order: T.number
  }),

  defaultProps: merge({}, DataProperty.defaultProps, {
    alias: null,
    displayed: false,
    displayable: true,
    filterable: true,
    sortable: true
  })
}

/**
 * Definition of the sorting feature.
 *
 * @type {object}
 */
const DataListSorting = {
  propTypes: {
    current: T.shape({
      property: T.string,
      direction: T.number
    }).isRequired,
    updateSort: T.func.isRequired
  }
}

/**
 * Definition of the selection feature.
 *
 * @type {object}
 */
const DataListSelection = {
  propTypes: {
    current: T.array.isRequired,
    toggle: T.func.isRequired,
    toggleAll: T.func.isRequired
  }
}

/**
 * Definition of the display feature.
 *
 * @type {object}
 */
const DataListDisplay = {
  propTypes: {
    /**
     * Available formats.
     */
    available: T.arrayOf(
      T.oneOf(listConst.DISPLAY_MODES)
    ),

    /**
     * Current format.
     */
    current: T.oneOf(listConst.DISPLAY_MODES),

    /**
     * A callback fired when the display mode is changed by the user.
     */
    changeDisplay: T.func
  }
}

/**
 * Definition of the search feature.
 *
 * @type {object}
 */
const DataListSearch = {
  propTypes: {
    mode: T.oneOf(Object.keys(searchConst.SEARCH_TYPES)).isRequired,
    current: T.arrayOf(T.shape({
      property: T.string.isRequired,
      locked: T.bool,
      value: T.any
    })).isRequired,
    addFilter: T.func.isRequired,
    removeFilter: T.func.isRequired,
    resetFilters: T.func.isRequired
  }
}

/**
 * Definition of the pagination feature.
 *
 * @type {object}
 */
const DataListPagination = {
  propTypes: {
    /**
     * The current page displayed by the list
     */
    current: T.number,
    /**
     * The number of items to display on each page.
     */
    pageSize: T.number.isRequired,

    changePage: T.func.isRequired,
    updatePageSize: T.func.isRequired
  }
}

/**
 * Definition of a list view (e.g. table, grid)
 *
 * @type {object}
 */
const DataListView = {
  propTypes: {
    data: T.arrayOf(T.object).isRequired,
    count: T.number.isRequired,
    definition: T.arrayOf(
      T.shape(DataListProperty.propTypes)
    ).isRequired,
    sorting: T.shape(
      DataListSorting.propTypes
    ),
    selection: T.shape(
      DataListSelection.propTypes
    ),

    /**
     * Data primary action (aka open/edit action for rows in most cases).
     */
    primaryAction: T.func,

    actions: T.func
  }
}

export {
  DataListProperty,
  DataListView,
  DataListDisplay,
  DataListSorting,
  DataListSelection,
  DataListSearch,
  DataListPagination
}
