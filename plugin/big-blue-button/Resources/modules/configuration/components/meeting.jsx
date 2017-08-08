import React from 'react'
import {PropTypes as T} from 'prop-types'
import {trans, t} from '#/main/core/translation'

export const Meeting = props =>
  <div className="bbb-meeting panel panel-default">
    <div className="panel-heading">
      <h3 className="panel-title">
        <a
          data-toggle="collapse"
          href={`#collapse-${props.meeting.meetingID}`}
        >
          {props.meeting.meetingName || props.meeting.meetingID}
        </a>
      </h3>
    </div>
    <div
      id={`collapse-${props.meeting.meetingID}`}
      className="panel-body collapse"
    >
      <b>{trans('meeting_id', {}, 'bbb')} :</b>
      &nbsp;
      {props.meeting.meetingID}
      <br/>
      <b>{trans('meeting_name', {}, 'bbb')} :</b>
      &nbsp;
      {props.meeting.meetingName}
      <br/>
      <b>{t('creation_date')} :</b>
      &nbsp;
      {props.meeting.createDate}
      <br/>
      <b>{trans('nb_participants', {}, 'bbb')} :</b>
      &nbsp;
      {props.meeting.participantCount}
      <br/>
      <hr/>
      <a href={props.meeting.url}>
        {props.meeting.url}
      </a>
    </div>
  </div>

Meeting.propTypes = {
  meeting: T.shape({
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
  }).isRequired
}
