import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {url} from '#/main/app/api'
import {DataInput} from '#/main/app/data/components/input'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentHtml} from '#/main/app/content/components/html'

import {BBB as BBBTypes, Recording as RecordingTypes} from '#/integration/big-blue-button/resources/bbb/prop-types'

class Player extends Component {
  constructor(props) {
    super(props)

    this.state = {
      ready: false,
      username: !isEmpty(this.props.currentUser) ? this.props.currentUser.name : trans('anonymous') + '_' + Math.floor(Math.random() * 1001)
    }
  }

  componentDidMount() {
    if (this.props.canStart && get(this.props.bbb, 'activated', false)) {
      this.props.createMeeting(this.props.bbb).then(() => this.setState({ready: true}))
    }
  }

  isClosed() {
    return !get(this.props.bbb, 'activated', false) || !this.props.canStart
  }

  openTab() {
    window.open(url(['apiv2_bbb_meeting_join', {id: this.props.bbb.id, username: this.state.username}]), '_blank')
  }

  render() {
    if (!this.isClosed() && !this.state.ready) {
      return (
        <ContentLoader
          className="row"
          size="lg"
          description="Nous ouvrons votre classe virtuelle..."
        />
      )
    }

    return (
      <Fragment>
        {this.props.bbb.newTab && this.props.bbb.welcomeMessage &&
          <div className="panel panel-default">
            <ContentHtml className="panel-body">
              {this.props.bbb.welcomeMessage}
            </ContentHtml>
          </div>
        }

        {this.isClosed() &&
          <AlertBlock type="danger" title={trans('meeting_is_closed', {}, 'bbb')} className="component-container">
            {trans(get(this.props.bbb, 'activated', false) ? 'meetings_limit_reached':'meeting_disabled', {}, 'bbb')}
          </AlertBlock>
        }

        {!this.isClosed() && !isEmpty(this.props.joinStatus) &&
          <AlertBlock type="warning" title={trans('meeting_cannot_join', {}, 'bbb')} className="component-container">
            {trans(this.props.joinStatus, {}, 'bbb')}
          </AlertBlock>
        }

        {!this.isClosed() && isEmpty(this.props.joinStatus) && this.props.bbb.newTab &&
          <form action="#" style={{marginTop: '20px'}}>
            {this.props.bbb.customUsernames &&
              <DataInput
                id="custom-username"
                type="string"
                label={trans('username')}
                value={this.state.username}
                required={true}
                onChange={(value) => this.setState({username: value})}
              />
            }

            <Button
              type={CALLBACK_BUTTON}
              className="btn btn-block btn-emphasis component-container"
              icon="fa fa-fw fa-sign-in-alt"
              label={trans('join', {}, 'bbb')}
              callback={() => this.openTab()}
              disabled={!this.state.ready}
              htmlType="submit"
              primary={true}
            />
          </form>
        }

        {(this.isClosed() || !isEmpty(this.props.joinStatus)) && this.props.allowRecords && this.props.bbb.record && get(this.props.lastRecording, 'media.presentation') && this.props.bbb.newTab &&
          <Button
            type={URL_BUTTON}
            className="btn btn-block btn-emphasis"
            icon="fa fa-fw fa-video"
            label={trans('show-last-record', {}, 'actions')}
            target={this.props.lastRecording.media.presentation}
            primary={true}
            open="_blank"
          />
        }

        {this.props.bbb.newTab && this.props.allowRecords && this.props.bbb.record &&
          <Button
            type={LINK_BUTTON}
            className="btn btn-block"
            icon="fa fa-fw fa-list"
            label={trans('show-records', {}, 'actions')}
            target={`${this.props.path}/records`}
          />
        }

        {!this.isClosed() && isEmpty(this.props.joinStatus) && !this.props.bbb.newTab &&
          <div
            className="content-container bbb-content-container"
            style={this.props.bbb.ratio ?
              {
                position: 'relative',
                paddingBottom: `${this.props.bbb.ratio}%`,
                height: 0,
                overflow: 'auto'
              } :
              {
                height: 0,
                overflow: 'auto'
              }
            }
          >
            <iframe
              className="bbb-iframe"
              src={url(['apiv2_bbb_meeting_join', {id: this.props.bbb.id}])}
              style={{
                width: '100%',
                height: '100%',
                position: 'absolute',
                top: 0,
                left: 0
              }}
            />
          </div>
        }
      </Fragment>
    )
  }
}

Player.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  bbb: T.shape(
    BBBTypes.propTypes
  ).isRequired,
  lastRecording: T.shape(
    RecordingTypes.propTypes
  ).isRequired,
  allowRecords: T.bool,
  canStart: T.bool.isRequired,
  joinStatus: T.string,
  createMeeting: T.func.isRequired
}

export {
  Player
}
