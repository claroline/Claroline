import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {displayDuration, displayDate, getTimeDiff} from '#/main/app/intl/date'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Section} from '#/main/app/content/components/sections'

import {Recording as RecordingType} from '#/integration/big-blue-button/resources/bbb/prop-types'

const Record = props =>
  <Section
    {...omit(props, 'meetingId', 'recording', 'deleteRecording')}
    className="embedded-list-section"
    title={displayDate(props.recording.startTime, true, true)}
    subtitle={displayDuration(getTimeDiff(props.recording.startTime, props.recording.endTime))}
    actions={[
      {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-trash-o',
        label: trans('delete', {}, 'actions'),
        callback: () => props.deleteRecording(props.meetingId, props.recording.recordID),
        dangerous: true,
        confirm: true,
        displayed: props.canEdit
      }
    ]}
  >
    {-1 < ['processing', 'processed'].indexOf(props.recording.state) &&
      <div className="alert alert-warning">
        {trans(props.recording.state, {}, 'bbb')}
      </div>
    }

    {props.recording.media.podcast &&
      <a href={props.recording.media.podcast}>
        {trans('podcast', {}, 'bbb')}
      </a>
    }

    {props.recording.media.presentation &&
      <a href={props.recording.media.presentation}>
        {trans('presentation', {}, 'bbb')}
      </a>
    }
  </Section>

Record.propTypes = {
  meetingId: T.string.isRequired,
  canEdit: T.bool.isRequired,
  recording: T.shape(RecordingType.propTypes).isRequired,
  deleteRecording: T.func.isRequired
}

export {
  Record
}