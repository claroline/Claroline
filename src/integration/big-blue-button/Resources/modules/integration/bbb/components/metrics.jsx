import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentInfoBlocks} from '#/main/app/content/components/info-block'

const BBBMetrics = (props) =>
  <ContentInfoBlocks
    className="my-4"
    size="lg"
    items={[
      {
        icon: 'fa fa-chalkboard',
        label: trans('active_meetings', {}, 'bbb'),
        value: props.meetings + (props.maxMeetings ? ' / ' + props.maxMeetings : '')
      }, {
        icon: 'fa fa-chalkboard-teacher',
        label: trans('meeting_participants', {}, 'bbb'),
        value: props.meetingParticipants ? props.meetingParticipants : <span className="fa fa-fw fa-infinity" />
      }, {
        icon: 'fa fa-users',
        label: trans('participants'),
        value: props.participants + (props.maxParticipants ? ' / ' + props.maxParticipants : '')
      }, {
        icon: 'fa fa-server',
        label: trans('available_servers', {}, 'bbb'),
        value: props.availableServers + ' / ' + props.servers
      }
    ]}
  />

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