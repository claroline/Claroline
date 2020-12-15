import React from 'react'
import {PropTypes as T} from 'prop-types'
import {schemeCategory20c} from 'd3-scale'

import {trans} from '#/main/app/intl/translation'

const BBBMetrics = (props) =>
  <div className="row">
    <div className="analytics-card">
      <span className="fa fa-chalkboard" style={{backgroundColor: schemeCategory20c[1]}} />

      <h1 className="h3">
        <small>{trans('active_meetings', {}, 'bbb')}</small>
        {props.meetings + (props.maxMeetings ? ' / ' + props.maxMeetings : '')}
      </h1>
    </div>

    <div className="analytics-card">
      <span className="fa fa-chalkboard-teacher" style={{backgroundColor: schemeCategory20c[5]}} />

      <h1 className="h3">
        <small>{trans('meeting_participants', {}, 'bbb')}</small>
        {props.meetingParticipants ? props.meetingParticipants : <span className="fa fa-fw fa-infinity" />}
      </h1>
    </div>

    <div className="analytics-card">
      <span className="fa fa-user" style={{backgroundColor: schemeCategory20c[9]}} />

      <h1 className="h3">
        <small>{trans('participants')}</small>
        {props.participants + (props.maxParticipants ? ' / ' + props.maxParticipants : '')}
      </h1>
    </div>

    <div className="analytics-card">
      <span className="fa fa-server" style={{backgroundColor: schemeCategory20c[13]}} />

      <h1 className="h3">
        <small>{trans('available_servers', {}, 'bbb')}</small>
        {props.availableServers + ' / ' + props.servers}
      </h1>
    </div>
  </div>

BBBMetrics.propTypes = {
  meetings: T.number,
  maxMeetings: T.number,
  meetingParticipants: T.number,
  participants: T.number,
  maxParticipants: T.number,
  servers: T.number,
  availableServers: T.number
}

export {
  BBBMetrics
}