/*global UserPicker*/
import {connect} from 'react-redux'
import React, {Component, PropTypes as T} from 'react'
import {trans, t} from '#/main/core/translation'
import {makeModal} from '#/main/core/layout/modal'
import {actions} from '../actions'
import {selectors} from '../selectors'
import {registrationTypes, registrationStatus} from '../enums'

class EventView extends Component {
  constructor(props) {
    super(props)
    this.state = {
      modal: {}
    }
    this.addParticipants = this.addParticipants.bind(this)
  }

  componentWillUnmount() {
    this.props.resetCurrentSessionEvent()
  }

  showEventEditionForm(sessionEvent) {
    this.setState({
      modal: {
        type: 'MODAL_EVENT_FORM',
        urlModal: null,
        props: {
          mode: 'edition',
          title: `${trans('session_event_edition', {}, 'cursus')}`,
          updateEventForm: this.props.updateEventForm,
          event: sessionEvent,
          confirmAction: this.props.editSessionEvent,
          resetFormData: this.props.resetEventForm,
          loadFormData: this.props.loadEventForm
        },
        fading: false
      }
    })
  }

  showParticipantsSelection() {
    let userPicker = new UserPicker()
    const options = {
      picker_name: 'validators-picker',
      picker_title: trans('validators_selection', {}, 'cursus'),
      multiple: true,
      //selected_users: this.getSelectedUsersIds(),
      forced_workspaces: [this.props.workspaceId],
      return_datas: true,
      blacklist: this.getParticipantsIds()
    }
    userPicker.configure(options, this.addParticipants)
    userPicker.open()
  }

  getParticipantsIds() {
    const ids = []
    this.props.participants.forEach(p => ids.push(p['user'].id))

    return ids
  }

  addParticipants(users) {
    if (users) {
      const toAdd = []
      users.forEach(u => toAdd.push(u.id))
      this.props.registerParticipants(this.props.event.id, toAdd)
    }
  }

  removeParticipant(id) {
    this.props.deleteParticipants([id])
  }

  hideModal() {
    this.setState({modal: {fading: true, urlModal: null}})
  }

  render() {
    return (
      <div>
        <h3>{this.props.event.name}</h3>
        {this.props.canEdit ?
          <span className="pull-right">
            <button className="btn btn-primary margin-right-sm"
                    data-toggle="tooltip"
                    data-placement="top"
                    title={trans('edit_session_event', {}, 'cursus')}
                    onClick={() => this.showEventEditionForm(this.props.event)}
            >
              <i className="fa fa-edit"></i>
            </button>
            <button className="btn btn-primary margin-right-sm"
                    data-toggle="tooltip"
                    data-placement="top"
                    title={trans('register_participants', {}, 'cursus')}
                    onClick={() => this.showParticipantsSelection()}
            >
                <i className="fa fa-user-plus"></i>
            </button>
          </span> :
          ''
        }
        <br/>
        <br/>
        {this.props.currentError &&
          <div className="alert alert-danger">
            <i className="close fa fa-times" onClick={() => this.props.resetCurrentError()}></i>
            {this.props.currentError}
          </div>
        }
        <div className="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
          <div className="panel panel-default">
            <div className="panel-heading" role="tab" id="description-heading">
              <h4 className="panel-title">
                <a role="button"
                   data-toggle="collapse"
                   data-parent="#accordion"
                   href="#description-collapse"
                   aria-expanded="true"
                   aria-controls="description-collapse"
                >
                  {trans('session_event_description', {}, 'cursus')}
                </a>
              </h4>
            </div>
            <div id="description-collapse"
                 className="panel-collapse collapse in"
                 role="tabpanel"
                 aria-labelledby="description-heading"
            >
              <div className="panel-body">
                <b>{t('duration')} :</b>
                &nbsp;
                {this.props.event['startDate']}
                &nbsp;
                <i className="fa fa-long-arrow-right"></i>
                &nbsp;
                {this.props.event['endDate']}
                <br/>
                <b>{trans('max_users', {} ,'cursus')} :</b>
                &nbsp;
                {this.props.event['maxUsers'] !== undefined ?
                  this.props.event['maxUsers'] :
                  '-'
                }
                <br/>
                <b>{t('registration')} :</b>
                &nbsp;
                {registrationTypes[this.props.event['registrationType']]}
                <hr/>
                {this.props.event['description'] ?
                  <div dangerouslySetInnerHTML={{__html: this.props.event['description']}}>
                  </div> :
                  <div className="alert alert-warning">
                    {trans('no_description', {}, 'cursus')}
                  </div>
                }
              </div>
            </div>
          </div>
          {this.props.canEdit ?
            <div className="panel panel-default">
              <div className="panel-heading" role="tab" id="participants-heading">
                <h4 className="panel-title">
                  <a role="button"
                     data-toggle="collapse"
                     data-parent="#accordion"
                     href="#participants-collapse"
                     aria-expanded="true"
                     aria-controls="participants-collapse"
                  >
                    {trans('participants', {}, 'cursus')}
                  </a>
                </h4>
              </div>
              <div id="participants-collapse"
                   className="panel-collapse collapse"
                   role="tabpanel"
                   aria-labelledby="participants-heading"
              >
                <div className="panel-body">
                  {this.props.participants.length > 0 ?
                    <div className="table-responsive">
                      <table className="table table-stripped">
                        <thead>
                        <tr>
                          <th>{t('last_name')}</th>
                          <th>{t('first_name')}</th>
                          <th>{t('status')}</th>
                          <th>{t('actions')}</th>
                        </tr>
                        </thead>
                        <tbody>
                          {this.props.participants.map((p, idx) =>
                            <tr key={idx}>
                              <td>{p.user.lastName}</td>
                              <td>{p.user.firstName}</td>
                              <td>
                                {p.registrationStatus === 0 &&
                                  <label className="label label-success">
                                    {registrationStatus[p.registrationStatus]}
                                  </label>
                                }
                                {p.registrationStatus === 1 &&
                                  <label className="label label-warning">
                                    {registrationStatus[p.registrationStatus]}
                                  </label>
                                }
                              </td>
                              <td>
                                <button className="btn btn-danger btn-sm" onClick={() => this.removeParticipant(p.id)}>
                                  <i className="fa fa-trash"></i>
                                </button>
                              </td>
                            </tr>
                          )}
                        </tbody>
                      </table>
                    </div> :
                    <div className="alert alert-warning">
                      {trans('no_participant', {}, 'cursus')}
                    </div>
                  }
                </div>
              </div>
            </div> :
            ''
          }
        </div>
        {this.state.modal.type &&
          this.props.createModal(
            this.state.modal.type,
            this.state.modal.props,
            this.state.modal.fading,
            this.hideModal.bind(this)
          )
        }
        <br/>
        <a className="btn btn-default" href={'#'}>
          <i className="fa fa-arrow-left"></i>
          &nbsp;
          {t('back')}
        </a>
      </div>
    )
  }
}

EventView.propTypes = {
  workspaceId: T.number.isRequired,
  event: T.shape({
    id: T.number,
    name: T.string,
    description: T.string,
    startDate: T.string,
    endDate: T.string,
    registrationType: T.number,
    maxUsers: T.number
  }).isRequired,
  participants: T.array.isRequired,
  canEdit: T.number.isRequired,
  currentError: T.string,
  resetCurrentSessionEvent: T.func.isRequired,
  editSessionEvent: T.func,
  resetEventForm: T.func,
  updateEventForm: T.func,
  loadEventForm: T.func,
  registerParticipants: T.func,
  deleteParticipants: T.func,
  resetCurrentError: T.func,
  createModal: T.func
}

function mapStateToProps(state) {
  return {
    workspaceId: state.workspaceId,
    event: selectors.currentEvent(state),
    participants: selectors.currentParticipants(state),
    canEdit: selectors.canEdit(state),
    currentError: selectors.currentError(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    resetCurrentSessionEvent: () => {
      dispatch(actions.resetCurrentSessionEvent())
    },
    editSessionEvent: (eventId, eventData) => {
      dispatch(actions.editSessionEvent(eventId, eventData))
    },
    resetEventForm: () => dispatch(actions.resetEventForm()),
    updateEventForm: (property, value) => dispatch(actions.updateEventForm(property, value)),
    loadEventForm: (event) => dispatch(actions.loadEventForm(event)),
    registerParticipants: (eventId, usersIds) => dispatch(actions.registerUsersToSessionEvent(eventId, usersIds)),
    deleteParticipants: (sessionEventUsersIds) => dispatch(actions.deleteSessionEventUsers(sessionEventUsersIds)),
    resetCurrentError: () => dispatch(actions.resetCurrentError()),
    createModal: (type, props, fading, hideModal) => makeModal(type, props, fading, hideModal, hideModal)
  }
}

const ConnectedEventView = connect(mapStateToProps, mapDispatchToProps)(EventView)

export {ConnectedEventView as EventView}