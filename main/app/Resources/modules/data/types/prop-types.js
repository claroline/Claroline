import {PropTypes as T} from 'prop-types'

import {constants} from '#/main/app/data/types/constants'

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
      description: T.string
    }),

    /**
     * The list of configuration fields for the data type.
     * It gets the current options values as param.
     *
     * NB. It's only used to generated configuration form for `creatable` data types.
     *
     * This may be better to be a real component for more control.
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
      table: T.any, // todo : rename into `cell`

      group: T.any,
      input: T.any,
      display: T.any,
      filter: T.any,
      cell: T.any
    })
  },
  defaultProps: {
    meta: {
      creatable: false
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
    placeholder: T.string,
    search: T.any,
    isValid: T.bool.isRequired,
    updateSearch: T.func.isRequired
  },
  defaultProps: {}
}

export {
  DataType,
  DataProperty,
  DataCell,
  DataDetails,
  DataSearch
}
