import React from 'react'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/core/translation'
import {Meeting} from './meeting.jsx'

export const Meetings = props =>
  <div className="bbb-meetings">
    <h3>{trans('active_meetings', {}, 'bbb')}</h3>
    {props.meetings.map((meeting, index) =>
      <Meeting
        key={index}
        meeting={meeting}
      />
    )}
  </div>

Meetings.propTypes = {
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
  })).isRequired
}
