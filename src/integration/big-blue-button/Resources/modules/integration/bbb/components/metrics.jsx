import React from 'react'
import {PropTypes as T} from 'prop-types'
import {schemeCategory20c} from 'd3-scale'

import {trans} from '#/main/app/intl/translation'
import {ContentCounter} from '#/main/app/content/components/counter'

const BBBMetrics = (props) =>
  <div className="row">
    <ContentCounter
      icon="fa fa-chalkboard"
      label={trans('active_meetings', {}, 'bbb')}
      color={schemeCategory20c[1]}
      value={props.meetings + (props.maxMeetings ? ' / ' + props.maxMeetings : '')}
    />

    <ContentCounter
      icon="fa fa-chalkboard-teacher"
      label={trans('meeting_participants', {}, 'bbb')}
      color={schemeCategory20c[5]}
      value={props.meetingParticipants ? props.meetingParticipants : <span className="fa fa-fw fa-infinity" />}
    />

    <ContentCounter
      icon="fa fa-user"
      label={trans('participants')}
      color={schemeCategory20c[9]}
      value={props.participants + (props.maxParticipants ? ' / ' + props.maxParticipants : '')}
    />

    <ContentCounter
      icon="fa fa-server"
      label={trans('available_servers', {}, 'bbb')}
      color={schemeCategory20c[13]}
      value={props.availableServers + ' / ' + props.servers}
    />
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