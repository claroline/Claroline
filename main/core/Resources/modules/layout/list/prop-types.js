import {PropTypes as T} from 'prop-types'

import {getTypes} from '#/main/core/layout/data'

/**
 * Action available for data in the list.
 * Bulk and Row actions uses the same interface.
 *
 * @type {object}
 */
const DataAction = {
  propTypes: {
    /**
     * The label associated to the action.
     *
     * @type {string}
     */
    label: T.string.isRequired,

    /**
     * The action itself (an URL or a function to call).
     * If action is a function it will receive an array of data objects as param.
     *
     * @type {string|function}
     */
    action: T.oneOfType([T.string, T.func]).isRequired,
    icon: T.string,

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
    context: T.oneOf(['row', 'selection']),
    isDangerous: T.bool
  }
}

/**
 * Definition of a data object property.
 *
 * @type {object}
 */
const DataProperty = {
  propTypes: {
    /**
     * The name (or path) of the property.
     * It's used to access the value inside the parent object.
     *
     * N.B. It can be any selector understandable by lodash/set & lodash/get.
     *
     * @type {string}
     */
    name: T.string.isRequired,

    /**
     * The label associated to the property.
     *
     * @type {string}
     */
    label: T.string.isRequired,

    /**
     * The data type (eg. string, number, boolean).
     *
     * @type {string}
     */
    type: T.oneOf(Object.keys(getTypes())),

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
     * A list of options to configure the data type (eg. the list of choices of an enum).
     *
     * @type {object}
     */
    options: T.object,

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
  },

  defaultProps: {
    type: 'string',
    alias: null,
    options: {},

    displayed: false,
    displayable: true,
    filterable: true,
    sortable: true
  }
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
      T.shape(DataProperty.propTypes)
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
    actions: T.arrayOf(
      T.shape(DataAction.propTypes)
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
    onClick: T.oneOfType([T.string, T.func]), // either a url or a custom func to execute
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

export {
  DataAction,
  DataProperty,
  DataCard,
  DataListView
}
