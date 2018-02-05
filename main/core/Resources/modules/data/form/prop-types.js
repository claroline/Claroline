import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

// todo use layout/form/prop-types

const DataFormProperty = {
  propTypes: {
    // field info
    name: T.string.isRequired,
    type: T.string,
    label: T.string.isRequired,
    help: T.string,

    // field configuration
    hideLabel: T.bool,
    displayed: T.bool,
    disabled: T.bool,
    readOnly: T.bool,
    options: T.object,
    required: T.bool,
    calculated : T.any,

    // field methods
    onChange: T.func,
    validate: T.func
  },
  defaultProps: {
    options: {},
    required: false,
    hideLabel: false,
    disabled: false,
    readOnly: false,
    displayed: true
  }
}

// todo merge with DataDetailsSection
const DataFormSection = {
  propTypes: {
    id: T.string.isRequired,
    icon: T.string,
    title: T.string.isRequired,
    primary: T.bool,
    displayed: T.bool,
    defaultOpened: T.bool,
    fields: T.arrayOf(T.shape(
      merge({}, DataFormProperty.propTypes, {
        // children
        linked: T.arrayOf(T.shape(
          DataFormProperty.propTypes
        ))
      })
    )).isRequired,
    advanced: T.shape({
      showText: T.string,
      hideText: T.string,
      fields: T.arrayOf(T.shape(
        merge({}, DataFormProperty.propTypes, {
          // children
          linked: T.arrayOf(T.shape(
            DataFormProperty.propTypes
          ))
        })
      )).isRequired
    })
  },
  defaultProps: {
    primary: false,
    displayed: true,
    defaultOpened: false
  }
}

export {
  DataFormSection,
  DataFormProperty
}
