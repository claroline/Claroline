import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/core/translation'
import {FormProp} from '#/main/app/content/form/components/prop'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button'

import {
  Field as FieldType,
  FieldChoiceCategory as FieldChoiceCategoryType
} from '#/plugin/claco-form/resources/claco-form/prop-types'

// TODO : find a way to reuse standard `fields` type

const FieldPreview = props =>
  <FormProp
    {...props}
    value={props.value}
    updateProp={(prop, value) => props.onChange(value)}
    setErrors={() => false}
  />

FieldPreview.propTypes = {
  value: T.any,
  onChange: T.func.isRequired
}

FieldPreview.defaultProps = {
  options: {}
}

class CategoryFieldsValues extends Component {
  constructor(props) {
    super(props)

    this.state = {
      showFieldSelector: false
    }
    this.updateFieldValue.bind(this)
  }

  addFieldValue(fieldId) {
    const field = this.props.fields.find(f => f.id === fieldId)

    if (field) {
      const newFieldValue = {
        id: makeId(),
        field: field
      }
      const fieldsValues = this.props.value.slice()
      fieldsValues.push(newFieldValue)

      this.props.onChange(fieldsValues)
    }

    this.setState({showFieldSelector: false})
  }

  updateFieldValue(index, value) {
    const fieldsValues = this.props.value.slice()
    fieldsValues[index]['value'] = value

    this.props.onChange(fieldsValues)
  }

  removeFieldValue(index) {
    const fieldsValues = this.props.value.slice()
    fieldsValues.splice(index, 1)

    this.props.onChange(fieldsValues)
  }

  formatField(fieldChoiceCategory) {
    const options = fieldChoiceCategory.field.options ? Object.assign({}, fieldChoiceCategory.field.options) : {}

    if (fieldChoiceCategory.field.type === 'choice') {
      options['choices'] = fieldChoiceCategory.field.options && fieldChoiceCategory.field.options.choices ?
        fieldChoiceCategory.field.options.choices.reduce((acc, choice) => {
          acc[choice.value] = choice.value

          return acc
        }, {}) :
        {}
    }
    const newValues = fieldChoiceCategory.field.type === 'choice' ?
      {options: options, name: fieldChoiceCategory.id} :
      {name: fieldChoiceCategory.id}

    return Object.assign({}, fieldChoiceCategory.field, newValues)
  }

  render() {
    return (
      <div className="field-list-control">
        {this.props.value.length > 0 &&
          <ul>
            {this.props.value.map((fieldChoiceCategory, index) =>
              <li key={index} className="field-item">
                <FieldPreview
                  {...this.formatField(fieldChoiceCategory)}
                  value={fieldChoiceCategory.value}
                  onChange={(value) => this.updateFieldValue(index, value)}
                />

                <div className="field-item-actions">
                  <TooltipButton
                    id={`field-choice-category-${index}-delete`}
                    title={trans('delete')}
                    className="btn-link-danger"
                    onClick={() => this.removeFieldValue(index)}
                  >
                    <span className="fa fa-fw fa-trash-o" />
                  </TooltipButton>
                </div>
              </li>
            )}
          </ul>
        }

        {this.props.value.length === 0 &&
          <div className="no-field-info">
            {trans('empty_fields_list')}
          </div>
        }

        {!this.state.showFieldSelector &&
          <button
            type="button"
            className="btn btn-default btn-block"
            onClick={() => this.setState({showFieldSelector: true})}
          >
            <span className="fa fa-plus icon-with-text-right"/>
            {trans('add_field')}
          </button>
        }

        {this.state.showFieldSelector &&
          <FormProp
            id="category-field-select"
            type="choice"
            name="category-field-select"
            label={trans('select_field')}
            hideLabel={true}
            options={{
              condensed: true,
              choices: this.props.fields.filter(f => ['file', 'date'].indexOf(f.type) === -1).reduce((acc, f) => {
                acc[f.id] = `[${trans(f.type)}] ${f.name}`

                return acc
              }, {})
            }}
            updateProp={(prop, value) => {
              this.addFieldValue(value)
            }}
            setErrors={() => false}
          />
        }
      </div>
    )
  }
}

CategoryFieldsValues.propTypes = {
  value: T.arrayOf(T.shape(FieldChoiceCategoryType.propTypes)),
  fields: T.arrayOf(T.shape(FieldType.propTypes)),
  onChange: T.func.isRequired
}

CategoryFieldsValues.defaultProps = {
  value: []
}

export {
  CategoryFieldsValues
}
