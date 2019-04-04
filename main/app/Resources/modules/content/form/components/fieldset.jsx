import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'
import set from 'lodash/set'

import {createFieldsetDefinition} from '#/main/app/content/form/utils'
import {FormGroup} from '#/main/app/content/form/components/group'

import {DataInput} from '#/main/app/data/components/input'

// todo : restore readOnly
// todo : add auto focus

class FormField extends Component {
  constructor(props) {
    super(props)

    this.onChange = this.onChange.bind(this)
    this.onError = this.onError.bind(this)
  }

  onChange(value) {
    this.props.update(this.props.name, value, this.props.onChange)
  }

  onError(error) {
    this.props.setErrors(this.props.name, error)
  }

  render() {
    return (
      <DataInput
        id={this.props.id}
        type={this.props.type}
        label={this.props.label}
        hideLabel={this.props.hideLabel}
        options={this.props.options}
        help={this.props.help}
        placeholder={this.props.placeholder}
        size={this.props.size}
        required={this.props.required}
        disabled={this.props.disabled}
        validating={this.props.validating}

        value={this.props.value}
        error={this.props.error}

        onChange={this.onChange}
        onError={this.onError}
      />
    )
  }
}

FormField.propTypes = {
  id: T.string.isRequired,
  name: T.string.isRequired,
  type: T.string.isRequired,
  label: T.string.isRequired,
  hideLabel: T.bool,
  options: T.object, // depends on the data type
  help: T.oneOfType([T.string, T.arrayOf(T.string)]),
  placeholder: T.any, // depends on the data type
  size: T.oneOf(['sm', 'lg']),
  onChange: T.func,

  // field state
  required: T.bool,
  disabled: T.bool,
  validating: T.bool,

  // field data
  value: T.any, // depends on the data type
  error: T.oneOfType([
    T.string,
    T.arrayOf(T.string),
    T.object
  ]),

  // form methods
  update: T.func.isRequired,
  setErrors: T.func.isRequired
}

class FormFieldset extends Component {
  constructor(props) {
    super(props)

    this.update = this.update.bind(this)
    this.setErrors = this.setErrors.bind(this)
  }

  getFieldId(field) {
    let id = this.props.id ? `${this.props.id}-` : ''

    id += field.name.replace(/\./g, '-')

    return id
  }

  renderFields(fields) {
    let rendered = []

    fields.map(field => {
      if (field.render) {
        rendered.push(
          <FormGroup
            key={field.name}
            id={this.getFieldId(field)}
            label={field.label}
            hideLabel={field.hideLabel}
            help={field.help}
            optional={!field.required}
            error={get(this.props.errors, field.name)}
            warnOnly={!this.props.validating}
          >
            {field.render(this.props.data, this.props.errors)}
          </FormGroup>
        )
      } else {
        rendered.push(
          <FormField
            key={field.name}
            id={this.getFieldId(field)}
            name={field.name}
            type={field.type}
            label={field.label}
            hideLabel={field.hideLabel}
            options={field.options}
            help={field.help}
            placeholder={field.placeholder}
            size={this.props.size}
            required={field.required}
            disabled={this.props.disabled || (typeof field.disabled === 'function' ? field.disabled(this.props.data) : field.disabled)}
            validating={this.props.validating}

            value={field.calculated ? field.calculated(this.props.data) : get(this.props.data, field.name)}
            error={get(this.props.errors, field.name)}
            onChange={field.onChange}
            update={this.update}
            setErrors={this.setErrors}
          />
        )
      }

      if (field.linked && 0 !== field.linked.length) {
        rendered.push(
          <div className="sub-fields" key={`${field.name}-subset`}>
            {this.renderFields(field.linked)}
          </div>
        )
      }
    })

    return rendered
  }

  update(name, value, onChange) {
    this.props.updateProp(name, value)
    if (onChange) {
      onChange(value)
    }
  }

  setErrors(name, error) {
    const newErrors = this.props.errors ? cloneDeep(this.props.errors) : {}
    set(newErrors, name, error)

    this.props.setErrors(newErrors)
  }

  render() {
    const fields = createFieldsetDefinition(this.props.fields, this.props.data)

    return (
      <fieldset
        id={this.props.id}
        className={this.props.className}
        disabled={this.props.disabled}
      >
        {this.renderFields(fields)}

        {this.props.children}
      </fieldset>
    )
  }
}

FormFieldset.propTypes = {
  id: T.string,
  className: T.string,
  disabled: T.bool,
  size: T.oneOf(['sm', 'lg']),
  errors: T.object,
  validating: T.bool,
  data: T.object,
  fields: T.arrayOf(T.shape({
    // TODO : fields propTypes
  })).isRequired,
  setErrors: T.func.isRequired,
  updateProp: T.func.isRequired,
  children: T.node
}

FormFieldset.defaultProps = {
  disabled: false,
  data: {}
}

export {
  FormFieldset
}
