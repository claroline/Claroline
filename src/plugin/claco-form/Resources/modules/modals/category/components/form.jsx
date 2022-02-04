import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import cloneDeep from 'lodash/cloneDeep'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormGroup} from '#/main/app/content/form/components/group'
import {DataInput} from '#/main/app/data/components/input'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

import {makeId} from '#/main/core/scaffolding/id'

import {selectors} from '#/plugin/claco-form/modals/category/store/selectors'
import {Category as CategoryTypes} from '#/plugin/claco-form/resources/claco-form/prop-types'

const supportedTypes = ['number', 'string', 'choice', 'country', 'cascade']

class FieldsValues extends Component {
  constructor(props) {
    super(props)

    this.state = {
      selectedField: null
    }
  }

  addField(field) {
    if (field) {
      const newFieldsValues = cloneDeep(this.props.formData.fieldsValues)
      newFieldsValues.push({
        id: makeId(),
        category: this.props.category,
        field: field,
        value: 'cascade' === field.type ? [] : null
      })
      this.props.updateProp('fieldsValues', newFieldsValues)

      this.setState({selectedField: null})
    }
  }

  removeField(fieldIndex) {
    const newFieldsValues = cloneDeep(this.props.formData.fieldsValues)
    newFieldsValues.splice(fieldIndex, 1)
    this.props.updateProp('fieldsValues', newFieldsValues)
  }

  updateFieldValue(fieldIndex, value) {
    const newFieldsValues = cloneDeep(this.props.formData.fieldsValues)
    newFieldsValues[fieldIndex].value = value
    this.props.updateProp('fieldsValues', newFieldsValues)
  }

  formatOptions(options, type) {
    const formattedOptions = cloneDeep(options)

    if (options.choices && 'choice' === type) {
      formattedOptions['choices'] = options.choices.reduce((acc, choice) => {
        acc[choice.value] = choice.value

        return acc
      }, {})
    }

    return formattedOptions
  }

  render() {
    return (
      <FormGroup id="fields-values">
        <DataInput
          label={trans('add_field')}
          type="choice"
          options={{
            multiple: false,
            condensed: true,
            choices: this.props.fields.filter(f => f.id && -1 < supportedTypes.indexOf(f.type)).reduce((acc, field) => {
              acc[field.id] = field.label

              return acc
            }, {})
          }}
          value={this.state.selectedField}
          onChange={(value) => this.setState({selectedField: value})}
        />
        <CallbackButton
          className="btn"
          callback={() => this.addField(this.props.fields.find(f => f.id === this.state.selectedField))}
          primary={true}
          disabled={!this.state.selectedField}
        >
          {trans('add', {}, 'actions')}
        </CallbackButton>

        {this.props.formData.fieldsValues.map((fv, idx) =>
          <div key={`field-value-${idx}`}>
            <hr/>
            <CallbackButton
              className="btn btn-sm btn-link pull-right"
              callback={() => this.removeField(idx)}
              dangerous={true}
            >
              <span className="fa fa-trash-o" />
            </CallbackButton>
            <DataInput
              label={fv.field.label}
              type={fv.field.type}
              options={fv.field.options ? this.formatOptions(fv.field.options, fv.field.type) : {}}
              value={fv.value}
              onChange={(value) => this.updateFieldValue(idx, value)}
            />
          </div>
        )}
      </FormGroup>
    )
  }
}

const CategoryFormModal = props => {
  const FieldsValuesComponent = (
    <FieldsValues
      {...props}
    />
  )

  return (
    <Modal
      {...omit(props, 'saveEnabled', 'formData', 'fields', 'category', 'loadCategory', 'saveCategory', 'updateProp')}
      icon="fa fa-fw fa-object-group"
      title={trans('category')}
      subtitle={(props.category && props.category.name) || trans('new_category')}
      onEntering={() => props.loadCategory(props.category)}
    >
      <FormData
        name={selectors.STORE_NAME}
        sections={[
          {
            id: 'general',
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'name',
                type: 'string',
                label: trans('name'),
                required: true
              }, {
                name: 'managers',
                label: trans('managers'),
                type: 'users'
              }
            ]
          }, {
            id: 'fields',
            icon: 'fa fa-fw fa-link',
            title: trans('fields_associations', {}, 'clacoform'),
            fields: [
              {
                name: 'fieldsValues',
                label: trans('fields_associations', {}, 'clacoform'),
                hideLabel: true,
                component: FieldsValuesComponent
              }
            ]
          }, {
            id: 'display',
            icon: 'fa fa-fw fa-desktop',
            title: trans('display_parameters'),
            fields: [
              {
                name: 'details.color',
                type: 'color',
                label: trans('color')
              }
            ]
          }, {
            id: 'notifications',
            icon: 'fa fa-fw fa-bell',
            title: trans('notifications'),
            fields: [
              {
                name: 'notifications',
                label: trans('notified_actions'),
                type: 'choice',
                options: {
                  multiple: true,
                  inline: false,
                  choices: {
                    notify_addition: trans('addition', {}, 'clacoform'),
                    notify_edition: trans('edition', {}, 'clacoform'),
                    notify_removal: trans('removal', {}, 'clacoform'),
                    notify_pending_comment: trans('comment_to_moderate', {}, 'clacoform')
                  }
                },
                calculated: (category) => [
                  'notify_addition',
                  'notify_edition',
                  'notify_removal',
                  'notify_pending_comment'
                ].filter(prop => category && category.details && category.details[prop]),
                onChange: (value) => {
                  props.updateProp('details.notify_addition', -1 !== value.indexOf('notify_addition'))
                  props.updateProp('details.notify_edition', -1 !== value.indexOf('notify_edition'))
                  props.updateProp('details.notify_removal', -1 !== value.indexOf('notify_removal'))
                  props.updateProp('details.notify_pending_comment', -1 !== value.indexOf('notify_pending_comment'))
                }
              }
            ]
          }
        ]}
      />

      <Button
        className="modal-btn btn btn-primary"
        type={CALLBACK_BUTTON}
        primary={true}
        label={trans('save', {}, 'actions')}
        disabled={!props.saveEnabled}
        callback={() => {
          props.saveCategory(props.formData)
          props.fadeModal()
        }}
      />
    </Modal>
  )
}

CategoryFormModal.propTypes = {
  saveEnabled: T.bool.isRequired,
  formData: T.shape(
    CategoryTypes.propTypes
  ),
  category: T.shape(
    CategoryTypes.propTypes
  ),
  fields: T.arrayOf(T.shape({
    // TODO : field propTypes
  })),
  updateProp: T.func.isRequired,
  loadCategory: T.func.isRequired,
  saveCategory: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  CategoryFormModal
}
