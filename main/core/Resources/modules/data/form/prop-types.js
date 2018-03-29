import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {DataProperty} from '#/main/core/data/prop-types'

// todo use layout/form/prop-types

const DataFormProperty = {
  propTypes: merge({}, DataProperty.propTypes, {
    /**
     * The calculated value for virtual properties.
     *
     * @param {object} row - The full row data.
     *
     * @type {mixed} - The computed value. Type depends on the data type.
     */
    calculated : T.func,

    // form configuration
    help: T.string,
    hideLabel: T.bool,
    displayed: T.oneOfType([
      T.bool,
      T.func // a function that receives the whole form data and returns the new state
    ]),
    disabled: T.oneOfType([
      T.bool,
      T.func // a function that receives the whole form data and returns the new state
    ]),
    readOnly: T.bool,
    options: T.object,
    required: T.bool,

    // field methods
    onChange: T.func,
    validate: T.func
  }),
  defaultProps: merge({}, DataProperty.defaultProps, {
    required: false,
    hideLabel: false,
    disabled: false,
    readOnly: false,
    displayed: true
  })
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
