import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import set from 'lodash/set'

import {trans} from '#/main/app/intl/translation'
import {getType} from '#/main/app/data/types'
import {FormDataModal} from '#/main/app/modals/form/components/data'

class ParametersModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      options: get(props.data, 'options', {}),
      restrictions: get(props.data, 'restrictions', {}),
      display: get(props.data, 'display', {}),
      typeDef: null
    }

    this.updateOptions = this.updateOptions.bind(this)
    this.updateRestrictions = this.updateRestrictions.bind(this)
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

  updateDisplay(displayName, displayValue) {
    const newDisplay = merge({}, this.state.display)

    set(newDisplay, displayName, displayValue)

    this.setState({
      display: newDisplay
    })
  }

  generateParametersForm(fields) {
    return fields.map(optionField => merge({}, optionField, {
      name: `options.${optionField.name}`, // store all options in an `options` sub object
      onChange: (value) => this.updateOptions(optionField.name, value),
      linked: optionField.linked ? this.generateParametersForm(optionField.linked) : []
    }))
  }

  render() {
    let conditionField = null
    if (!isEmpty(this.props.fields) && get(this.state, 'display.condition.field')) {
      conditionField = this.props.fields.find(field => field.id === get(this.state, 'display.condition.field'))
    }

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

          this.props.save(merge({}, fieldData, {
            name: normalizedName,
            required: required,
            restrictions: restrictions
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
                label: trans('type'),
                type: 'type',
                hideLabel: true,
                calculated: () => this.state.typeDef ? ({
                  icon: <span className={this.state.typeDef.meta.icon} />,
                  name: this.state.typeDef.meta.label,
                  description: this.state.typeDef.meta.description
                }) : null
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
            id: 'display',
            icon: 'fa fa-fw fa-desktop',
            title: trans('display_parameters'),
            fields: [
              {
                name: 'display.order',
                type: 'number',
                label: trans('order'),
                onChange: (value) => this.updateDisplay('order', value)
              }, {
                name: 'display._conditional',
                type: 'boolean',
                label: trans('Afficher en fonction de la valeur d\'un autre champ'),
                displayed: !isEmpty(this.props.fields),
                calculated: (field) => !isEmpty(get(field, 'display.condition.field')) || get(field, 'display._conditional'),
                onChange: (checked) => {
                  if (!checked) {
                    this.updateDisplay('condition', null)
                  }
                },
                linked: [
                  {
                    name: 'display.condition.field',
                    type: 'choice',
                    label: trans('field'),
                    required: true,
                    displayed: (field) => !isEmpty(this.props.fields) && (!isEmpty(get(field, 'display.condition.field')) || get(field, 'display._conditional')),
                    options: {
                      condensed: true,
                      choices: this.props.fields.reduce((acc, current) => Object.assign({}, acc, {
                        [current.id]: current.label
                      }), {})
                    },
                    onChange: (value) => this.updateDisplay('condition.field', value)
                  }, {
                    name: 'display.condition.comparator',
                    type: 'choice',
                    label: trans('field_comparator'),
                    required: true,
                    displayed: !isEmpty(conditionField),
                    onChange: (value) => this.updateDisplay('condition.comparator', value),
                    options: {
                      choices: {
                        'equal': trans('equal'),
                        'different': trans('different'),
                        'empty': trans('empty'),
                        'not_empty': trans('not_empty')
                      }
                    }
                  }, {
                    name: 'display.condition.value',
                    type: get(conditionField, 'type', 'string'),
                    label: trans('value'),
                    required: true,
                    displayed: (field) => !isEmpty(conditionField) && -1 === ['empty', 'not_empty'].indexOf(get(field, 'display.condition.comparator')),
                    onChange: (value) => this.updateDisplay('condition.value', value),
                    options: get(conditionField, 'options', {})
                  }
                ]
              }
            ]
          }, {
            id: 'restrictions',
            icon: 'fa fa-fw fa-key',
            title: trans('access_restrictions'),
            fields: [
              {
                name: 'restrictions.metadata',
                type: 'boolean',
                label: trans('confidential_data'),
                onChange: (value) => this.updateRestrictions('metadata', value)
              }, {
                name: 'restrictions.hidden',
                type: 'boolean',
                label: trans('hide'),
                onChange: (value) => this.updateRestrictions('hidden', value)
              }, {
                name: 'restrictions.locked',
                type: 'boolean',
                label: trans('lock'),
                help: trans('required_locked_conflict'),
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
    display: T.object
  }),
  fields: T.array,
  fadeModal: T.func.isRequired,
  save: T.func.isRequired
}

ParametersModal.defaultProps = {
  fields: []
}

export {
  ParametersModal
}
