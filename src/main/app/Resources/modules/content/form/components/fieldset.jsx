import React, {Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import set from 'lodash/set'

import {Alert} from '#/main/app/components/alert'
import {toKey} from '#/main/app/utils/text'
import {Html} from '#/main/app/components/html'
import {DataInput} from '#/main/app/data/components/input'

/**
 * ATTENTION: as it's only to be used in the FormData component, the `fields` are not defaulted by the component.
 * You should consider apply `createFieldsetDefinition` on your list of fields before using it.
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

  setErrors(name, error, onError) {
    const newErrors = this.props.errors ? cloneDeep(this.props.errors) : {}
    set(newErrors, name, error)

    this.props.setErrors(newErrors)
    if (onError) {
      onError(newErrors)
    }
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
        customInput = createElement(field.component, this.props)
      } else if (field.render) {
        customInput = field.render(this.props.data, this.props.errors)
      }

      rendered.push(
        <DataInput
          key={field.name}
          id={this.getFieldId(field)}
          name={field.name}
          type={field.type}
          icon={field.icon}
          label={field.label}
          hideLabel={field.hideLabel}
          options={field.options}
          help={field.help}
          placeholder={field.placeholder}
          size={this.props.size || field.size}
          required={field.required}
          recommended={field.recommended}
          disabled={this.props.disabled || (typeof field.disabled === 'function' ? field.disabled(this.props.data) : field.disabled)}
          autoFocus={field.autoFocus}
          additional={field.additional}

          value={value}
          error={get(this.props.errors, field.name)}
          onChange={(value) => this.update(field.name, value, field.onChange)}
          onError={(error) => this.setErrors(field.name, error, field.onError)}
        >
          {customInput}
        </DataInput>
      )

      if (field.linked && 0 !== field.linked.length) {
        rendered.push(
          <div className="sub-fields" key={`${field.name}-subset`} role="presentation">
            {this.renderFields(field.linked)}
          </div>
        )
      }
    })

    return rendered
  }

  renderHelp() {
    if (!isEmpty(this.props.help)) {
      const helps = Array.isArray(this.props.help) ? this.props.help : [this.props.help]

      return helps.map(help =>
        <Alert key={toKey(help)} type="info" className="mb-0">
          <Html>{help}</Html>
        </Alert>
      )
    }

    return null
  }

  render() {
    return (
      <>
        {this.renderHelp()}
        {this.renderFields(this.props.fields)}
      </>
    )
  }
}

FormFieldset.propTypes = {
  id: T.string,
  disabled: T.bool,
  size: T.oneOf(['sm', 'lg']),
  errors: T.object,
  data: T.object,
  help: T.oneOfType([T.string, T.arrayOf(T.string)]),
  fields: T.arrayOf(T.shape({
    // fields propTypes
  })).isRequired,
  setErrors: T.func.isRequired,
  updateProp: T.func.isRequired
}

FormFieldset.defaultProps = {
  disabled: false,
  data: {}
}

export {
  FormFieldset
}
