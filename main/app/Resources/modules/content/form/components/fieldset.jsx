import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'
import set from 'lodash/set'

import {DataInput} from '#/main/app/data/components/input'

// todo : restore readOnly
// todo : add auto focus

/**
 * ATTENTION : as it's only be used in the FormData component, the `fields` are not defaulted by the component.
 * You should consider apply `createFieldsetDefinition` on your fields list before using it.
 */
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

  renderFields(fields) {
    let rendered = []

    fields.map(field => {
      let value
      if (undefined !== field.calculated) {
        value = typeof field.calculated === 'function' ? field.calculated(this.props.data) : field.calculated
      } else {
        value = get(this.props.data, field.name)
      }

      let customInput
      if (field.component) {
        customInput = field.component
      } else if (field.render) {
        customInput = field.render(this.props.data, this.props.errors)
      }

      rendered.push(
        <DataInput
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

          value={value}
          error={get(this.props.errors, field.name)}
          onChange={(value) => this.update(field.name, value, field.onChange)}
          onError={(error) => this.setErrors(field.name, error)}
        >
          {customInput}
        </DataInput>
      )

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

  render() {
    return (
      <fieldset
        id={this.props.id}
        className={this.props.className}
        disabled={this.props.disabled}
      >
        {this.renderFields(this.props.fields)}

        {this.props.children}
      </fieldset>
    )
  }
}

FormFieldset.propTypes = {
  id: T.string,
  className: T.string,
  disabled: T.bool,
  mode: T.string.isRequired,
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
