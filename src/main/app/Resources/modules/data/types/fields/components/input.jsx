import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classes from 'classnames'
import uuid from 'uuid'

import {trans} from '#/main/app/intl/translation'

import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {MODAL_SELECTION} from '#/main/app/modals/selection'
import {MODAL_FIELD_PARAMETERS} from '#/main/app/data/types/fields/modals/parameters'

import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {getCreatableTypes} from '#/main/app/data/types'
import {DataInput} from '#/main/app/data/components/input'

// todo try to avoid connexion to the store
// todo create working preview
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
    this.open        = this.open.bind(this)
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

  open(field, allFields, callback) {
    this.props.showModal(MODAL_FIELD_PARAMETERS, {
      data: field,
      fields: allFields.filter(otherField => otherField.id !== field.id),
      save: callback
    })
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
        {0 !== this.props.value.length &&
          <button
            type="button"
            className="btn btn-remove-all btn-sm btn-link-danger"
            onClick={this.removeAll}
          >
            {trans('delete_all')}
          </button>
        }

        {0 < this.props.value.length &&
          <ul>
            {this.props.value.map((field, fieldIndex) =>
              <li key={fieldIndex} className="field-item">
                <FieldPreview {...this.formatField(field)} />

                <div className="field-item-actions">
                  <Button
                    id={`${this.props.id}-${fieldIndex}-edit`}
                    type={MODAL_BUTTON}
                    className="btn-link"
                    icon="fa fa-fw fa-pencil"
                    label={trans('edit', {}, 'actions')}
                    tooltip="top"
                    modal={[MODAL_FIELD_PARAMETERS, {
                      data: field,
                      fields: allFields.filter(otherField => otherField.id !== field.id),
                      save: (data) => this.update(fieldIndex, data)
                    }]}
                  />

                  <Button
                    id={`${this.props.id}-${fieldIndex}-delete`}
                    type={CALLBACK_BUTTON}
                    className="btn-link"
                    icon="fa fa-fw fa-trash-o"
                    label={trans('delete', {}, 'actions')}
                    tooltip="top"
                    confirm={{
                      title: trans('delete_field'),
                      message: trans('delete_field_confirm')
                    }}
                    callback={() => this.remove(fieldIndex)}
                    dangerous={true}
                  />
                </div>
              </li>
            )}
          </ul>
        }

        {0 === this.props.value.length &&
          <div className="no-field-info">{this.props.placeholder}</div>
        }

        <Button
          type={CALLBACK_BUTTON}
          className="btn btn-block"
          icon="fa fa-fw fa-plus"
          label={trans('add_field')}
          callback={() => getCreatableTypes().then(types => {
            this.props.showModal(MODAL_SELECTION, {
              title: trans('create_field'),
              items: types.map(type => Object.assign({}, type.meta, {name: type.name})),
              handleSelect: (type) => this.open({
                id: uuid(),
                type: type.name,
                restrictions: {
                  locked: false,
                  lockedEditionOnly: false
                }
              }, allFields, this.add)
            })
          })}
        />
      </div>
    )
  }
}

FieldList.propTypes = {
  id: T.string.isRequired,
  placeholder: T.string,
  className: T.string,
  value: T.arrayOf(T.shape({

  })),
  // a list of all fields for conditional rendering
  // it uses the current list of fields in `value` in missing
  // this is useful for profile where fields are propagated between multiple tabs/panels
  fields: T.array,
  onChange: T.func.isRequired,
  showModal: T.func.isRequired
}

FieldList.defaultProps = {
  placeholder: trans('empty_fields_list'),
  value: []
}

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
