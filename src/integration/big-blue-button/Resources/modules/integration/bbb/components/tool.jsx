import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ToolPage} from '#/main/core/tool/containers/page'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

import {BBBMetrics} from '#/integration/big-blue-button/integration/bbb/components/metrics'
import {BBBResources} from '#/integration/big-blue-button/integration/bbb/components/resources'
import {BBBRooms} from '#/integration/big-blue-button/integration/bbb/components/rooms'
import {BBBRecordings} from '#/integration/big-blue-button/integration/bbb/components/recordings'
import {BBBServers} from '#/integration/big-blue-button/integration/bbb/components/servers'

class BBBTool extends Component {
  componentDidMount() {
    if (!this.props.loaded) {
      this.props.loadInfo()
    }
  }

  render() {
    if (!this.props.loaded) {
      return (
        <ContentLoader
          size="lg"
          description="Nous chargeons votre outil..."
        />
      )
    }

    return (
      <ToolPage
        path={[{
          type: LINK_BUTTON,
          label: trans('bbb', {}, 'integration'),
          target: `${this.props.path}/bbb`
        }]}
        subtitle={trans('bbb', {}, 'integration')}
      >
        <BBBMetrics
          meetings={this.props.activeMeetingsCount}
          maxMeetings={this.props.maxMeetings}
          meetingParticipants={this.props.maxMeetingParticipants}
          participants={this.props.participantsCount}
          maxParticipants={this.props.maxParticipants}
          servers={this.props.servers.length}
          availableServers={this.props.servers.filter(server => !server.disabled && (!server.limit || server.limit > server.participants)).length}
        />

        <div className="row">
          <div className="col-md-3">
            <Vertical
              basePath={this.props.path+'/bbb'}
              style={{
                marginTop: 20
              }}
              tabs={[
                {
                  icon: 'fa fa-fw fa-folder',
                  title: trans('resources'),
                  path: '/',
                  exact: true
                }, {
                  icon: 'fa fa-fw fa-chalkboard',
                  title: trans('meetings', {}, 'bbb'),
                  path: '/rooms'
                }, {
                  icon: 'fa fa-fw fa-video',
                  title: trans('recordings', {}, 'bbb'),
                  path: '/recordings'
                }, {
                  icon: 'fa fa-fw fa-server',
                  title: trans('servers', {}, 'bbb'),
                  path: '/servers'
                }
              ]}
            />
          </div>

          <div className="col-md-9">
            <Routes
              path={this.props.path+'/bbb'}
              routes={[
                {
                  path: '/',
                  exact: true,
                  render: () => (
                    <BBBResources
                      servers={this.props.servers}
                      endMeetings={this.props.endMeetings}
                    />
                  )
                }, {
                  path: '/rooms',
                  render: () => (
                    <BBBRooms
                      meetings={this.props.activeMeetings}
                    />
                  )
                }, {
                  path: '/recordings',
                  render: () => (
                    <BBBRecordings syncRecordings={this.props.syncRecordings} />
                  )
                }, {
                  path: '/servers',
                  render: () => (
                    <BBBServers
                      servers={this.props.servers}
                    />
                  )
                }
              ]}
            />
          </div>
        </div>
      </ToolPage>
    )
  }
}

BBBTool.propTypes = {
  path: T.string.isRequired,
  loaded: T.bool,
  maxMeetings: T.number,
  maxMeetingParticipants: T.number,
  maxParticipants: T.number,
  activeMeetings: T.array,
  activeMeetingsCount: T.number,
  participantsCount: T.number,
  servers: T.arrayOf(T.shape({
    url: T.string.isRequired,
    participants: T.number,
    limit: T.number,
    disabled: T.bool
  })),
  allowRecords: T.bool.isRequired,
  loadInfo: T.func.isRequired,
  endMeetings: T.func.isRequired,
  syncRecordings: T.func.isRequired
}

export {
  BBBTool
}