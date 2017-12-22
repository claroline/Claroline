import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import invariant from 'invariant'
import get from 'lodash/get'
import set from 'lodash/set'

import {Form} from '#/main/core/data/form/components/form.jsx'
import {actions} from '#/main/core/data/form/actions'
import {select} from '#/main/core/data/form/selectors'

const FormComponent = props =>
  <Form
    {...props}

    data={props.data}
    errors={props.errors}
    pendingChanges={props.pendingChanges}
    validating={props.validating}
    updateProp={props.updateProp}
    setErrors={props.setErrors}
  >
    {props.children}
  </Form>

FormComponent.propTypes = {
  /**
   * The name of the data in the form.
   *
   * It should be the key in the store where the list has been mounted
   * (aka where `makeFormReducer()` has been called).
   */
  name: T.string.isRequired,

  /**
   * Permits to connect the form on a sub-part of the data.
   * This is useful when the form is broken in multiple steps/pages
   *
   * It MUST be a valid lodash/get selector.
   */
  dataPart: T.string,

  /**
   * Custom parts of the form.
   */
  children: T.node,

  // retrieved from store
  data: T.object,
  errors: T.object,
  pendingChanges: T.bool,
  validating: T.bool,
  setErrors: T.func.isRequired,
  updateProp: T.func.isRequired
}

FormComponent.defaultProps = {
  data: {},
  errors: {},
  pendingChanges: false,
  validating: false
}

const FormContainer = connect(
  (state, ownProps) => {
    // get the root of the form in the store
    const formState = select.form(state, ownProps.name)

    invariant(undefined !== formState, `Try to connect form on undefined store '${ownProps.name}'.`)

    let data = select.data(formState)
    let errors = select.errors(formState)
    if (ownProps.dataPart) {
      // just select what is related to the managed data part
      data = get(data, ownProps.dataPart)
      errors = get(errors, ownProps.dataPart)
    }

    return {
      data: data,
      errors: errors,
      pendingChanges: select.pendingChanges(formState),
      validating: select.validating(formState)
    }
  },
  (dispatch, ownProps) => ({
    setErrors(errors) {
      if (ownProps.dataPart) {
        errors = set({}, ownProps.dataPart, errors)
      }

      dispatch(actions.setErrors(ownProps.name, errors))
    },

    updateProp(propName, propValue) {
      if (ownProps.dataPart) {
        propName = ownProps.dataPart+'.'+propName
      }

      dispatch(actions.updateProp(ownProps.name, propName, propValue))
    }
  })
)(FormComponent)

export {
  FormContainer
}