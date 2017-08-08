import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/core/translation'
import {actions} from '../actions'
import {Meetings} from './meetings.jsx'

class BBBConfigForm extends Component {
  componentDidMount() {
    this.props.getMeetings()
  }

  componentWillUnmount() {
    this.props.resetMessage()
  }

  render() {
    return (
      <div>
        {this.props.message && this.props.message.content &&
          <div className={`alert alert-${this.props.message.type}`}>
            <i
              className="fa fa-times close"
              onClick={() => this.props.resetMessage()}
            >
            </i>
            {this.props.message.content}
          </div>
        }
        <div className="form-group row">
          <label className="control-label col-md-3">
            {trans('bbb_server_url', {}, 'bbb')}
          </label>
          <div className="col-md-9">
            <input
              type="text"
              className="form-control"
              value={this.props.serverUrl ? this.props.serverUrl : undefined}
              onChange={e => this.props.updateConfig('serverUrl', e.target.value)}
            />
          </div>
        </div>

        <div className="form-group row">
          <label className="control-label col-md-3">
            {trans('security_salt', {}, 'bbb')}
          </label>
          <div className="col-md-9">
            <input
              type="text"
              className="form-control"
              value={this.props.securitySalt ? this.props.securitySalt : undefined}
              onChange={e => this.props.updateConfig('securitySalt', e.target.value)}
            />
          </div>
        </div>

        <hr/>
        <div className="config-buttons">
          <button
            className="btn btn-primary"
            onClick={() => this.props.saveConfig()}
          >
            {trans('save_configuration', {}, 'bbb')}
          </button>
        </div>
        {this.props.meetings.length > 0 &&
          <div>
            <hr/>
            <Meetings meetings={this.props.meetings}/>
          </div>
        }
      </div>
    )
  }
}

BBBConfigForm.propTypes = {
  serverUrl: T.string,
  securitySalt: T.string,
  message: T.object,
  meetings: T.arrayOf(T.shape({
    meetingID: T.string.isRequired,
    meetingName: T.string,
    createTime: T.string,
    createDate: T.string,
    attendeePW: T.string,
    moderatorPW: T.string,
    hasBeenForciblyEnded: T.string,
    running: T.string,
    participantCount: T.string,
    listenerCount: T.string,
    voiceParticipantCount: T.string,
    videoCount: T.string,
    duration: T.string,
    hasUserJoined: T.string,
    url: T.string
  })),
  updateConfig: T.func,
  saveConfig: T.func,
  resetMessage: T.func,
  getMeetings: T.func
}

function mapStateToProps(state) {
  return {
    serverUrl: state.config.serverUrl,
    securitySalt: state.config.securitySalt,
    message: state.message,
    meetings: state.meetings
  }
}

function mapDispatchToProps(dispatch) {
  return {
    updateConfig: (property, value) => dispatch(actions.updateConfiguration(property, value)),
    saveConfig: () => dispatch(actions.saveConfiguration()),
    resetMessage: () => dispatch(actions.resetConfigurationMessage()),
    getMeetings: () => dispatch(actions.getMeetings())
  }
}

const ConnectedBBBConfigForm = connect(mapStateToProps, mapDispatchToProps)(BBBConfigForm)

export {ConnectedBBBConfigForm as BBBConfigForm}