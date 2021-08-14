import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'
import set from 'lodash/set'

import {trans} from '#/main/app/intl/translation'
import {getType} from '#/main/app/data/types'
import {FormDataModal} from '#/main/app/modals/form/components/data'

class ParametersModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      options: props.data.options ? props.data.options : {},
      restrictions: props.data.restrictions ? props.data.restrictions : {},
      conditions: props.data.conditions ? props.data.conditions : {},
      typeDef: null,
      selectedField: props.conditionalFields && props.conditionalFields[this.props.data.id] ? props.conditionalFields[this.props.data.id] : {},
      conditionalFields: props.conditionalFields ? props.conditionalFields : {},
      formFields: [],
      currentFieldId: this.props.data.id
    }

    this.updateOptions = this.updateOptions.bind(this)
    this.updateRestrictions = this.updateRestrictions.bind(this)
    this.updateConditions = this.updateConditions.bind(this)
    this.getFieldsNames = this.getFieldsNames.bind(this)
    this.normalizeFormOptions = this.normalizeFormOptions.bind(this)
    this.omitCurrentField = this.omitCurrentField.bind(this)
  }

  componentDidMount() {
    this.setState({formFields: this.props.getFormFields()})
    getType(this.props.data.type).then(definition => this.setState({typeDef: definition}))
  }

  /**
   * We locally manage a copy of current options to be able
   * to configure options form based on current values.
   *
   * This is only used to be passed to `typeDefinition.configure()`
   * which generate the form for the current data type field.
   *
   * @param {string} optionName
   * @param {*}  optionValue
   */
  updateOptions(optionName, optionValue) {
    const newOptions = merge({}, this.state.options)

    set(newOptions, optionName, optionValue)

    this.setState({
      options: newOptions
    })
  }

  updateRestrictions(restrictionName, restrictionValue) {
    const newRestrictions = merge({}, this.state.restrictions)

    set(newRestrictions, restrictionName, restrictionValue)

    this.setState({
      restrictions: newRestrictions
    })
  }

  updateConditions(conditionName, conditionValue) {
    // this.setState({
    //   conditions: {...this.state.conditions, [conditionName]: Array.isArray(conditionValue) ? conditionValue[0] : conditionValue}
    // })
    const newConditions = merge({}, this.state.conditions)

    set(newConditions, conditionName, conditionValue)

    this.setState({
      conditions: newConditions
    })
  }

  generateParametersForm(fields) {
    return fields.map(optionField => merge({}, optionField, {
      name: `options.${optionField.name}`, // store all options in an `options` sub object
      onChange: (value) => this.updateOptions(optionField.name, value),
      linked: optionField.linked ? this.generateParametersForm(optionField.linked) : []
    }))
  }

  getFieldsNames(formFields) {
    return formFields.flatMap(({sections}) => sections).flatMap(({fields})=> fields)
  }

  normalizeFormOptions(fieldData = {}) {
    let normalizedOptions

    if(Array.isArray(fieldData)) {
      normalizedOptions = fieldData.reduce((acc, current) => ({...acc, ...{[current.name]: current.label}}), {})
    } else {
      switch (fieldData.type) {
        case 'cascade':
        case 'choice':
          normalizedOptions = fieldData.options.choices.reduce((acc, current) => ({...acc, ...{[current.name]: current.label}}), {})
          break
        case 'boolean':
          normalizedOptions = {true: 'True', false: 'False'}
          break
      }
    }
    return normalizedOptions
  }

  omitCurrentField(fieldData) {
    const {currentFieldId} = this.state
    return fieldData.filter(({id, type}) => id !== currentFieldId && (type === 'boolean' || type === 'choice'))
  }

  render() {
    const {selectedField, formFields} = this.state

    return (
      <FormDataModal
        {...this.props}
        save={fieldData => {
          // generate normalized name for field (c/p from api Entity)
          // TODO : use stripDiacritics instead (it's much more exhaustive)
          let normalizedName = fieldData.label.replace(new RegExp(' ', 'g'), '-') // Replaces all spaces with hyphens.
          normalizedName = normalizedName.replace(/[^A-Za-z0-9-]/g, '') // Removes special chars.
          normalizedName = normalizedName.replace(/-+/g, '-') // Replaces multiple hyphens with single one.
          const required = fieldData.restrictions.locked && !fieldData.restrictions.lockedEditionOnly ?
            false :
            fieldData.required
          const restrictions = merge({}, fieldData.restrictions)

          if (!fieldData.restrictions.locked) {
            restrictions['lockedEditionOnly'] = false
          }
          const conditions = merge({}, fieldData.conditions)

          this.props.save(merge({}, fieldData, {
            name: normalizedName,
            required: required,
            restrictions: restrictions,
            conditions: conditions
          }))

          // this.props.updateConditionalFields(this.state.currentFieldId, this.state.selectedField)
        }}
        title={trans('edit_field')}
        sections={[
          {
            id: 'general',
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'type',
                type: 'string',
                label: trans('type'),
                hideLabel: true,
                render: () => this.state.typeDef ? this.state.typeDef.meta.label : ''
              }, {
                name: 'label',
                type: 'string',
                label: trans('name'),
                required: true
              }, {
                name: 'required',
                type: 'boolean',
                label: trans('field_required'),
                displayed: !this.state.restrictions.locked || this.state.restrictions.lockedEditionOnly
              }
            ]
          }, {
            id: 'conditions',
            icon: 'fa fa-fw fa-cog',
            title: 'Conditional Rendering',
            fields: [
              {
                name: 'conditions.field',
                type: 'choice',
                label: 'Field',
                onChange: (value) => {
                  this.setState({selectedField: this.getFieldsNames(this.state.formFields).find(field => field.name === value)})
                  this.updateConditions('conditions.value', null)
                  this.updateConditions('conditions.field', value)
                },
                options: {
                  choices: this.normalizeFormOptions(this.omitCurrentField(this.getFieldsNames(formFields))),
                  condensed: true,
                  required: true
                }
              }, {
                name: 'conditions.condition',
                type: 'choice',
                label: 'Condition',
                onChange: (value) => this.updateConditions('conditions.condition', value),
                options: {
                  choices: {equals: 'Equals', 'does-not-equal': 'Does not equal'},
                  condensed: true,
                  required: true
                }
              }, {
                name: 'conditions.value',
                type: selectedField.type === 'boolean' ? 'choice' : selectedField.type,
                label: 'Value',
                onChange: (value) => this.updateConditions('conditions.value', value),
                options: {
                  choices: this.normalizeFormOptions(selectedField),
                  condensed: selectedField.type !== 'boolean',
                  required: true
                }
              }
            ]
          }, this.state.typeDef && {
            id: 'parameters',
            icon: 'fa fa-fw fa-cog',
            title: trans('parameters'),
            fields: this.generateParametersForm(this.state.typeDef.configure(this.state.options))
          }, {
            id: 'help',
            icon: 'fa fa-fw fa-info',
            title: trans('help'),
            fields: [
              {
                name: 'help',
                type: 'string',
                label: trans('message'),
                options: {
                  long: true
                }
              }
            ]
          }, {
            id: 'restrictions',
            icon: 'fa fa-fw fa-key',
            title: trans('access_restrictions'),
            fields: [
              {
                name: 'restrictions.isMetadata',
                type: 'boolean',
                label: trans('confidential_data'),
                onChange: (value) => this.updateRestrictions('isMetadata', value)
              }, {
                name: 'restrictions.hidden',
                type: 'boolean',
                label: trans('hide_field'),
                onChange: (value) => this.updateRestrictions('hidden', value)
              }, {
                name: 'restrictions.locked',
                type: 'boolean',
                label: trans('locked'),
                options: {
                  help: trans('required_locked_conflict')
                },
                onChange: (value) => this.updateRestrictions('locked', value),
                linked: [
                  {
                    name: 'restrictions.lockedEditionOnly',
                    type: 'boolean',
                    label: trans('edition_only'),
                    displayed: this.state.restrictions.locked,
                    onChange: (value) => this.updateRestrictions('lockedEditionOnly', value)
                  }
                ]
              }, {
                name: 'restrictions.order',
                type: 'number',
                label: trans('order'),
                onChange: (value) => this.updateRestrictions('order', value)
              }
            ]
          }
        ].filter(section => !!section)}
      />
    )
  }
}

ParametersModal.propTypes = {
  conditionalFields: T.object.isRequired,
  data: T.shape({
    type: T.string.isRequired,
    options: T.object,
    restrictions: T.object,
    conditions: T.object,
    id: T.string
  }),
  fadeModal: T.func.isRequired,
  save: T.func.isRequired,
  getFormFields: T.func.isRequired,
  updateConditionalFields: T.func.isRequired
}

export {
  ParametersModal
}
