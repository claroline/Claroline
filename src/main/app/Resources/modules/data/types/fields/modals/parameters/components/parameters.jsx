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
      typeDef: null,
      conditions: props.data.conditions ? props.data.conditions : {},
      currentFieldId: this.props.data.id
    }

    this.updateOptions = this.updateOptions.bind(this)
    this.updateRestrictions = this.updateRestrictions.bind(this)
    this.updateConditions = this.updateConditions.bind(this)
    this.getAllFields = this.getAllFields.bind(this)
    this.getDependencyFieldOptions = this.getDependencyFieldOptions.bind(this)
    this.getDependencyFields = this.getDependencyFields.bind(this)
  }

  componentDidMount() {
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

  getAllFields() {
    return this.props.formFields.flatMap(({sections}) => sections).flatMap(({fields})=> fields)
  }

  findDependencyFieldData() {
    const {conditions: {dependencyField}} = this.state
    
    return this.getAllFields().find(({id}) => id === dependencyField)
  }

  getDependencyFieldOptions() { 
    const field = this.findDependencyFieldData()

    if (field !== undefined) {
      if (field.type === 'choice') {
        return field.options.choices.reduce((acc, current) => ({...acc, ...{[current.id]: current.label}}), {})
      } else {
        return {true: 'True', false: 'False'}
      }
    } else {
      return []
    }
  }

  mapFieldsToOptions(fields) {
    return fields.reduce((acc, current) => ({...acc, ...{[current.id]: current.label}}), {})
  }

  getDependencyFields(fieldData) {
    const {currentFieldId} = this.state
    return fieldData.filter(({id, type}) => id !== currentFieldId && (type === 'boolean' || type === 'choice'))
  }

  render() {
    const dependencyFieldData = this.findDependencyFieldData()

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
                name: 'conditions.dependencyField',
                type: 'choice',
                label: 'Field',
                onChange: (value) => {
                  this.updateConditions('comparisonValue', null)
                  this.updateConditions('dependencyField', value)
                },
                options: {
                  choices: this.mapFieldsToOptions(this.getDependencyFields(this.getAllFields())),
                  condensed: true,
                  required: true
                }
              }, {
                name: 'conditions.validationType',
                type: 'choice',
                label: 'Condition',
                onChange: (value) => this.updateConditions('validationType', value),
                options: {
                  choices: {equals: 'Equals', 'does-not-equal': 'Does not equal'},
                  condensed: true,
                  required: true
                }
              }, {
                name: 'conditions.comparisonValue',
                type: 'choice',
                label: 'Value',
                onChange: (value) => this.updateConditions('comparisonValue', value),
                options: {
                  choices: this.getDependencyFieldOptions(),
                  condensed: dependencyFieldData && dependencyFieldData.type !== 'boolean',
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
  data: T.shape({
    type: T.string.isRequired,
    options: T.object,
    restrictions: T.object,
    conditions: T.object,
    id: T.string
  }),
  fadeModal: T.func.isRequired,
  save: T.func.isRequired,
  formFields: T.array.isRequired
}

export {
  ParametersModal
}
