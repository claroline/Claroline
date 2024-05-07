import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {DataProperty} from '#/main/app/data/types/prop-types'
import {Action, PromisedAction} from '#/main/app/action/prop-types'
import {constants} from '#/main/app/content/form/constants'

const DataFormProperty = {
  propTypes: merge({}, DataProperty.propTypes, {
    // form configuration
    help: T.oneOfType([T.string, T.arrayOf(T.string)]),
    hideLabel: T.bool,
    displayed: T.oneOfType([
      T.bool,
      T.func // a function that receives the whole form data and returns a bool
    ]),
    required: T.bool,
    disabled: T.oneOfType([
      T.bool,
      T.func // a function that receives the whole form data and returns a bool
    ]),
    readOnly: T.bool,
    autoFocus: T.bool,

    // field methods
    onChange: T.func,
    validate: T.func
  }),
  defaultProps: merge({}, DataProperty.defaultProps, {
    mode: constants.FORM_MODE_SIMPLE,
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
    id: T.string,
    icon: T.string,
    title: T.string.isRequired,
    subtitle: T.string,
    help: T.string,
    primary: T.bool,
    displayed: T.oneOfType([T.bool, T.func]),
    disabled: T.oneOfType([T.bool, T.func]),
    defaultOpened: T.bool,
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
    fields: T.arrayOf(T.shape(
      merge({}, DataFormProperty.propTypes, {
        // children
        linked: T.arrayOf(T.shape(
          DataFormProperty.propTypes
        ))
      })
    )),
    component: T.oneOfType([T.func, T.object]),
    render: T.func
  },
  defaultProps: {
    mode: constants.FORM_MODE_SIMPLE,
    primary: false,
    displayed: true,
    defaultOpened: false,
    fields: []
  }
}

export {
  DataFormSection,
  DataFormProperty
}
