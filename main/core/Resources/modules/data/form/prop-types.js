import {PropTypes as T} from 'prop-types'

// todo use layout/form/prop-types

const DataFormProperty = {
  propTypes: {
    name: T.string.isRequired,
    type: T.string,
    label: T.string.isRequired,
    help: T.string,
    hideLabel: T.bool,
    displayed: T.bool,
    disabled: T.bool,
    readOnly: T.bool,
    options: T.object,
    required: T.bool,
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
      DataFormProperty.propTypes
    )).isRequired,
    advanced: T.shape({
      showText: T.string,
      hideText: T.string,
      fields: T.arrayOf(T.shape(
        DataFormProperty.propTypes
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
