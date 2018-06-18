import React from 'react'
import {PropTypes as T} from 'prop-types'
import invariant from 'invariant'
import merge from 'lodash/merge'

import {getTypeOrDefault} from '#/main/core/data'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group'

import {validateProp} from '#/main/core/data/form/validator'

const FormField = props => {
  const typeDef = getTypeOrDefault(props.type)
  invariant(typeDef.components.form, `form component cannot be found for '${props.type}'`)

  if (props.readOnly) {
    // TODO : maybe reuse the details component if any.
    return (
      <FormGroup
        id={props.name}
        label={props.label}
        hideLabel={props.hideLabel}
        help={props.help}
      >
        {typeDef.render(props.value, props.options) || '-'}
      </FormGroup>
    )
  } else {
    return React.createElement(typeDef.components.form, merge({}, props.options, {
      id: props.name.replace(/\./g, '-'),
      label: props.label,
      hideLabel: props.hideLabel,
      disabled: props.disabled,
      help: props.help,
      error: props.error,
      warnOnly: !props.validating,
      optional: !props.required,
      value: props.value,
      onChange: (value) => {
        props.updateProp(props.name, value) // todo : maybe disable for calculated value

        if (props.onChange) {
          props.onChange(value)
        }

        props.setErrors(validateProp(props, value))
      }
    }))
  }
}

// todo : use the one defined in prop-types
FormField.propTypes = {
  name: T.string.isRequired,
  type: T.string,
  label: T.string.isRequired,
  help: T.oneOfType([T.string, T.arrayOf(T.string)]),
  hideLabel: T.bool,
  disabled: T.bool,
  readOnly: T.bool,
  options: T.object,
  required: T.bool,
  value: T.any,
  error: T.oneOfType([T.string, T.object]), // object is for complex types like collection
  validating: T.bool,
  onChange: T.func,
  updateProp: T.func,
  setErrors: T.func
}

export {
  FormField
}
