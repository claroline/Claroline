import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classes from 'classnames'

import {t} from '#/main/core/translation'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_GENERIC_TYPE_PICKER} from '#/main/core/layout/modal'
import {MODAL_CONFIGURE_FIELD} from '#/main/core/data/form/modals'

import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'

import {FormField} from '#/main/core/data/form/components/field.jsx'
import {getCreatableTypes} from '#/main/core/data'


// todo create working preview
const FieldPreview = props =>
  <FormField
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

    this.add       = this.add.bind(this)
    this.update    = this.update.bind(this)
    this.remove    = this.remove.bind(this)
    this.removeAll = this.removeAll.bind(this)
    this.open      = this.open.bind(this)
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

  open(field, callback) {
    this.props.showModal(MODAL_CONFIGURE_FIELD, {
      data: field,
      save: callback
    })
  }

  render() {
    return (
      <div className="field-list-control">
        {0 !== this.props.value.length &&
        <button
          type="button"
          className="btn btn-remove-all btn-sm btn-link-danger"
          onClick={this.removeAll}
        >
          {t('delete_all')}
        </button>
        }

        {0 < this.props.value.length &&
        <ul>
          {this.props.value.map((field, fieldIndex) =>
            <li key={fieldIndex} className="field-item">
                <span className={classes('field-item-icon',
                  getCreatableTypes()[Object.keys(getCreatableTypes()).find(type => field.type === type)].meta.icon
                )} />

              <FieldPreview
                {...field}
              />

              <div className="field-item-actions">
                <TooltipButton
                  id={`${this.props.id}-${fieldIndex}-edit`}
                  title={t('edit')}
                  className="btn-link-default"
                  onClick={() => this.open(field, (data) => {
                    this.update(fieldIndex, data)
                  })}
                >
                  <span className="fa fa-fw fa-pencil" />
                </TooltipButton>

                <TooltipButton
                  id={`${this.props.id}-${fieldIndex}-delete`}
                  title={t('delete')}
                  className="btn-link-danger"
                  onClick={() => this.remove(fieldIndex)}
                >
                  <span className="fa fa-fw fa-trash-o" />
                </TooltipButton>
              </div>
            </li>
          )}
        </ul>
        }

        {0 === this.props.value.length &&
        <div className="no-field-info">{this.props.placeholder}</div>
        }

        <button
          type="button"
          className="btn btn-default btn-block"
          onClick={() => this.props.showModal(MODAL_GENERIC_TYPE_PICKER, {
            title: t('create_field'),
            types: Object.keys(getCreatableTypes()).map(type => getCreatableTypes()[type].meta),
            handleSelect: (type) => this.open({type: type.type}, (data) => {
              this.add(data)
            })
          })}
        >
          <span className="fa fa-plus icon-with-text-right"/>
          {t('add_field')}
        </button>
      </div>
    )
  }
}

FieldList.propTypes = {
  id: T.string.isRequired,
  placeholder: T.string,
  value: T.arrayOf(T.shape({

  })),
  onChange: T.func.isRequired,
  showModal: T.func.isRequired
}

FieldList.defaultProps = {
  placeholder: t('empty_fields_list'),
  value: []
}

const Fields = connect(
  null,
  (dispatch) => ({
    showModal(modalType, modalProps) {
      dispatch(modalActions.showModal(modalType, modalProps))
    }
  })
)(FieldList)

export {
  Fields
}
