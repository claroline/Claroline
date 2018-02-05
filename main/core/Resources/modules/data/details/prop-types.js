import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {DataProperty} from '#/main/core/data/prop-types'

const DataDetailsProperty = {
  propTypes: merge({}, DataProperty.propTypes, {
    hideLabel: T.bool,
    displayed: T.bool
  }),
  defaultProps: merge({}, DataProperty.defaultProps, {
    hideLabel: false,
    displayed: true
  })
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
