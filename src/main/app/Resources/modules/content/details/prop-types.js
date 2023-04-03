import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {DataProperty} from '#/main/app/data/types/prop-types'

const DataDetailsProperty = {
  propTypes: merge({}, DataProperty.propTypes, {
    hideLabel: T.bool,
    displayed: T.oneOfType([
      T.bool,
      T.func // a function that receives the whole details data and returns a bool
    ])
  }),
  defaultProps: merge({}, DataProperty.defaultProps, {
    hideLabel: false,
    displayed: true
  })
}

// todo merge with DataFormSection
const DataDetailsSection = {
  propTypes: {
    icon: T.string,
    title: T.string.isRequired,
    className: T.string,
    primary: T.bool,
    displayed: T.oneOfType([
      T.bool,
      T.func // a function that receives the whole details data and returns a bool
    ]),
    defaultOpened: T.bool,
    fields: T.arrayOf(T.shape(
      DataDetailsProperty.propTypes
    )),
    component: T.node, // TODO : add warn if component and render are defined on the same section
    render: T.func
  },
  defaultProps: {
    primary: false,
    displayed: true,
    defaultOpened: false,
    fields: []
  }
}

export {
  DataDetailsSection,
  DataDetailsProperty
}
