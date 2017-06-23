/*global UserPicker*/
import {connect} from 'react-redux'
import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import moment from 'moment'
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
          event: sessionEvent,
          session: this.props.session,
          confirmAction: this.props.editSessionEvent
        },
        fading: false
      }
    })
  }

  showEventCommentsManagement(sessionEvent) {
    this.setState({
      modal: {
        type: 'MODAL_EVENT_COMMENTS',
        urlModal: null,
        props: {
          title: this.props.canEdit ? trans('informations_management', {}, 'cursus') : t('informations'),
          event: sessionEvent
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

  acceptParticipant(id) {
    this.props.acceptParticipant(id)
  }

  hideModal() {
    this.setState({modal: {fading: true, urlModal: null}})
  }

  render() {
    return (
      <div>
        <h3>{this.props.event.name}</h3>
        <span className="pull-right">
          {this.props.canEdit &&
            <button
              className="btn btn-primary margin-right-sm"
              data-toggle="tooltip"
              data-placement="top"
              title={trans('edit_session_event', {}, 'cursus')}
              onClick={() => this.showEventEditionForm(this.props.event)}
            >
              <i className="fa fa-edit"></i>
            </button>
          }
          <button
            className="btn btn-primary margin-right-sm"
            data-toggle="tooltip"
            data-placement="top"
            title={this.props.canEdit ? trans('informations_management', {}, 'cursus') : t('informations')}
            onClick={() => this.showEventCommentsManagement(this.props.event)}
          >
            <i className="fa fa-info"></i>
          </button>
          {this.props.canEdit &&
            <button
              className="btn btn-primary margin-right-sm"
              data-toggle="tooltip"
              data-placement="top"
              title={trans('register_participants', {}, 'cursus')}
              onClick={() => this.showParticipantsSelection()}
            >
              <i className="fa fa-user-plus"></i>
            </button>
          }
        </span>
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
                <a
                  role="button"
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
            <div
              id="description-collapse"
              className="panel-collapse collapse in"
              role="tabpanel"
              aria-labelledby="description-heading"
            >
              <div className="panel-body">
                <b>{t('duration')} :</b>
                &nbsp;
                {moment(this.props.event['startDate']).format('DD/MM/YYYY HH:mm')}
                &nbsp;
                <i className="fa fa-long-arrow-right"></i>
                &nbsp;
                {moment(this.props.event['endDate']).format('DD/MM/YYYY HH:mm')}
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
                <hr/>
                {this.props.event['location'] || this.props.event['locationExtra'] ?
                  <div>
                    <h4>{t('location')}</h4>
                    {this.props.event['location'] &&
                      <div>
                        {this.props.event['location']['name']}
                        <br/>
                        {this.props.event['location']['street'] }, {this.props.event['location']['street_number'] }
                        {this.props.event['location']['box_number'] &&
                          <span> / {this.props.event['location']['box_number']}</span>
                        }
                        <br/>
                        {this.props.event['location']['pc']} {this.props.event['location']['town']}
                        <br/>
                        {this.props.event['location']['country']}
                        {this.props.event['location']['phone'] &&
                          <span>
                            <br/>
                            {this.props.event['location']['phone']}
                          </span>
                        }
                      </div>
                    }
                    <div dangerouslySetInnerHTML={{__html: this.props.event['locationExtra']}}>
                    </div>
                  </div> :
                  <div className="alert alert-warning">
                    {trans('no_location', {}, 'cursus')}
                  </div>
                }
                <hr/>
                {this.props.event['tutors'] && this.props.event['tutors'].length > 0 ?
                  <div>
                    <h4>{trans('tutors', {}, 'cursus')}</h4>
                    <ul>
                      {this.props.event['tutors'].map((tutor, index) =>
                        <li key={index}>
                          {tutor['firstName']} {tutor['lastName']}
                        </li>
                      )}
                    </ul>
                  </div> :
                  <div className="alert alert-warning">
                    {trans('no_tutor', {}, 'cursus')}
                  </div>
                }
              </div>
            </div>
          </div>
          {this.props.canEdit ?
            <div className="panel panel-default">
              <div className="panel-heading" role="tab" id="participants-heading">
                <h4 className="panel-title">
                  <a
                    role="button"
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
              <div
                id="participants-collapse"
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
                                {p.registrationStatus === 1 &&
                                  <button className="btn btn-success btn-sm" onClick={() => this.acceptParticipant(p.id)}>
                                    <i className="fa fa-check"></i>
                                  </button>
                                }
                                &nbsp;
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
    maxUsers: T.number,
    location: T.object,
    locationExtra: T.string,
    tutors: T.array
  }).isRequired,
  session: T.object,
  participants: T.array.isRequired,
  canEdit: T.bool.isRequired,
  currentError: T.string,
  resetCurrentSessionEvent: T.func.isRequired,
  editSessionEvent: T.func,
  registerParticipants: T.func,
  deleteParticipants: T.func,
  acceptParticipant: T.func,
  resetCurrentError: T.func,
  createModal: T.func
}

function mapStateToProps(state) {
  return {
    workspaceId: state.workspaceId,
    event: selectors.currentEvent(state),
    session: selectors.currentSession(state),
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
    registerParticipants: (eventId, usersIds) => dispatch(actions.registerUsersToSessionEvent(eventId, usersIds)),
    deleteParticipants: (sessionEventUsersIds) => dispatch(actions.deleteSessionEventUsers(sessionEventUsersIds)),
    acceptParticipant: (sessionEventUserId) => dispatch(actions.acceptSessionEventUser(sessionEventUserId)),
    resetCurrentError: () => dispatch(actions.resetCurrentError()),
    createModal: (type, props, fading, hideModal) => makeModal(type, props, fading, hideModal, hideModal)
  }
}

const ConnectedEventView = connect(mapStateToProps, mapDispatchToProps)(EventView)

export {ConnectedEventView as EventView}