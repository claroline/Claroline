import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {getType} from '#/main/app/data/types'
import {selectors} from '#/main/app/data/types/fields/modals/parameters/store'

class ParametersModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      typeDef: null
    }
  }

  componentDidMount() {
    getType(this.props.field.type).then(definition => this.setState({typeDef: definition}))
  }

  generateParametersForm(fields) {
    return fields.map(optionField => merge({}, optionField, {
      name: `options.${optionField.name}`, // store all options in an `options` sub object
      linked: optionField.linked ? this.generateParametersForm(optionField.linked) : []
    }))
  }

  render() {
    let conditionField = null
    if (!isEmpty(this.props.fields) && get(this.props.formData, 'display.condition.field')) {
      conditionField = this.props.fields.find(field => field.id === get(this.props.formData, 'display.condition.field'))
    }

    return (
      <Modal
        {...omit(this.props, 'field', 'fields', 'isNew', 'saveEnabled', 'formData', 'update', 'save', 'reset')}
        icon={this.props.isNew ? 'fa fa-fw fa-plus' : 'fa fa-fw fa-cog'}
        title={trans(this.props.isNew ? 'new_field' : 'parameters')}
        subtitle={this.props.isNew ? trans('new_field_configure') : this.props.formData.label}
        size="lg"
        onEntering={() => this.props.reset(this.props.field, this.props.isNew)}
      >
        <FormData
          name={selectors.STORE_NAME}
          flush={true}
          definition={[
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
                  displayed: (fieldData) => !get(fieldData, 'restrictions.locked') || get(fieldData, 'restrictions.lockedEditionOnly')
                }
              ]
            }, this.state.typeDef && {
              id: 'parameters',
              icon: 'fa fa-fw fa-cog',
              title: trans('parameters'),
              fields: this.generateParametersForm(this.state.typeDef.configure(this.props.formData.options || {}))
            }, {
              id: 'help',
              icon: 'fa fa-fw fa-circle-question',
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
                  label: trans('order')
                }, {
                  name: 'display._conditional',
                  type: 'boolean',
                  label: trans('field_condition_enable'),
                  displayed: !isEmpty(this.props.fields),
                  calculated: (fieldData) => !isEmpty(get(fieldData, 'display.condition.field')) || get(fieldData, 'display._conditional'),
                  onChange: (checked) => {
                    if (!checked) {
                      this.props.update('display.condition', null)
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
                      }
                    }, {
                      name: 'display.condition.comparator',
                      type: 'choice',
                      label: trans('field_comparator'),
                      required: true,
                      displayed: !isEmpty(conditionField),
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
                  name: 'restrictions._enableConfidentiality',
                  type: 'boolean',
                  label: trans('enable_confidentiality'),
                  calculated: (fieldData) => get(fieldData, 'restrictions._enableConfidentiality')
                    || (get(fieldData, 'restrictions.confidentiality') && 'none' !== get(fieldData, 'restrictions.confidentiality')),
                  onChange: (value) => {
                    if (!value) {
                      this.props.update('restrictions.confidentiality', 'none')
                    } else {
                      this.props.update('restrictions.confidentiality', 'owner')
                    }
                  },
                  linked: [
                    {
                      name: 'restrictions.confidentiality',
                      type: 'choice',
                      label: trans('visibility'),
                      displayed: (fieldData) => get(fieldData, 'restrictions._enableConfidentiality') || 'none' !== get(fieldData, 'restrictions.confidentiality'),
                      required: true,
                      options: {
                        choices: {
                          owner: trans('confidentiality_owner'),
                          manager: trans('confidentiality_manager')
                        }
                      }
                    }
                  ]
                }, {
                  name: 'restrictions.locked',
                  type: 'boolean',
                  label: trans('lock'),
                  help: trans('required_locked_conflict'),
                  linked: [
                    {
                      name: 'restrictions.lockedEditionOnly',
                      type: 'boolean',
                      label: trans('edition_only'),
                      displayed: (fieldData) => !!get(fieldData, 'restrictions.locked')
                    }
                  ]
                }
              ]
            }
          ].filter(section => !!section)}
        >
          <Button
            className="modal-btn"
            variant="btn"
            size="lg"
            htmlType="submit"
            type={CALLBACK_BUTTON}
            primary={true}
            label={trans('save', {}, 'actions')}
            disabled={!this.props.saveEnabled}
            callback={() => {
              // generate normalized name for field (c/p from api Entity)
              // TODO : use stripDiacritics instead (it's much more exhaustive)
              let normalizedName = this.props.formData.label.replace(new RegExp(' ', 'g'), '-') // Replaces all spaces with hyphens.
              normalizedName = normalizedName.replace(/[^A-Za-z0-9-]/g, '') // Removes special chars.
              normalizedName = normalizedName.replace(/-+/g, '-') // Replaces multiple hyphens with single one.
              const required = this.props.formData.restrictions.locked && !this.props.formData.restrictions.lockedEditionOnly ?
                false :
                this.props.formData.required
              const restrictions = merge({}, this.props.formData.restrictions)

              if (!this.props.formData.restrictions.locked) {
                restrictions['lockedEditionOnly'] = false
              }

              this.props.save(merge({}, this.props.formData, {
                name: normalizedName,
                required: required,
                restrictions: restrictions
              }))

              this.props.fadeModal()
            }}
          />
        </FormData>
      </Modal>
    )
  }
}

ParametersModal.propTypes = {
  isNew: T.bool.isRequired,
  field: T.shape({
    type: T.string.isRequired,
    options: T.object,
    restrictions: T.object,
    display: T.object
  }),
  fields: T.array,
  formData: T.object.isRequired,
  saveEnabled: T.bool.isRequired,
  fadeModal: T.func.isRequired,
  save: T.func.isRequired,
  reset: T.func.isRequired,
  update: T.func.isRequired
}

ParametersModal.defaultProps = {
  fields: []
}

export {
  ParametersModal
}
