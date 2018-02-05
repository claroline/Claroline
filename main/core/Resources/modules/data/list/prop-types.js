import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {DataProperty} from '#/main/core/data/prop-types'
import {Action} from '#/main/core/layout/button/prop-types'

/**
 * Action available for data in the list.
 * Bulk and Row actions uses the same interface.
 *
 * @type {object}
 */
const DataListAction = {
  propTypes: merge({}, Action.propTypes, {
    /**
     * A function to calculate if the action should be disabled.
     * It receives the list of data objects as param.
     */
    disabled: T.func,

    /**
     * A function to calculate if the action should be displayed.
     * It receives the list of data objects as param.
     */
    displayed: T.func,

    /**
     * Defines if the action is available as row action or bulk action.
     * If not set, action will be available in both context
     *
     * @type {string}
     */
    context: T.oneOf(['row', 'selection'])
  }),
  defaultProps: merge({}, Action.defaultProps)
}

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
     * is in a sub-object (eg. `meta.published` can be referenced using `published`).
     *
     * @type {string}
     */
    alias: T.string,

    /**
     * The calculated value for virtual properties.
     *
     * @param {object} row - The full row data.
     *
     * @type {mixed} - The computed value. Type depends on the data type.
     */
    calculated : T.func, // only used in tables representation

    /**
     * Customizes how the property is rendered in `table` like representations.
     *
     * @param {object} row
     *
     * @return {T.node}
     */
    renderer: T.func, // only used in tables representation

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
    sortable: T.bool
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
 * Definition of a list view (eg. table, grid)
 *
 * @type {object}
 */
const DataListView = {
  propTypes: {
    size: T.oneOf(['sm', 'md', 'lg']).isRequired,
    data: T.arrayOf(T.object).isRequired,
    count: T.number.isRequired,
    columns: T.arrayOf(
      T.shape(DataListProperty.propTypes)
    ).isRequired,
    sorting: T.shape({
      current: T.shape({
        property: T.string,
        direction: T.number
      }).isRequired,
      updateSort: T.func.isRequired
    }),
    selection: T.shape({
      current: T.array.isRequired,
      toggle: T.func.isRequired,
      toggleAll: T.func.isRequired
    }),

    /**
     * Data primary action (aka open/edit action for rows in most cases).
     */
    primaryAction: T.shape({
      disabled: T.func,
      action: T.oneOfType([T.string, T.func]).isRequired
    }),

    actions: T.arrayOf(
      T.shape(DataListAction.propTypes)
    ),

    /**
     * A function to normalize data for card display.
     * - the data row is passed as argument
     * - the func MUST return an object respecting `DataCard.propTypes`.
     *
     * It's required to enable cards based display modes.
     */
    card: T.func.isRequired
  },

  defaultProps: {
    actions: []
  }
}

/**
 * Definition of grid card data.
 *
 * @type {object}
 */
const DataCard = {
  propTypes: {
    // onClick: T.oneOfType([T.string, T.func]), // either a url or a custom func to execute
    className: T.string,
    poster: T.string,
    icon: T.oneOfType([T.string, T.element]).isRequired,
    title: T.string.isRequired,
    subtitle: T.string,
    contentText: T.string,
    flags: T.arrayOf(
      T.arrayOf(T.string)
    ),
    footer: T.node,
    footerLong: T.node
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
 * Definition of the search feature.
 *
 * @type {object}
 */
const DataListSearch = {
  propTypes: {
    current: T.arrayOf(T.shape({
      property: T.string.isRequired,
      value: T.any
    })).isRequired,
    addFilter: T.func.isRequired,
    removeFilter: T.func.isRequired
  }
}

/**
 * Definition of the pagination feature.
 *
 * @type {object}
 */
const DataListPagination = {
  propTypes: {
    current: T.number,
    pageSize: T.number.isRequired,
    changePage: T.func.isRequired,
    updatePageSize: T.func.isRequired
  }
}

export {
  DataListAction,
  DataListProperty,
  DataCard,
  DataListView,
  DataListSelection,
  DataListSearch,
  DataListPagination
}
