import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {DataProperty} from '#/main/core/data/prop-types'

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
    primaryAction: T.func,

    actions: T.func
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
  DataListProperty,
  DataListView,
  DataListSelection,
  DataListSearch,
  DataListPagination
}
