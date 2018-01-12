import React from 'react'
import {PropTypes as T} from 'prop-types'
import invariant from 'invariant'
import merge from 'lodash/merge'

import {getTypeOrDefault} from '#/main/core/data'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

import {validateProp} from '#/main/core/data/form/validator'

const FormField = props => {
  const typeDef = getTypeOrDefault(props.type)
  invariant(typeDef.components.form, `form component cannot be found for '${props.type}'`)

  if (props.readOnly) {
    return (
      <FormGroup
        id={props.name}
        label={props.label}
        help={props.help}
      >
        <div>
          {typeDef.render(props.value, props.options) || '-'}
        </div>
      </FormGroup>
    )
  } else {
    return React.createElement(typeDef.components.form, merge({}, props.options, {
      id: props.name,
      label: props.label,
      hideLabel: props.hideLabel,
      disabled: props.disabled,
      help: props.help,
      error: props.error,
      warnOnly: !props.validating,
      optional: !props.required,
      value: props.value,
      onChange: (value) => {
        if (props.onChange) {
          props.onChange(value)
        }

        props.updateProp(props.name, value) // todo : maybe disable for calculated value
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
  help: T.string,
  hideLabel: T.bool,
  disabled: T.bool,
  readOnly: T.bool,
  options: T.object,
  required: T.bool,
  value: T.any,
  error: T.string,
  validating: T.bool,
  onChange: T.func,
  validate: T.func,
  updateProp: T.func,
  setErrors: T.func
}

export {
  FormField
}
