import {PropTypes as T} from 'prop-types'

const Property = {
  propTypes: {
    name: T.string.isRequired,
    type: T.string,
    description: T.string,
    required: T.bool,
    isArray: T.bool
  },
  defaultProps: {
    required: false,
    isArray: false
  }
}

const OneOf = {
  propTypes: {
    oneOf: T.arrayOf(T.shape(
      Property.propTypes
    )).isRequired,
    description: T.string, // it should not be used because it always contains the same auto generated string
    required: T.bool
  },
  defaultProps: {
    oneOf: [],
    required: false
  }
}

const Schema = {
  propTypes: {
    properties: T.arrayOf(T.oneOfType([
      T.shape(Property.propTypes),
      T.shape(OneOf.propTypes)
    ])),
    identifiers: T.array
  },
  defaultProps: {
    properties: [],
    identifiers: []
  }
}

export {
  Schema
}
