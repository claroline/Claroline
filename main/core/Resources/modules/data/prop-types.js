import {PropTypes as T} from 'prop-types'

//import {getTypes} from '#/main/core/data'

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
    /*type: T.oneOf(Object.keys(getTypes())),*/
    type: T.string,

    /**
     * A list of options to configure the data type (eg. the list of choices of an enum).
     *
     * @type {object}
     */
    options: T.object
  },
  defaultProps: {
    type: 'string',
    options: {}
  }
}

const DataCell = {
  propTypes: {
    data: T.any
  },
  defaultTypes: {
    data: null
  }
}

const DataDetails = {
  propTypes: {
    data: T.any
  },
  defaultTypes: {
    data: null
  }
}

const DataSearch = {
  propTypes: {
    search: T.string.isRequired,
    isValid: T.bool.isRequired,
    updateSearch: T.func.isRequired
  },
  defaultProps: {}
}

/**
 * Definition of card data.
 *
 * @type {object}
 */
const DataCard = {
  propTypes: {
    id: T.string.isRequired,
    size: T.oneOf(['sm', 'lg']),
    orientation: T.oneOf(['col', 'row']),
    className: T.string,
    poster: T.string,
    icon: T.oneOfType([T.string, T.element]).isRequired,
    title: T.string.isRequired,
    subtitle: T.string,
    contentText: T.string,
    flags: T.arrayOf(
      T.arrayOf(T.oneOfType([T.string, T.number]))
    ),
    primaryAction: T.shape({
      action: T.oneOfType([T.string, T.func]).isRequired,
      disabled: T.bool
    }),
    actions: T.arrayOf(T.shape({

    })),

    footer: T.node
  },
  defaultProps: {
    size: 'sm',
    orientation: 'row',
    level: 2,
    actions: [],
    flags: []
  }
}

export {
  DataProperty,
  DataCell,
  DataDetails,
  DataCard,
  DataSearch
}
