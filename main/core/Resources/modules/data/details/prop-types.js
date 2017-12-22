import {PropTypes as T} from 'prop-types'

const DataDetailsProperty = {
  propTypes: {
    name: T.string.isRequired,
    type: T.string,
    label: T.string.isRequired,
    hideLabel: T.bool,
    displayed: T.bool,
    options: T.object
  },
  defaultProps: {
    options: {},
    hideLabel: false,
    displayed: true
  }
}

// todo merge with DataFormSection
const DataDetailsSection = {
  propTypes: {
    id: T.string.isRequired,
    icon: T.string,
    title: T.string.isRequired,
    primary: T.bool,
    displayed: T.bool,
    defaultOpened: T.bool,
    fields: T.arrayOf(T.shape(
      DataDetailsProperty.propTypes
    )).isRequired
  },
  defaultProps: {
    primary: false,
    displayed: true,
    defaultOpened: false
  }
}

export {
  DataDetailsSection,
  DataDetailsProperty
}
