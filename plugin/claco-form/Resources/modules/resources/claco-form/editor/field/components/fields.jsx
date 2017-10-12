import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {trans, t} from '#/main/core/translation'
import {select as resourceSelect} from '#/main/core/layout/resource/selectors'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {actions} from '../actions'
import {getFieldType} from '../../../utils'

class Fields extends Component {
  showFieldCreationForm() {
    this.props.showModal(
      'MODAL_FIELD_FORM',
      {
        title: trans('create_a_field', {}, 'clacoform'),
        confirmAction: (field) => this.props.createField(field),
        field: {
          id: 0,
          name: '',
          type: 1,
          required: false,
          isMetadata: false,
          hidden: false,
          locked: false,
          lockedEditionOnly: false,
          details: {
            file_types: [],
            nb_files_max: 1
          }
        },
        resourceId: this.props.resourceId
      }
    )
  }

  showFieldEditionForm(field) {
    this.props.showModal(
      'MODAL_FIELD_FORM',
      {
        title: trans('edit_field', {}, 'clacoform'),
        confirmAction: (f) => this.props.editField(f),
        field: {
          id: field.id,
          name: field.name,
          type: field.type,
          required: field.required,
          isMetadata: field.isMetadata,
          hidden: field.hidden,
          locked: field.locked,
          lockedEditionOnly: field.lockedEditionOnly,
          fieldFacet: field.fieldFacet,
          details: field.details
        },
        resourceId: this.props.resourceId
      }
    )
  }

  showFieldDeletion(field) {
    this.props.showModal(MODAL_DELETE_CONFIRM, {
      title: trans('delete_field', {}, 'clacoform'),
      question: trans('delete_field_confirm_message', {name: field.name}, 'clacoform'),
      handleConfirm: () => this.props.deleteField(field.id)
    })
  }

  render() {
    return (
      <div>
        <h2>{trans('fields_management', {}, 'clacoform')}</h2>
        <br/>
        {this.props.canEdit ?
          <div>
            <table className="table">
              <thead>
                <tr>
                  <th>
                    {t('name')}
                  </th>
                  <th>{t('type')}</th>
                  <th className="text-center">{trans('mandatory', {}, 'clacoform')}</th>
                  <th className="text-center">{trans('metadata', {}, 'clacoform')}</th>
                  <th className="text-center">{t('locked')}</th>
                  <th>{t('actions')}</th>
                </tr>
              </thead>
              <tbody>
                {this.props.fields.map((field) =>
                  <tr key={`field-${field.id}`}>
                    <td>
                      {field.hidden &&
                        <span>
                          <span
                            className="fa fa-w fa-eye-slash"
                            data-toggle="tooltip"
                            data-placement="top"
                            title={trans('hidden_field', {}, 'clacoform')}
                          >
                          </span>
                          &nbsp;
                        </span>
                      }
                      {field.name}
                    </td>
                    <td>
                      {getFieldType(field.type).label}
                    </td>
                    <td className="text-center">
                      {field.required ?
                        <span className="fa fa-w fa-check text-success"></span> :
                        <span className="fa fa-w fa-times text-danger"></span>
                      }
                    </td>
                    <td className="text-center">
                      {field.isMetadata ?
                        <span className="fa fa-w fa-check text-success"></span> :
                        <span className="fa fa-w fa-times text-danger"></span>
                      }
                    </td>
                    <td className="text-center">
                      {field.locked ?
                        field.lockedEditionOnly ?
                          <span className="fa fa-w fa-unlock-alt"></span> :
                          <span className="fa fa-w fa-lock"></span> :
                        <span className="fa fa-w fa-unlock"></span>
                      }
                    </td>
                    <td>
                      <button
                        className="btn btn-default btn-sm"
                        onClick={() => this.showFieldEditionForm(field)}
                      >
                        <span className="fa fa-w fa-pencil"></span>
                      </button>
                      &nbsp;
                      <button
                        className="btn btn-danger btn-sm"
                        onClick={() => this.showFieldDeletion(field)}
                      >
                        <span className="fa fa-w fa-trash"></span>
                      </button>
                    </td>
                  </tr>
                )}
              </tbody>
            </table>

            <button className="btn btn-primary" onClick={() => this.showFieldCreationForm()}>
              <span className="fa fa-w fa-plus"></span>
              &nbsp;
              {trans('create_a_field', {}, 'clacoform')}
            </button>
          </div> :
          <div className="alert alert-danger">
            {t('unauthorized')}
          </div>
        }
      </div>
    )
  }
}

Fields.propTypes = {
  canEdit: T.bool.isRequired,
  resourceId: T.number.isRequired,
  fields: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired,
    type: T.number.isRequired,
    required: T.bool.isRequired,
    isMetadata: T.bool.isRequired,
    locked: T.bool.isRequired,
    lockedEditionOnly: T.bool.isRequired,
    hidden: T.bool
  })).isRequired,
  createField: T.func.isRequired,
  editField: T.func.isRequired,
  deleteField: T.func.isRequired,
  showModal: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    canEdit: resourceSelect.editable(state),
    resourceId: state.resource.id,
    fields: state.fields
  }
}

function mapDispatchToProps(dispatch) {
  return {
    createField: (data) => dispatch(actions.createField(data)),
    editField: (data) => dispatch(actions.editField(data)),
    deleteField: (fieldId) => dispatch(actions.deleteField(fieldId)),
    showModal: (type, props) => dispatch(modalActions.showModal(type, props))
  }
}

const ConnectedFields = connect(mapStateToProps, mapDispatchToProps)(Fields)

export {ConnectedFields as Fields}