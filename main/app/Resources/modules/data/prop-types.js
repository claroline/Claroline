import {PropTypes as T} from 'prop-types'

import {Action, PromisedAction} from '#/main/app/action/prop-types'

import {constants} from '#/main/app/data/constants'

/**
 * Describe the structure of a data type definition.
 */
const DataType = {
  /**
   * Information about the data type.
   */
  propTypes: {
    /**
     * The name of the DataType used to reference it in usages.
     */
    name: T.string.isRequired,

    meta: T.shape({
      creatable: T.bool,
      icon: T.string,
      label: T.string,
      description: T.string,
      noLabel: T.bool
    }),

    /**
     * The list of configuration fields for the data type.
     * It gets the current options values as param.
     *
     * NB. It's only used to generated configuration form for `creatable` data types.
     *
     * @return {array}
     */
    configure: T.func,

    /**
     * Parses a value.
     *
     * @param value
     *
     * @return {*} - the parsed value
     */
    parse: T.func,

    /**
     * Displays a value for the data type.
     *
     * @param raw
     *
     * @return {*} - the rendered value
     */
    render: T.func,

    /**
     * Validates a value provided for the data type.
     */
    validate: T.func,

    /**
     * Custom components for the data type.
     *
     * Keys :
     *   - search
     *   - form
     *   - table
     *   - details
     */
    components: T.shape({
      // todo : find correct types
      details: T.any, // todo : rename into `display`
      form: T.any, // todo : rename into `input` + `group`
      search: T.any, // todo : rename into `filter`
      table: T.any // todo : rename into `cell`
    })
  },
  defaultProps: {
    meta: {
      creatable: false,
      noLabel: false
    },
    configure: () => [],
    parse: (value) => value,
    render: (raw) => raw,
    validate: () => undefined,
    components: {}
  }
}

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
    type: T.string,

    /**
     * A list of options to configure the data type (eg. the list of choices of an enum).
     *
     * @type {object}
     */
    options: T.object,

    /**
     * The calculated value for virtual properties.
     *
     * @param {object} object - The full data object.
     *
     * @type {*} - The computed value. Type depends on the data type.
     */
    calculated : T.func,

    /**
     * A custom rendering function (it receives the whole data object as argument).
     *
     * @param {object} object
     *
     * @return {T.node}
     */
    render: T.func
  },
  defaultProps: {
    type: constants.DEFAULT_TYPE,
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
    id: T.string.isRequired,
    data: T.any,
    label: T.string,
    hideLabel: T.bool
  },
  defaultTypes: {
    data: null,
    hideLabel: false
  }
}

const DataSearch = {
  propTypes: {
    search: T.any,
    isValid: T.bool.isRequired,
    updateSearch: T.func.isRequired
  },
  defaultProps: {}
}

/**
 * Definition of card data.
 *
 * @type {object}
 *
 * @todo move elsewhere
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
    primaryAction: T.shape(
      Action.propTypes
    ),
    actions: T.oneOfType([
      // a regular array of actions
      T.arrayOf(T.shape(
        Action.propTypes
      )),
      // a promise that will resolve a list of actions
      T.shape(
        PromisedAction.propTypes
      )
    ]),

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
  DataType,
  DataProperty,
  DataCell,
  DataDetails,
  DataCard,
  DataSearch
}
