import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'
import set from 'lodash/set'

import {t} from '#/main/core/translation'

import {getTypeOrDefault} from '#/main/core/data'
import {DataFormModal} from '#/main/core/data/form/modals/components/data-form.jsx'

const MODAL_CONFIGURE_FIELD = 'MODAL_CONFIGURE_FIELD'

class ConfigureFieldModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      options: props.data.options ? props.data.options : {}
    }

    this.updateOptions = this.updateOptions.bind(this)
  }

  /**
   * We locally manage a copy of current options to be able
   * to configure options form based on current values.
   *
   * This is only used to be passed to `typeDefinition.configure()`
   * which generate the form for the current data type field.
   *
   * @param {string} optionName
   * @param {mixed}  optionValue
   */
  updateOptions(optionName, optionValue) {
    const newOptions = merge({}, this.state.options)

    set(newOptions, optionName, optionValue)

    this.setState({
      options: newOptions
    })
  }

  render() {
    const typeDef = getTypeOrDefault(this.props.data.type)

    return (
      <DataFormModal
        {...this.props}
        save={fieldData => {
          // generate normalized name for field (c/p from api Entity)
          let normalizedName = fieldData.label.replace(' ', '-') // Replaces all spaces with hyphens.
          normalizedName.replace(/[^A-Za-z0-9\-]/, '') // Removes special chars.
          normalizedName.replace(/-+/, '-') // Replaces multiple hyphens with single one.

          this.props.save(merge({}, fieldData, {
            name: normalizedName
          }))
        }}
        title={t('edit_field')}
        sections={[
          {
            id: 'general',
            title: t('general'),
            primary: true,
            fields: [
              {
                name: 'label',
                type: 'string',
                label: t('name'),
                required: true
              }, {
                name: 'required',
                type: 'boolean',
                label: t('field_optional'),
                options: {
                  labelChecked: t('field_required')
                }
              }
            ]
          }, {
            id: 'parameters',
            icon: 'fa fa-fw fa-cog',
            title: t('parameters'),
            fields: typeDef.configure(this.state.options).map(optionField => merge({}, optionField, {
              name: `options.${optionField.name}`, // store all options in an `options` sub object
              onChange: (value) => this.updateOptions(optionField.name, value)
            }))
          }, {
            id: 'help',
            icon: 'fa fa-fw fa-info',
            title: t('help'),
            fields: [
              {
                name: 'help',
                type: 'string',
                label: t('message'),
                options: {
                  long: true
                }
              }
            ]
          }, {
            id: 'restrictions',
            icon: 'fa fa-fw fa-key',
            title: t('access_restrictions'),
            fields: [

            ]
          }
        ]}
      />
    )
  }
}

ConfigureFieldModal.propTypes = {
  data: T.shape({
    type: T.string.isRequired,
    options: T.object
  }),
  fadeModal: T.func.isRequired,
  save: T.func.isRequired
}

export {
  MODAL_CONFIGURE_FIELD,
  ConfigureFieldModal
}
