import {connect} from 'react-redux'
import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import moment from 'moment'
import Modal from 'react-bootstrap/lib/Modal'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'
import {t, trans} from '#/main/core/translation'
import {Textarea} from '#/main/core/layout/form/components/field/textarea.jsx'
import {actions} from '../actions'

export const MODAL_EVENT_COMMENTS = 'MODAL_EVENT_COMMENTS'

class EventCommentsModal  extends Component {
  constructor(props) {
    super(props)
    this.state = {
      showCreationForm: false,
      newComment: null,
      showEditionForm: 0,
      currentComment: null
    }
  }

  componentDidMount() {
    this.props.loadComments(this.props.event['id'])
  }

  componentWillUnmount() {
    this.props.resetComments()
  }

  switchCreationForm(enabled) {
    this.setState({showCreationForm: enabled, newComment: null})
  }

  switchEditionForm(commentId, content) {
    this.setState({showEditionForm: commentId, currentComment: content})
  }

  updateNewComment(content) {
    this.setState({newComment: content})
  }

  updateCurrentComment(content) {
    this.setState({currentComment: content})
  }

  validateNewComment() {
    this.props.createComment(this.props.event['id'], this.state['newComment'])
    this.switchCreationForm(false)
  }

  validateEditionComment() {
    this.props.editComment(this.state['showEditionForm'], this.state['currentComment'])
    this.switchEditionForm(0, null)
  }

  render() {
    return (
      <BaseModal {...this.props}>
        <Modal.Body>
          <div className="panel panel-default">
            <div className="panel-heading" role="tab" id="description-heading">
              <h4 className="panel-title">
                <a
                  role="button"
                  data-toggle="collapse"
                  href="#event-collapse"
                  aria-expanded="true"
                  aria-controls="event-collapse"
                >
                  {trans('session_event', {}, 'cursus')}
                  <i className="pull-right fa fa-chevron-down"></i>
                </a>
              </h4>
            </div>
            <div
              id="event-collapse"
              className="panel-collapse collapse"
              role="tabpanel"
              aria-labelledby="event-heading"
            >
              <div className="panel-body">
                <h3>{this.props.event['name']}</h3>
                <div>
                  {moment(this.props.event['startDate']).format('DD/MM/YYYY HH:mm')}
                  &nbsp;
                  <i className="fa fa-long-arrow-right"></i>
                  &nbsp;
                  {moment(this.props.event['endDate']).format('DD/MM/YYYY HH:mm')}
                </div>
                {(this.props.event['location'] || this.props.event['locationExtra']) &&
                  <hr/>
                }
                {this.props.event['location'] &&
                  <div>
                    {this.props.event['location']['name']}
                    <br/>
                    {this.props.event['location']['street']}, {this.props.event['location']['street_number']}
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
                {this.props.event['locationExtra'] &&
                  <div dangerouslySetInnerHTML={{__html: this.props.event['locationExtra']}}></div>
                }
                {this.props.event['description'] &&
                  <div>
                    <hr/>
                    <div dangerouslySetInnerHTML={{__html: this.props.event['description']}}>
                    </div>
                  </div>
                }
                {this.props.event['tutors'] && this.props.event['tutors'].length > 0 &&
                  <div>
                    <hr/>
                    <h4>{trans('tutors', {}, 'cursus')}</h4>
                    <ul>
                      {this.props.event['tutors'].map((tutor, index) =>
                        <li key={index}>
                          {tutor['firstName']} {tutor['lastName']}
                        </li>
                      )}
                    </ul>
                  </div>
                }
              </div>
            </div>
          </div>
          <hr/>
          {this.props.canEdit &&
            <div>
              {this.state.showCreationForm ?
                <div>
                  <label>{trans('add_information', {}, 'cursus')}</label>
                  <Textarea
                    id="new-event-comment"
                    content={this.state.newComment}
                    onChange={text => this.updateNewComment(text)}
                  >
                  </Textarea>
                  <br/>
                  <button className="btn btn-primary" onClick={() => this.validateNewComment(false)}>
                      {t('ok')}
                  </button>
                  &nbsp;
                  <button className="btn btn-default" onClick={() => this.switchCreationForm(false)}>
                      {t('cancel')}
                  </button>
                </div> :
                <button className="btn btn-default" onClick={() => this.switchCreationForm(true)}>
                  <i className="fa fa-plus-circle"></i>
                  &nbsp;
                  {trans('add_information', {}, 'cursus')}
                </button>
              }
              <hr/>
            </div>
          }
          {this.props.eventComments.length > 0 ?
            <div className="table-responsive" style={{padding: '1px'}}>
              <table className="table table-stripped">
                <thead>
                  <tr>
                    <th>{t('informations')}</th>
                    <th>{t('creation_date')}</th>
                  </tr>
                </thead>
                <tbody>
                  {this.props.eventComments.map((c, index) => {
                    if (this.props.canEdit && (this.state.showEditionForm === c.id)) {
                      return (
                        <tr key={index}>
                          <td colSpan="3">
                            <label>{trans('edit_information', {}, 'cursus')}</label>
                            <Textarea
                              id="edition-event-comment"
                              content={this.state.currentComment}
                              onChange={text => this.updateCurrentComment(text)}
                            >
                            </Textarea>
                            <br/>
                            <button className="btn btn-primary" onClick={() => this.validateEditionComment()}>
                                {t('ok')}
                            </button>
                            &nbsp;
                            <button className="btn btn-default" onClick={() => this.switchEditionForm(0, null)}>
                                {t('cancel')}
                            </button>
                          </td>
                        </tr>
                      )
                    } else {
                      return (
                        <tr key={index}>
                          <td>
                            <div dangerouslySetInnerHTML={{__html: c['content']}}></div>
                          </td>
                          <td>
                            {c['creationDate']}
                          </td>
                          {this.props.canEdit &&
                            <td>
                              <button
                                className="btn btn-default btn-sm"
                                data-toggle="tooltip"
                                data-placement="left"
                                title={trans('edit_information', {}, 'cursus')}
                                onClick={() => this.switchEditionForm(c['id'], c['content'])}
                              >
                                <i className="fa fa-edit"></i>
                              </button>
                              &nbsp;
                              <button
                                className="btn btn-danger btn-sm"
                                data-toggle="tooltip"
                                data-placement="left"
                                title={trans('delete_information', {}, 'cursus')}
                                onClick={() => this.props.deleteComment(c['id'])}
                              >
                                <i className="fa fa-trash"></i>
                              </button>
                            </td>
                          }
                        </tr>
                      )
                    }
                  })}
                </tbody>
              </table>
            </div> :
            <div className="alert alert-warning">
              {trans('no_information', {}, 'cursus')}
            </div>
          }
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

EventCommentsModal.propTypes = {
  canEdit: T.bool.isRequired,
  event: T.shape({
    id: T.number,
    name: T.string,
    description: T.string,
    startDate: T.string,
    endDate: T.string,
    registrationType: T.number.isRequired,
    maxUsers: T.number,
    location: T.object,
    locationExtra: T.string,
    tutors: T.array
  }).isRequired,
  eventComments: T.array.isRequired,
  loadComments: T.func.isRequired,
  resetComments: T.func.isRequired,
  createComment: T.func.isRequired,
  editComment: T.func.isRequired,
  deleteComment: T.func.isRequired,
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    canEdit: state.canEdit === 1,
    eventComments: state.eventComments
  }
}

function mapDispatchToProps(dispatch) {
  return {
    loadComments: (eventId) => dispatch(actions.getEventComments(eventId)),
    resetComments: () => dispatch(actions.resetEventComments()),
    createComment: (eventId, content) => dispatch(actions.createEventComment(eventId, content)),
    editComment: (commentId, content) => dispatch(actions.editEventComment(commentId, content)),
    deleteComment: (commentId) => dispatch(actions.deleteEventComment(commentId))
  }
}

const ConnectedEventCommentsModal = connect(mapStateToProps, mapDispatchToProps)(EventCommentsModal)

export {ConnectedEventCommentsModal as EventCommentsModal}
