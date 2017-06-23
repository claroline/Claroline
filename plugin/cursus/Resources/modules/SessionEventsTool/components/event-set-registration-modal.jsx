import {connect} from 'react-redux'
import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import moment from 'moment'
import Modal from 'react-bootstrap/lib/Modal'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'
import {t, trans} from '#/main/core/translation'
import {actions} from '../actions'
import {selectors} from '../selectors'
import {registrationStatus} from '../enums'

export const MODAL_EVENT_SET_REGISTRATION = 'MODAL_EVENT_SET_REGISTRATION'

class EventSetRegistrationModal  extends Component {
  componentDidMount() {
    this.props.loadSetEvents(this.props.eventSet.id)
  }

  componentWillUnmount() {
    this.props.resetSetEvents()
  }

  render() {
    return (
      <BaseModal {...this.props}>
        <Modal.Body>
          <div className="well well-sm">
            {trans('session_event_set_limit_info', {limit: this.props.eventSet.limit}, 'cursus')}
          </div>
          <div className="table-responsive">
            <table className="table table-bordered">
              <tbody>
                {this.props.events.map((e, index) =>
                  <tr key={index}>
                    <td>
                      <div>
                        <a data-toggle="collapse" href={'#collapse-' + e.id}>
                          <b>{e.name}</b>
                        </a>
                        <div className="collapse" id={'collapse-' + e.id}>
                          {moment(e.startDate).format('DD/MM/YYYY HH:mm')}
                          &nbsp;
                          <i className="fa fa-long-arrow-right"></i>
                          &nbsp;
                          {moment(e.endDate).format('DD/MM/YYYY HH:mm')}
                          {e.description &&
                            <div>
                              <hr/>
                              <div dangerouslySetInnerHTML={{__html: e.description}}></div>
                            </div>
                          }
                          {(e.location || e.locationExtra) &&
                            <div>
                              <hr/>
                              <b>{t('location')}</b>
                            </div>
                          }
                          {e.location &&
                            <div>
                              {e.location.name}
                              <br/>
                              {e.location.street}, {e.location.street_number}
                              {e.location.box_number &&
                                <span> / {e.location.box_number}</span>
                              }
                              <br/>
                              {e.location.pc} {e.location.town}
                              <br/>
                              {e.location.country}
                              {e.location.phone &&
                                <span>
                                  <br/>
                                  {e.location.phone}
                                </span>
                              }
                            </div>
                          }
                          {e.locationExtra &&
                            <div dangerouslySetInnerHTML={{__html: e.locationExtra}}></div>
                          }
                          {e.tutors && e.tutors.length > 0 &&
                            <div>
                              <hr/>
                              <b>{trans('tutors', {}, 'cursus')}</b>
                              <ul>
                                {e.tutors.map((tutor, index) =>
                                  <li key={index}>
                                    {tutor['firstName']} {tutor['lastName']}
                                  </li>
                                )}
                              </ul>
                            </div>
                          }
                        </div>
                      </div>
                    </td>
                    <td className="text-center">
                      {this.props.registrations[e.id] ?
                        this.props.registrations[e.id].registrationStatus === 0 ?
                          <span className="label label-success">
                            {registrationStatus[this.props.registrations[e.id].registrationStatus]}
                          </span> :
                          <span className="label label-warning">
                            {registrationStatus[this.props.registrations[e.id].registrationStatus]}
                          </span> :
                        !this.props.disableRegistration && this.props.eventSet.limit > this.props.nbRegistrations &&
                          <button className="btn btn-default" onClick={() => this.props.selfRegisterToSessionEvent(e.id)}>
                            {t('register')}
                          </button>
                      }
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </Modal.Body>
        <Modal.Footer>
          <button className="btn btn-default" onClick={this.props.fadeModal}>
            {t('close')}
          </button>
        </Modal.Footer>
      </BaseModal>
    )
  }
}

EventSetRegistrationModal.propTypes = {
  disableRegistration: T.bool,
  eventSet: T.shape({
    id: T.number,
    name: T.string,
    limit: T.number
  }).isRequired,
  events: T.array,
  registrations: T.object,
  nbRegistrations: T.number,
  loadSetEvents: T.func.isRequired,
  resetSetEvents: T.func.isRequired,
  selfRegisterToSessionEvent: T.func.isRequired,
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    disableRegistration: selectors.disableRegistration(state),
    events: state.setEvents.events,
    registrations: state.setEvents.registrations,
    nbRegistrations: state.setEvents.nbRegistrations
  }
}

function mapDispatchToProps(dispatch) {
  return {
    loadSetEvents: eventSetId => dispatch(actions.getSetEvents(eventSetId)),
    resetSetEvents: () => dispatch(actions.resetSetEvents()),
    selfRegisterToSessionEvent: sessionEventId => dispatch(actions.selfRegisterToSessionEvent(sessionEventId, true))
  }
}

const ConnectedEventSetRegistrationModal = connect(mapStateToProps, mapDispatchToProps)(EventSetRegistrationModal)

export {ConnectedEventSetRegistrationModal as EventSetRegistrationModal}
