import React, {Component} from 'react'
import {connect} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'
import isNumber from 'lodash/isNumber'

import {implementPropTypes, PropTypes as T} from '#/main/app/prop-types'
import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/app/intl/translation'

import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {MODAL_SELECTION} from '#/main/app/modals/selection'
import {MODAL_FIELD_PARAMETERS} from '#/main/app/data/types/fields/modals/parameters'

import {Button} from '#/main/app/action/components/button'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {getCreatableTypes} from '#/main/app/data/types'
import {DataInput} from '#/main/app/data/components/input'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

// todo find a way to use collections

const FieldPreview = props =>
  <DataInput
    {...props}
    onChange={() => true}
  />

FieldPreview.propTypes = {
  name: T.string.isRequired
}

FieldPreview.defaultProps = {
  options: {}
}

class FieldList extends Component {
  constructor(props) {
    super(props)

    this.add         = this.add.bind(this)
    this.update      = this.update.bind(this)
    this.remove      = this.remove.bind(this)
    this.removeAll   = this.removeAll.bind(this)
    this.formatField = this.formatField.bind(this)
  }

  add(newField) {
    const fields = this.props.value.slice()

    // add
    fields.push(newField)

    this.props.onChange(fields)
  }

  update(index, field) {
    const fields = this.props.value.slice()

    // update
    fields[index] = field

    this.props.onChange(fields)
  }

  remove(index) {
    const fields = this.props.value.slice()

    // remove
    fields.splice(index, 1)

    this.props.onChange(fields)
  }

  removeAll() {
    this.props.onChange([])
  }

  formatField(field) {
    const options = field.options ? Object.assign({}, field.options) : {}

    // TODO : find a way to remove this hack on choices
    if (field.type === 'choice') {
      options['choices'] = field.options && field.options.choices ?
        field.options.choices.reduce((acc, choice) => {
          acc[choice.value] = choice.value

          return acc
        }, {}) :
        {}
    }

    return field.type === 'choice' ? Object.assign({}, field, {options: options}) : field
  }

  render() {
    const allFields = (this.props.fields || this.props.value)
      .map(this.formatField)

    return (
      <div className={classes('field-list-control', this.props.className)}>
        {0 === this.props.value.length &&
          <ContentPlaceholder className="mb-2" title={this.props.placeholder} size={this.props.size} />
        }

        {0 !== this.props.value.length &&
          <Button
            className="btn btn-text-danger btn-delete-all"
            type={CALLBACK_BUTTON}
            label={trans('delete_all')}
            disabled={this.props.disabled}
            size="sm"
            dangerous={true}
            callback={this.removeAll}
          />
        }

        {0 < this.props.value.length &&
          <ul>
            {this.props.value
              .sort((a, b) => {
                if (isNumber(get(a, 'display.order')) && !isNumber(get(b, 'display.order'))) {
                  return -1
                } else if (!isNumber(get(a, 'display.order')) && isNumber(get(b, 'display.order'))) {
                  return 1
                } else if (isNumber(get(a, 'display.order')) && isNumber(get(b, 'display.order'))) {
                  return get(a, 'display.order') - get(b, 'display.order')
                } else if (a.label > b.label) {
                  return 1
                }

                return 0
              })
              .map((field, fieldIndex) =>
                <li key={fieldIndex} className="field-item mb-2">
                  <div className="field-item-preview">
                    <FieldPreview {...this.formatField(field)} />
                    {get(field, 'restrictions.confidentiality') && 'none' !== get(field, 'restrictions.confidentiality') &&
                      <div className="badge text-bg-primary mt-1">
                        <span className="fa fa-fw fa-eye icon-with-text-right" />
                        {trans('confidentiality_'+field.restrictions.confidentiality)}
                      </div>
                    }
                  </div>

                  <Toolbar
                    id={`${this.props.id}-${fieldIndex}-actions`}
                    className="field-item-actions"
                    tooltip="top"
                    actions={[
                      {
                        name: 'edit',
                        type: MODAL_BUTTON,
                        className: 'btn btn-text-secondary',
                        icon: 'fa fa-fw fa-pencil',
                        label: trans('edit', {}, 'actions'),
                        modal: [MODAL_FIELD_PARAMETERS, {
                          field: field,
                          isNew: false,
                          fields: allFields.filter(otherField => otherField.id !== field.id),
                          save: (data) => this.update(fieldIndex, data)
                        }]
                      }, {
                        name: 'delete',
                        type: CALLBACK_BUTTON,
                        className: 'btn btn-text-danger',
                        icon: 'fa fa-fw fa-trash',
                        label: trans('delete', {}, 'actions'),
                        confirm: {
                          title: trans('delete_field'),
                          message: trans('delete_field_confirm')
                        },
                        callback: () => this.remove(fieldIndex),
                        dangerous: true
                      }
                    ]}
                  />
                </li>
              )
            }
          </ul>
        }

        <Button
          type={CALLBACK_BUTTON}
          variant="btn"
          className="w-100"
          icon="fa fa-fw fa-plus"
          label={trans('add_field')}
          callback={() => getCreatableTypes().then(types => {
            this.props.showModal(MODAL_SELECTION, {
              icon: 'fa fa-fw fa-plus',
              title: trans('new_field'),
              subtitle: trans('new_field_select'),
              items: types.map(type => Object.assign({}, type.meta, {name: type.name})),
              selectAction: (type) => ({
                type: MODAL_BUTTON,
                modal: [MODAL_FIELD_PARAMETERS, {
                  field: {
                    id: makeId(),
                    type: type.name
                  },
                  isNew: true,
                  fields: allFields,
                  save: this.add
                }]
              })
            })
          })}
        />
      </div>
    )
  }
}

implementPropTypes(FieldList, DataInputTypes, {
  // more precise value type
  value: T.arrayOf(T.object),

  // a list of all fields for conditional rendering
  // it uses the current list of fields in `value` in missing
  // this is useful for profile where fields are propagated between multiple tabs/panels
  fields: T.array,
  showModal: T.func.isRequired
}, {
  placeholder: trans('empty_fields_list'),
  value: []
})

const FieldsInput = connect(
  null,
  (dispatch) => ({
    showModal(modalType, modalProps) {
      dispatch(modalActions.showModal(modalType, modalProps))
    }
  })
)(FieldList)

export {
  FieldsInput
}
