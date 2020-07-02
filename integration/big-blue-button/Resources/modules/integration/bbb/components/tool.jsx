import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ToolPage} from '#/main/core/tool/containers/page'
import {MetricCard} from '#/main/core/layout/components/metric-card'

import {Meeting} from '#/integration/big-blue-button/integration/bbb/components/meeting'

class BBBTool extends Component {
  componentDidMount() {
    if (!this.props.loaded) {
      this.props.loadMeetings()
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
        <div className="row">
          <div className="col-md-2 col-sm-3 col-xs-3 col-md-offset-1">
            <MetricCard
              value={this.props.maxMeetings}
              cardTitle={trans('meetings_limit', {}, 'bbb')}
            />
          </div>
          <div className="col-md-2 col-sm-3 col-xs-3">
            <MetricCard
              value={this.props.maxMeetingParticipants}
              cardTitle={trans('meeting_participants_limit', {}, 'bbb')}
            />
          </div>
          <div className="col-md-2 col-sm-3 col-xs-3">
            <MetricCard
              value={this.props.maxParticipants}
              cardTitle={trans('participants_limit', {}, 'bbb')}
            />
          </div>
          <div className="col-md-2 col-sm-3 col-xs-3">
            <MetricCard
              value={this.props.activeMeetingsCount}
              cardTitle={trans('active_meetings', {}, 'bbb')}
            />
          </div>
          <div className="col-md-2 col-sm-3 col-xs-3">
            <MetricCard
              value={this.props.participantsCount}
              cardTitle={trans('nb_participants', {}, 'bbb')}
            />
          </div>
        </div>

        <h3>{trans('active_meetings', {}, 'bbb')}</h3>

        {0 === this.props.meetings.length &&
          <em>{trans('no_active_meeting', {}, 'bbb')}</em>
        }

        {this.props.meetings.map((meeting, index) =>
          <Meeting
            key={index}
            meeting={meeting}
            endMeeting={this.props.endMeeting}
          />
        )}
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
  activeMeetingsCount: T.number,
  participantsCount: T.number,
  meetings: T.arrayOf(T.shape({
    // TODO : prop-types
  })),
  loadMeetings: T.func.isRequired,
  endMeeting: T.func.isRequired
}

export {
  BBBTool
}