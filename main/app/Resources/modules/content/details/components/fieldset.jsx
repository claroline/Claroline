import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {DataDisplay} from '#/main/app/data/components/display'

/**
 * ATTENTION : as it's only be used in the DetailsData component, the `fields` are not defaulted by the component.
 * You should consider apply `createFieldsetDefinition` on your fields list before using it.
 */
class DetailsFieldset extends Component {
  getFieldId(field) {
    let id = this.props.id ? `${this.props.id}-` : ''

    id += field.name.replace(/\./g, '-')

    return id
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
        <DataDisplay
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

          value={value}
          error={get(this.props.errors, field.name)}
        >
          {customInput}
        </DataDisplay>
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

DetailsFieldset.propTypes = {
  id: T.string,
  className: T.string,
  disabled: T.bool,
  size: T.oneOf(['sm', 'lg']),
  errors: T.object,
  data: T.object,
  fields: T.arrayOf(T.shape({
    // TODO : fields propTypes
  })).isRequired,
  children: T.node
}

DetailsFieldset.defaultProps = {
  data: {}
}

export {
  DetailsFieldset
}
