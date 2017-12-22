import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/core/translation'
import {actions} from '../actions'

class BBBContent extends Component {
  componentDidMount() {
    if (this.props.serverUrl && this.props.securitySalt) {
      this.props.connectToBBB()

      if (!this.hasEnded() && this.props.params.newTab) {
        const newTabInterval = setInterval(
          () => {
            if (this.props.bbbUrl && this.props.canJoin) {
              clearInterval(newTabInterval)
              window.open(this.props.bbbUrl, '_blank')
            }
          },
          2000
        )
      }
      if (!this.hasEnded() && !this.props.canJoin) {
        const canJoinInterval = setInterval(
          () => {
            if (this.props.canJoin) {
              clearInterval(canJoinInterval)
            } else {
              this.props.checkForModerators()
            }
          },
          60000
        )
      }
    }
  }

  hasEnded() {
    return this.props.params.endDate &&
      this.props.params.endDate.date &&
      new Date() > new Date(this.props.params.endDate.date)
  }

  render() {
    return (
      <div>
        {(!this.props.serverUrl || !this.props.securitySalt) &&
          <div className="alert alert-danger">
            {trans('bbb_not_configured_msg', {}, 'bbb')}
          </div>
        }
        {this.hasEnded() &&
          <div className="alert alert-warning">
            {trans('room_is_closed', {}, 'bbb')}
          </div>
        }
        {!this.hasEnded() && this.props.bbbUrl && this.props.canJoin && !this.props.params.newTab &&
          <iframe className="bbb-iframe" src={this.props.bbbUrl}></iframe>
        }
        {!this.hasEnded() && this.props.params.newTab && this.props.canJoin &&
          <div className="alert alert-info">
            {trans('bbb_running_in_new_tab', {}, 'bbb')}
          </div>
        }
        {!this.hasEnded() && !this.props.canJoin &&
          <div className="alert alert-warning">
            {trans('waiting_for_moderator', {}, 'bbb')}
          </div>
        }
      </div>
    )
  }
}

BBBContent.propTypes = {
  params: T.shape({
    id: T.number,
    roomName: T.string,
    newTab: T.boolean,
    moderatorRequired: T.boolean,
    record: T.boolean,
    startDate: T.object,
    endDate: T.object
  }),
  resource: T.shape({
    id: T.string,
    name: T.string
  }),
  serverUrl: T.string,
  securitySalt: T.string,
  bbbUrl: T.string,
  canJoin: T.bool.isRequired,
  connectToBBB: T.func.isRequired,
  checkForModerators: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    params: state.resource,
    resource: state.resourceNode,
    serverUrl: state.config.serverUrl,
    securitySalt: state.config.securitySalt,
    bbbUrl: state.bbbUrl,
    canJoin: state.canJoin
  }
}

function mapDispatchToProps(dispatch) {
  return {
    connectToBBB: () => dispatch(actions.connectToBBB()),
    checkForModerators: () => dispatch(actions.checkForModerators())
  }
}

const ConnectedBBBContent = connect(mapStateToProps, mapDispatchToProps)(BBBContent)

export {ConnectedBBBContent as BBBContent}