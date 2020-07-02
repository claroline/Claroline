import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormSection} from '#/main/app/content/form/components/sections'

const Meeting = props =>
  <FormSection
    className="embedded-list-section"
    icon="fa fa-fw fa-users"
    title={props.meeting.meetingName || props.meeting.meetingID}
    actions={[
      {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-stop-circle',
        label: trans('end_meeting', {}, 'bbb'),
        callback: () => props.endMeeting(props.meeting.meetingID)
      }
    ]}
  >
    <b>{trans('meeting_id', {}, 'bbb')} :</b> {props.meeting.meetingID}
    <br/>
    <b>{trans('meeting_name', {}, 'bbb')} :</b> {props.meeting.meetingName}
    <br/>
    <b>{trans('creation_date')} :</b> {props.meeting.createDate}
    <br/>
    <b>{trans('nb_participants', {}, 'bbb')} :</b> {props.meeting.participantCount}
    <br/>
    <hr/>
    <a href={props.meeting.url}>
      {props.meeting.url}
    </a>
  </FormSection>

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
    participantCount: T.number,
    listenerCount: T.string,
    voiceParticipantCount: T.string,
    videoCount: T.string,
    duration: T.string,
    hasUserJoined: T.string,
    url: T.string
  }).isRequired,
  endMeeting: T.func.isRequired
}

export {
  Meeting
}
