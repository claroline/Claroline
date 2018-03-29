import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import invariant from 'invariant'
import merge from 'lodash/merge'

import {getTypeOrDefault} from '#/main/core/data'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

import {validateProp} from '#/main/core/data/form/validator'

class FormField extends Component {
  render() {
    const typeDef = getTypeOrDefault(this.props.type)
    invariant(typeDef.components.form, `form component cannot be found for '${this.props.type}'`)

    if (this.props.readOnly) {
      return (
        <FormGroup
          id={this.props.name}
          label={this.props.label}
          help={this.props.help}
        >
          <div>
            {typeDef.render(this.props.value, this.props.options) || '-'}
          </div>
        </FormGroup>
      )
    } else {
      return React.createElement(typeDef.components.form, merge({}, this.props.options, {
        id: this.props.name,
        label: this.props.label,
        hideLabel: this.props.hideLabel,
        disabled: this.props.disabled,
        help: this.props.help,
        error: this.props.error,
        warnOnly: !this.props.validating,
        optional: !this.props.required,
        value: this.props.value,
        onChange: (value) => {
          if (this.props.onChange) {
            this.props.onChange(value)
          }

          this.props.updateProp(this.props.name, value) // todo : maybe disable for calculated value
          this.props.setErrors(validateProp(this.props, value))
        }
      }))
    }
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
  error: T.oneOfType([T.string, T.object]), // object is for complex types like collection
  validating: T.bool,
  onChange: T.func,
  validate: T.func,
  updateProp: T.func,
  setErrors: T.func
}

export {
  FormField
}
