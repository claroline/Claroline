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

const DataForm = {
  propTypes: {
    name: T.string.isRequired,
    type: T.string,
    label: T.string.isRequired,
    help: T.string,
    hideLabel: T.bool,
    disabled: T.bool,
    error: T.string,
    value: T.any,
    onChange: T.func.isRequired
  },
  defaultProps: {}
}

export {
  DataProperty,
  DataCell,
  DataDetails,
  DataForm,
  DataSearch
}
